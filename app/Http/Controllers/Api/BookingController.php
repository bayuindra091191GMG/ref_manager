<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Libs\AutoNumberGenerator;
use App\Libs\QrisUtilities;
use App\Libs\TaxUtilities;
use App\Jobs\SendBookingCancelledNotification;
use App\Jobs\SendBookingCreatedNotification;
use App\Models\BookingDetail;
use App\Models\BookingHeader;
use App\Models\Branch;
use App\Models\Configuration;
use App\Models\Item;
use App\Models\Package;
use App\Models\TransactionHeader;
use App\Models\TransactionInvoice;
use App\Models\UserReview;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    private array $result;

    public function __construct()
    {
        $this->result = [
            'error' => [],
            'message' => '',
            'warning' => '',
            'request_id' => ApiUtilities::generateRandomShortString(20),
            'response' => [],
        ];
    }

    public function getBookingStatuses()
    {
        try {
            $statusArr = [
                'Semua',
                'Selesai',
            ];

            $this->result['response'] = $statusArr;
            $this->result['message'] = 'Sukses ambil data status booking!';

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/BookingController - getBookingStatuses ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';

            return Response::json($this->result, 500);
        }
    }

    public function getTermConditions()
    {
        try {
            $result = [
                'tnc_booking' => Configuration::where('key', 'tnc_booking')->value('value') ?? '',
                'tnc_payment' => Configuration::where('key', 'tnc_payment')->value('value') ?? '',
            ];
            $this->result['response'] = $result;
            $this->result['message'] = 'Ambil data term condition boooking berhasil!';

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/BookingController - getTermConditions ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';

            return Response::json($this->result, 500);
        }
    }

    public function get(Request $request)
    {
        try {
            $user = $request->user();

            $errors = [];
            $skip = 0;
            $statusStr = null;
            $statusId = null;

            // Validate status
            if (empty($request->query('status'))) {
                $errors[] = 'status tidak ditemukan';
            } else {
                $statusStr = $request->query('status');

                if ($statusStr !== 'Semua') {
                    $status = DB::table('statuses')
                        ->where('display', 'like', '%'.$statusStr.'%')
                        ->first();

                    if (empty($status)) {
                        $errors[] = 'status salah';
                    } else {
                        $statusId = $status->id;
                    }
                }
            }

            if (count($errors) > 0) {
                $this->result['error'] = $errors;
                $this->result['message'] = 'Terjadi kesalahan validasi';

                return Response::json($this->result, 400);
            }

            $headers = BookingHeader::with(['status'])
                ->where('user_id', $user->id);

            if ($statusStr !== 'Semua') {
                $headers = $headers->where('status_id', '=', $statusId);
            }

            if (! empty($request->query('type'))) {
                $headers = $headers->where('type', '=', $request->query('type'));
            }

            if (! empty($request->query('start_date')) && ! empty($request->query('end_date'))) {
                $startDate = Carbon::createFromFormat('d-m-Y', $request->query('start_date'));
                $startDate->startOfDay();

                $endDate = Carbon::createFromFormat('d-m-Y', $request->query('end_date'));
                $endDate->startOfDay()->addDays(1);

                $headers = $headers->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()]);
            }

            if ($request->exists('skip')) {
                if ($request->query('skip') != null) {
                    $skip = $request->query('skip');
                }
            }

            $headers = $headers->skip($skip)
                ->take(20)
                ->orderBy('created_at', 'desc')
                ->get();

            $reviewedDetailIds = UserReview::query()
                ->where('user_id', $user->id)
                ->where('status_id', 9)
                ->whereNotNull('booking_detail_id')
                ->pluck('booking_detail_id')
                ->mapWithKeys(fn ($detailId) => [(int) $detailId => true]);

            foreach ($headers as $booking) {
                $bookingStatus = $booking->status->display;

                $trxCode = $booking->code;
                $invoice = null;
                if (! empty($booking->transaction_id)) {
                    $trxCode = DB::table('transaction_headers')
                        ->where('id', '=', $booking->transaction_id)
                        ->value('code');
                    $invoice = TransactionInvoice::where('header_ids', $booking->transaction_id)->first();
                }

                $nResult = [
                    // 'booking' => $booking,
                    'booking_id' => $booking->id,
                    'booking_status' => $bookingStatus,
                    'booking_date' => \Carbon\Carbon::parse($booking->booking_date)->format('d-m-Y'),
                    'booking_time' => $booking->booking_time,
                    'payment_due_at' => ($booking->payment_due_at ?? $booking->created_at?->copy()->addMinutes(30))?->format('d-m-Y H:i'),
                    'booking_payment_proof' => $booking->payment_proof != null ? asset('storage/proofs/'.$booking->payment_proof) : '',
                    'qris' => (int) ($booking->point_used ?? 0) > 0 ? '' : ($booking->qr_payment_data != null ? $booking->qr_payment_data : $trxCode),
                    'gender' => $booking->user->gender == 'L' ? 'Laki-Laki' : 'Perempuan',
                    'trxCode' => $trxCode,
                    'grand_total' => (float) ($booking->grand_total ?? 0),
                    'payment_charge_ppn_percentage' => (float) ($booking->payment_charge_ppn_percentage ?? 0),
                    'payment_charge_ppn_value' => (float) ($booking->payment_charge_ppn_value ?? 0),
                    'total_payment' => (float) ($booking->total_payment ?? $booking->grand_total ?? 0),
                    'use_point' => (int) ($booking->point_used ?? 0) > 0,
                    'point_used' => (int) ($booking->point_used ?? 0),
                    'details' => [],
                    'invoice' => $invoice != null ? $invoice->id : 0,
                ];

                // get details
                $details = [];
                foreach ($booking->booking_details as $detail) {
                    $package = $detail->package_id != null ? $detail->package : $detail->item;
                    $details[] = [
                        'id' => $detail->package_id != null ? $detail->package_id : $detail->item_id,
                        'type' => $detail->package_id != null ? 'package' : 'item',
                        'name' => $detail->package_id != null ? $detail->package_name : $detail->item_name,
                        'qty' => $detail->qty,
                        'price' => $detail->price,
                        'partner_id' => null,
                        'need_partner' => $detail->item_id != null && $detail->item->category_id == 27,
                        'room_id' => null,
                        'duration' => $detail->package_id != null ? $detail->package->duration : $detail->item->duration,
                        'img' => $package->image_path != null ? asset('storage/images/items/'.$package->image_path) : '',
                        'customer_name' => $detail->customer_name != null ? $detail->customer_name : '',
                        'is_reviewed' => $reviewedDetailIds->has((int) $detail->id),
                    ];

                    // if($detail->package_id != null ){
                    //     if($detail->package->item_id != null){
                    //         $details[] = [
                    //             'id' => $detail->package->item_id,
                    //             'type' => 'item',
                    //             'name' => $detail->package->item->name,
                    //             'qty' => 1,
                    //             'price' => 0,
                    //             'partner_id' => null,
                    //         ];
                    //     }
                    // }
                }
                $nResult['details'] = $details;

                $this->result['response'][] = $nResult;
            }

            $this->result['message'] = 'Sukses ambil data booking!';

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/BookingController - get ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';

            return Response::json($this->result, 500);
        }
    }

    public function receipt(Request $request, ?int $id = null)
    {
        try {
            $id ??= $request->integer('id');

            if (! $id) {
                return response()->json([
                    'message' => 'ID tidak valid',
                    'response' => null,
                ], 422);
            }

            $booking = BookingHeader::query()
                ->where('id', $id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (! $booking || (int) $booking->status_id !== 9 || ! $booking->transaction_id) {
                return response()->json([
                    'message' => 'Receipt tidak ditemukan',
                    'response' => null,
                ], 404);
            }

            $relativePath = $this->getReceiptRelativePath($booking->id);
            $disk = Storage::disk('receipt_pdf');

            // ✅ ensure directory exists
            $directory = dirname($relativePath);
            if (! $disk->exists($directory)) {
                $disk->makeDirectory($directory);
            }

            // ✅ if already exists
            if ($disk->exists($relativePath)) {
                return response()->json([
                    'message' => 'Receipt berhasil diambil!',
                    'response' => [
                        'receipt_url' => $disk->url($relativePath),
                    ],
                ]);
            }

            $invoice = TransactionInvoice::query()
                ->with(['transaction_payment.pos_payment_type'])
                ->where('header_ids', $booking->transaction_id)
                ->first();

            if (! $invoice) {
                return response()->json([
                    'message' => 'Receipt tidak ditemukan',
                    'response' => null,
                ], 404);
            }

            $headers = TransactionHeader::query()
                ->with([
                    'employee',
                    'room',
                    'transaction_details.package',
                    'transaction_details.item',
                    'transaction_details.transaction_detail_partner.partner',
                ])
                ->where('id', $booking->transaction_id)
                ->get();

            if ($headers->isEmpty()) {
                return response()->json([
                    'message' => 'Transaction header tidak ditemukan',
                    'response' => null,
                ], 404);
            }

            $branch = Branch::query()
                ->select('id', 'name', 'address')
                ->find($booking->branch_id ?? $headers->first()->branch_id);

            // ✅ generate pdf
            $pdf = Pdf::loadView('admin.monitor_room.receipt', [
                'invoice' => $invoice,
                'branch' => $branch,
                'headers' => $headers,
                'reprint' => false,
                'membershipLevels' => '',
                'discountUsed' => [],
                'vouchers' => [],
            ]);

            $pdfContent = $pdf->output();

            if (empty($pdfContent)) {
                throw new \Exception('PDF kosong');
            }

            // ✅ save
            $disk = Storage::disk('receipt_pdf');

            $fullPath = $disk->path($relativePath);

            // ✅ ensure directory exists (INI PENTING BANGET)
            $directory = dirname($fullPath);

            if (! file_exists($directory)) {
                mkdir($directory, 0775, true);
            }

            $stored = $disk->put($relativePath, $pdfContent);

            if (! $stored) {
                throw new \Exception('Gagal menyimpan file');
            }

            return response()->json([
                'message' => 'Receipt berhasil diambil!',
                'response' => [
                    'receipt_url' => $disk->url($relativePath),
                ],
            ]);

        } catch (\Throwable $ex) {
            // Log::channel('api_exception')->error('Api/BookingController - receipt ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';

            return Response::json($this->result, 500);
        }
    }

    public function receiptCopilot(Request $request)
    {
        try {
            $id = $request->integer('id');
            $booking = BookingHeader::query()
                ->where('id', $id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (empty($booking) || (int) $booking->status_id !== 9 || empty($booking->transaction_id)) {
                $this->result['message'] = 'Receipt tidak ditemukan';

                return Response::json($this->result, 404);
            }

            $relativePath = $this->getReceiptRelativePath($booking->id);

            if (Storage::disk('receipt_pdf')->exists($relativePath)) {
                $this->result['response'] = [
                    'receipt_url' => Storage::disk('receipt_pdf')->url($relativePath),
                ];
                $this->result['message'] = 'Receipt berhasil diambil! if';

                return Response::json($this->result, 200);
            }

            $invoice = TransactionInvoice::query()
                ->with(['transaction_payment.pos_payment_type'])
                ->where('header_ids', $booking->transaction_id)
                ->first();

            if (empty($invoice)) {
                $this->result['message'] = 'Receipt tidak ditemukan';

                return Response::json($this->result, 404);
            }

            $headers = TransactionHeader::query()
                ->with([
                    'employee',
                    'room',
                    'transaction_details.package',
                    'transaction_details.item',
                    'transaction_details.transaction_detail_partner.partner',
                ])
                ->where('id', $booking->transaction_id)
                ->get();

            $branch = Branch::query()
                ->select('id', 'name', 'address')
                ->find($booking->branch_id ?? $headers->first()->branch_id);

            $pdfContent = Pdf::loadView('admin.monitor_room.receipt', [
                'invoice' => $invoice,
                'branch' => $branch,
                'headers' => $headers,
                'reprint' => false,
                'membershipLevels' => '',
                'discountUsed' => [],
                'vouchers' => [],
            ])->output();

            Storage::disk('receipt_pdf')->put($relativePath, $pdfContent);

            $this->result['response'] = [
                'receipt_url' => Storage::disk('receipt_pdf')->url($relativePath),
            ];
            $this->result['message'] = 'Receipt berhasil diambil! bottom';

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/BookingController - receipt ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';

            return Response::json($this->result, 500);
        }
    }

    private function getReceiptRelativePath(int $bookingId): string
    {
        return 'booking-'.$bookingId.'.pdf';
    }

    public function submit(Request $request)
    {
        try {
            $rules = [
                'package_ids' => 'required|array|min:1',
                'date' => 'required',
                'time' => 'required',
            ];

            $messages = [
                'package_ids.required' => 'Tidak Ada Treatment Yang dipilih',
                'date.required' => 'Tanggal Booking Belum dipilih',
                'time.required' => 'Waktu Booking Belum dipilih',
            ];

            // ==========================================
            // 1. UBAH INI SESUAI PERMINTAAN ANDA
            // ==========================================
            $data = $request->all();
            // ==========================================

            // Data dari JSON sudah otomatis array → tidak perlu json_decode
            $validator = Validator::make($data, $rules, $messages);

            if ($validator->fails()) {
                $this->result['response'] = $validator->errors()->all();
                $this->result['message'] = 'Terjadi kesalahan validasi';

                return Response::json($this->result, 400);
            }

            $user = $request->user();
            $branchId = 1;
            $dateCarbon = null;
            $dynamicQris = '';

            $errors = [];

            $branch = DB::table('branches')
                ->where('id', '=', $branchId)
                ->first();

            // ===== Validate date =====
            try {
                $today = Carbon::today('Asia/Jakarta');
                $dateCarbon = Carbon::createFromFormat('d-m-Y', $data['date']);

                if ($dateCarbon->lessThanOrEqualTo($today)) {
                    $errors[] = 'Hanya bisa pilih jadwal booking H+1!';
                }
            } catch (\Exception $ex) {
                $errors[] = 'Terjadi kesalahan format tanggal!';
            }

            if (count($errors) > 0) {
                $this->result['error'] = $errors;
                $this->result['message'] = 'Terjadi kesalahan validasi!';

                return Response::json($this->result, 400);
            }

            $packages = $data['package_ids'] ?? [];
            $items = $data['items'] ?? [];
            $genderTerapis = $data['gender_terapis'] ?? [];
            $customerNames = $data['customer_name'] ?? [];

            $tmpTotal = 0;
            $packageTotal = 0;

            foreach ($packages as $packageId) {
                $tmpPackage = DB::table('packages')
                    ->where('id', '=', $packageId)
                    ->first();

                $tmpTotal += $tmpPackage->price;
                $packageTotal += $tmpPackage->price;
            }

            $discount = 0;
            $discountPercentage = 0;
            $discountFlat = 0;

            $today = Carbon::today('Asia/Jakarta');
            $pattern = 'BOOK/'.$today->month;
            $autonumber = AutoNumberGenerator::generateBookAutoNumber($pattern);

            $newBooking = new BookingHeader;
            $newBooking->type = 'booking';
            $newBooking->code = $autonumber;
            $newBooking->branch_id = 1;
            $newBooking->branch_name = $branch->name;
            $newBooking->branch_code = $branch->code;
            $newBooking->user_id = $user->id;
            $newBooking->user_name = $user->name;
            $newBooking->user_phone = $user->phone;
            $newBooking->user_email = $user->email;
            $newBooking->booking_time = $data['time'];
            $newBooking->booking_date = $dateCarbon;
            $newBooking->booking_notes = $data['notes'] ?? '';

            $newBooking->voucher_code = $voucher->code ?? null;
            $newBooking->voucher_value = $discountPercentage > 0 ? $discountPercentage : $discountFlat;

            $newBooking->app_coupon_code = $voucherItem->code ?? null;
            $newBooking->total_discount = $discount;
            $newBooking->grand_total = $tmpTotal;

            $ppnPercentage = TaxUtilities::getPpnPercentage();

            $newBooking->payment_at = Carbon::now('Asia/Jakarta');
            $newBooking->payment_extra_charge = 0;
            $newBooking->payment_charge_ppn_percentage = $ppnPercentage;
            $newBooking->payment_charge_ppn_value = TaxUtilities::calculatePpnValue($tmpTotal, $ppnPercentage);
            $newBooking->payment_charge = 0;
            $newBooking->total_payment_before = $tmpTotal + $newBooking->payment_charge_ppn_value;
            $newBooking->total_payment = ceil($newBooking->total_payment_before);
            $newBooking->udf_03 = Str::random(20);
            $newBooking->status_id = 3;
            $paymentDueAt = Carbon::now('Asia/Jakarta')->addMinutes(30);
            $newBooking->payment_due_at = $paymentDueAt;

            $newBooking->is_birthday_discount = false;
            $newBooking->birthday_discount_percentage = 0;

            // create dynamic qrcode
            $qrisUtility = new QrisUtilities;
            $staticQris = Configuration::where('key', 'static_qris')->value('value');
            $amount = (int) ceil($newBooking->total_payment);

            $dynamicQris = $qrisUtility->convertToDynamic($staticQris, $amount);
            $newBooking->qr_payment_data = $dynamicQris;

            $newBooking->save();

            $tsNoteArr = [];
            if (isset($data['notes']) && ! empty($data['notes'])) {
                $tsNoteArr = $data['notes'];
            }

            $idx = 0;
            $tmpDiscountFlat = $discountFlat;

            // ==========================
            // Insert Package Details
            // ==========================
            foreach ($packages as $packageId) {
                $tmpPackage = DB::table('packages')
                    ->where('id', '=', $packageId)
                    ->first();

                $newDetail = new BookingDetail;
                $newDetail->header_id = $newBooking->id;
                $newDetail->package_id = $tmpPackage->id;
                $newDetail->package_name = $tmpPackage->name;
                $newDetail->package_code = $tmpPackage->code;

                if (! empty($tsNoteArr)) {
                    $newDetail->ts_notes = $tsNoteArr[$idx];
                }

                $newDetail->qty = 1;
                $newDetail->price = $tmpPackage->price;

                $discountDetail = 0;
                if ($discountPercentage > 0) {
                    $discountDetail = $tmpPackage->price * ($discountPercentage / 100);
                    $subtotal = $tmpPackage->price - $discountDetail;
                } elseif ($discountFlat > 0) {
                    if ($tmpPackage->price >= $tmpDiscountFlat) {
                        $discountDetail = $tmpPackage->price - $tmpDiscountFlat;
                        $tmpDiscountFlat = 0;
                    } else {
                        $discountDetail = $tmpPackage->price;
                        $tmpDiscountFlat -= $discountDetail;
                    }

                    $subtotal = $tmpPackage->price - $discountDetail;
                } else {
                    $subtotal = $tmpPackage->price;
                }

                $newDetail->discount_amount = $discountDetail;
                $newDetail->discount_percentage = $discountPercentage;
                $newDetail->subtotal = $subtotal;
                $newDetail->booking_time = $data['time'];
                $newDetail->therapist_gender = $genderTerapis[$idx];
                $newDetail->customer_name = $customerNames[$idx];
                $newDetail->status_id = 3;
                $newDetail->save();

                $idx++;
            }

            // ==========================
            // Insert Item Details
            // ==========================
            foreach ($items as $itemData) {
                $tmpItem = DB::table('items')
                    ->where('id', $itemData['item_id'])
                    ->first();

                $newDetail = new BookingDetail;
                $newDetail->header_id = $newBooking->id;
                $newDetail->item_id = $tmpItem->id;
                $newDetail->item_name = $tmpItem->name;
                $newDetail->item_code = $tmpItem->code;

                if (! empty($tsNoteArr)) {
                    $newDetail->ts_notes = $tsNoteArr[$idx];
                }

                $qty = intval($itemData['qty']);
                $price = $tmpItem->price;

                $newDetail->qty = $qty;
                $newDetail->price = $price;

                // hitung subtotal & discount
                $discountDetail = 0;

                if ($discountPercentage > 0) {
                    $discountDetail = ($price * $qty) * ($discountPercentage / 100);
                    $subtotal = ($price * $qty) - $discountDetail;
                } elseif ($discountFlat > 0) {
                    if (($price * $qty) >= $tmpDiscountFlat) {
                        $discountDetail = ($price * $qty) - $tmpDiscountFlat;
                        $tmpDiscountFlat = 0;
                    } else {
                        $discountDetail = ($price * $qty);
                        $tmpDiscountFlat -= $discountDetail;
                    }

                    $subtotal = ($price * $qty) - $discountDetail;
                } else {
                    $subtotal = ($price * $qty);
                }

                $newDetail->discount_amount = $discountDetail;
                $newDetail->discount_percentage = $discountPercentage;
                $newDetail->subtotal = $subtotal;
                $newDetail->booking_time = $data['time'];
                $newDetail->status_id = 3;

                $newDetail->save();
                $idx++;
            }

            $result[] = [
                'booking_id' => $newBooking->id,
                'code' => $newBooking->code,
                'trxCode' => $newBooking->code,
                'datetime' => $paymentDueAt->format('d-m-Y H:i'),
                'payment_due_at' => $paymentDueAt->format('d-m-Y H:i'),
                'qris' => $dynamicQris,
                'grand_total' => (float) $newBooking->grand_total,
                'payment_charge_ppn_percentage' => (float) $newBooking->payment_charge_ppn_percentage,
                'payment_charge_ppn_value' => (float) $newBooking->payment_charge_ppn_value,
                'total_payment' => (float) $newBooking->total_payment,
            ];
            $this->result['message'] = 'Booking sukses!';
            $this->result['response'] = $result;

            SendBookingCreatedNotification::dispatch($newBooking);

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/BookingController - submit ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan internal sistem';

            return Response::json($this->result, 500);
        }
    }

    public function submitStudio(Request $request)
    {
        try {
            $rules = [
                'package_ids' => 'required|array|min:1',
                'date' => 'required|array',
                'times' => 'required|array',
            ];

            $messages = [
                'package_ids.required' => 'Tidak Ada Treatment Yang dipilih',
                'date.required' => 'Tanggal Booking Belum dipilih',
                'times.required' => 'Waktu Booking Belum dipilih',
            ];

            // ==========================================
            // 1. UBAH INI SESUAI PERMINTAAN ANDA
            // ==========================================
            $data = $request->all();
            // ==========================================

            // Data dari JSON sudah otomatis array → tidak perlu json_decode
            $validator = Validator::make($data, $rules, $messages);

            if ($validator->fails()) {
                $this->result['response'] = $validator->errors()->all();
                $this->result['message'] = 'Terjadi kesalahan validasi';

                return Response::json($this->result, 400);
            }

            $user = $request->user();
            $branchId = 1;
            $dateCarbon = null;

            $errors = [];

            $branch = DB::table('branches')
                ->where('id', '=', $branchId)
                ->first();

            // ===== Validate date =====
            try {
                $today = Carbon::today('Asia/Jakarta');
                $dateCarbon = Carbon::createFromFormat('d-m-Y', $data['date']);

                if ($dateCarbon->lessThanOrEqualTo($today)) {
                    $errors[] = 'Hanya bisa pilih jadwal booking H+1!';
                }
            } catch (\Exception $ex) {
                $errors[] = 'Terjadi kesalahan format tanggal!';
            }

            if (count($errors) > 0) {
                $this->result['error'] = $errors;
                $this->result['message'] = 'Terjadi kesalahan validasi!';

                return Response::json($this->result, 400);
            }

            $packages = $data['package_ids'] ?? [];
            $items = $data['items'] ?? [];

            $tmpTotal = 0;
            $packageTotal = 0;

            foreach ($packages as $packageId) {
                $tmpPackage = DB::table('packages')
                    ->where('id', '=', $packageId)
                    ->first();

                $tmpTotal += $tmpPackage->price;
                $packageTotal += $tmpPackage->price;
            }

            $discount = 0;
            $discountPercentage = 0;
            $discountFlat = 0;

            $today = Carbon::today('Asia/Jakarta');
            $pattern = 'BOOK/'.$today->month;
            $autonumber = AutoNumberGenerator::generateBookAutoNumber($pattern);

            $newBooking = new BookingHeader;
            $newBooking->type = 'booking';
            $newBooking->code = $autonumber;
            $newBooking->branch_id = 1;
            $newBooking->branch_name = $branch->name;
            $newBooking->branch_code = $branch->code;
            $newBooking->user_id = $user->id;
            $newBooking->user_name = $user->name;
            $newBooking->user_phone = $user->phone;
            $newBooking->user_email = $user->email;
            $newBooking->booking_time = $data['time'];
            $newBooking->booking_date = $dateCarbon;

            $newBooking->voucher_code = $voucher->code ?? null;
            $newBooking->voucher_value = $discountPercentage > 0 ? $discountPercentage : $discountFlat;

            $newBooking->booking_notes = $data['notes'] ?? null;
            $newBooking->app_coupon_code = $voucherItem->code ?? null;
            $newBooking->total_discount = $discount;
            $newBooking->grand_total = $tmpTotal;

            $ppnPercentage = TaxUtilities::getPpnPercentage();

            $newBooking->payment_extra_charge = 0;
            $newBooking->payment_charge_ppn_percentage = $ppnPercentage;
            $newBooking->payment_charge_ppn_value = TaxUtilities::calculatePpnValue($tmpTotal, $ppnPercentage);
            $newBooking->payment_charge = 0;
            $newBooking->total_payment_before = $tmpTotal + $newBooking->payment_charge_ppn_value;
            $newBooking->total_payment = ceil($newBooking->total_payment_before);
            $newBooking->udf_03 = Str::random(20);
            $newBooking->status_id = 3;
            $newBooking->payment_due_at = Carbon::now('Asia/Jakarta')->addHour();

            $newBooking->is_birthday_discount = false;
            $newBooking->birthday_discount_percentage = 0;
            $newBooking->save();

            $tsNoteArr = [];
            if (isset($data['notes']) && ! empty($data['notes'])) {
                $tsNoteArr = $data['notes'];
            }

            $idx = 0;
            $tmpDiscountFlat = $discountFlat;

            // ==========================
            // Insert Package Details
            // ==========================
            foreach ($packages as $packageId) {
                $tmpPackage = DB::table('packages')
                    ->where('id', '=', $packageId)
                    ->first();

                $newDetail = new BookingDetail;
                $newDetail->header_id = $newBooking->id;
                $newDetail->package_id = $tmpPackage->id;
                $newDetail->package_name = $tmpPackage->name;
                $newDetail->package_code = $tmpPackage->code;

                if (! empty($tsNoteArr)) {
                    $newDetail->ts_notes = $tsNoteArr[$idx];
                }

                $newDetail->qty = 1;
                $newDetail->price = $tmpPackage->price;

                $discountDetail = 0;
                if ($discountPercentage > 0) {
                    $discountDetail = $tmpPackage->price * ($discountPercentage / 100);
                    $subtotal = $tmpPackage->price - $discountDetail;
                } elseif ($discountFlat > 0) {
                    if ($tmpPackage->price >= $tmpDiscountFlat) {
                        $discountDetail = $tmpPackage->price - $tmpDiscountFlat;
                        $tmpDiscountFlat = 0;
                    } else {
                        $discountDetail = $tmpPackage->price;
                        $tmpDiscountFlat -= $discountDetail;
                    }

                    $subtotal = $tmpPackage->price - $discountDetail;
                } else {
                    $subtotal = $tmpPackage->price;
                }

                $newDetail->discount_amount = $discountDetail;
                $newDetail->discount_percentage = $discountPercentage;
                $newDetail->subtotal = $subtotal;
                $newDetail->booking_time = $data['time'][$idx];
                $newDetail->status_id = 3;
                $newDetail->save();

                $idx++;
            }

            // ==========================
            // Insert Item Details
            // ==========================
            foreach ($items as $itemData) {
                $tmpItem = DB::table('items')
                    ->where('id', $itemData['item_id'])
                    ->first();

                $newDetail = new BookingDetail;
                $newDetail->header_id = $newBooking->id;
                $newDetail->item_id = $tmpItem->id;
                $newDetail->item_name = $tmpItem->name;
                $newDetail->item_code = $tmpItem->code;

                if (! empty($tsNoteArr)) {
                    $newDetail->ts_notes = $tsNoteArr[$idx];
                }

                $qty = intval($itemData['qty']);
                $price = $tmpItem->price;

                $newDetail->qty = $qty;
                $newDetail->price = $price;

                // hitung subtotal & discount
                $discountDetail = 0;

                if ($discountPercentage > 0) {
                    $discountDetail = ($price * $qty) * ($discountPercentage / 100);
                    $subtotal = ($price * $qty) - $discountDetail;
                } elseif ($discountFlat > 0) {
                    if (($price * $qty) >= $tmpDiscountFlat) {
                        $discountDetail = ($price * $qty) - $tmpDiscountFlat;
                        $tmpDiscountFlat = 0;
                    } else {
                        $discountDetail = ($price * $qty);
                        $tmpDiscountFlat -= $discountDetail;
                    }

                    $subtotal = ($price * $qty) - $discountDetail;
                } else {
                    $subtotal = ($price * $qty);
                }

                $newDetail->discount_amount = $discountDetail;
                $newDetail->discount_percentage = $discountPercentage;
                $newDetail->subtotal = $subtotal;
                $newDetail->booking_time = $data['time'][$idx];
                $newDetail->status_id = 3;

                $newDetail->save();
                $idx++;
            }

            $this->result['message'] = 'Booking sukses!';
            $this->result['response'] = [[
                'booking_id' => $newBooking->id,
                'code' => $newBooking->code,
                'trxCode' => $newBooking->code,
                'datetime' => Carbon::parse($newBooking->payment_due_at)->format('d-m-Y H:i'),
                'payment_due_at' => Carbon::parse($newBooking->payment_due_at)->format('d-m-Y H:i'),
                'qris' => $newBooking->qr_payment_data,
                'grand_total' => (float) $newBooking->grand_total,
                'payment_charge_ppn_percentage' => (float) $newBooking->payment_charge_ppn_percentage,
                'payment_charge_ppn_value' => (float) $newBooking->payment_charge_ppn_value,
                'total_payment' => (float) $newBooking->total_payment,
            ]];

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/BookingController - submit ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan internal sistem';

            return Response::json($this->result, 500);
        }
    }

    public function uploadPhoto(Request $request)
    {
        $filename = null;
        $previousFilename = null;

        try {

            $validator = Validator::make($request->all(), [
                'booking_id' => 'required',
                'image' => 'required|image|mimes:jpg,jpeg,png|max:4048',
            ], [
                'booking_id.required' => 'Booking Tidak ditemukan',
                'image.required' => 'Foto wajib diupload',
                'image.image' => 'File harus berupa gambar',
                'image.mimes' => 'Format harus jpg, jpeg, png',
                'image.max' => 'Ukuran maksimal 4MB',
            ]);

            if ($validator->fails()) {
                $this->result['error'] = $validator->errors()->first();
                $this->result['message'] = 'Terjadi kesalahan validasi!';

                return Response::json($this->result, 400);
            }

            $bookingData = BookingHeader::where('id', $request->input('booking_id'))
                ->where('user_id', $request->user()->id)
                ->first();

            if (! $bookingData) {
                $this->result['error'] = ['Booking tidak ditemukan'];
                $this->result['message'] = 'Booking tidak ditemukan';

                return Response::json($this->result, 404);
            }

            if ($request->hasFile('image')) {
                $previousFilename = $bookingData->payment_proof;
                $filename = $this->storeProofImage($request->file('image'));
                $bookingData->payment_proof = $filename;
                if (in_array((int) $bookingData->status_id, [13, 14], true)) {
                    $bookingData->status_id = 3;
                }
                $bookingData->save();

                if ($previousFilename !== $filename) {
                    $this->deleteProofImageIfExists($previousFilename);
                }

                $resultData = [
                    'status' => true,
                    'message' => 'Upload image berhasil',
                    'data' => [
                        'file_name' => $filename,
                        'file_url' => asset('storage/proofs/'.$filename),
                    ],
                ];
                $this->result['message'] = 'Upload Berhasil!';
                $this->result['response'] = $resultData;

                return Response::json($this->result, 200);
            }

            return response()->json([
                'status' => false,
                'message' => 'Photo tidak ditemukan',
            ], 400);

        } catch (\Exception $ex) {
            if (! empty($filename) && $filename !== $previousFilename) {
                $this->deleteProofImageIfExists($filename);
            }

            Log::channel('api_exception')->error('Api/BookingController - submit ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan internal sistem';

            return Response::json($this->result, 500);
        }
    }

    public function cancel(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'booking_id' => 'required',
            ], [
                'booking_id.required' => 'Booking Tidak ditemukan',
            ]);

            if ($validator->fails()) {
                $this->result['error'] = $validator->errors()->first();
                $this->result['message'] = 'Terjadi kesalahan validasi!';

                return Response::json($this->result, 400);
            }

            $bookingData = BookingHeader::query()
                ->where('id', $request->input('booking_id'))
                ->where('user_id', $request->user()->id)
                ->first();

            if (! $bookingData) {
                $this->result['error'] = ['Booking tidak ditemukan'];
                $this->result['message'] = 'Booking tidak ditemukan';

                return Response::json($this->result, 404);
            }

            if (! $this->canCustomerCancelBooking($bookingData)) {
                $this->result['error'] = ['Booking tidak bisa dibatalkan'];
                $this->result['message'] = 'Booking tidak bisa dibatalkan';

                return Response::json($this->result, 400);
            }

            $cancelledAt = Carbon::now('Asia/Jakarta');

            DB::transaction(function () use ($bookingData, $cancelledAt): void {
                $bookingHeader = BookingHeader::query()
                    ->whereKey($bookingData->id)
                    ->lockForUpdate()
                    ->first();

                if (! $this->canCustomerCancelBooking($bookingHeader)) {
                    return;
                }

                $bookingHeader->status_id = 10;
                $bookingHeader->canceled_at = $cancelledAt;
                $bookingHeader->save();
            });

            $bookingData->refresh();

            if ((int) $bookingData->status_id !== 10) {
                $this->result['error'] = ['Booking tidak bisa dibatalkan'];
                $this->result['message'] = 'Booking tidak bisa dibatalkan';

                return Response::json($this->result, 400);
            }

            SendBookingCancelledNotification::dispatch($bookingData, 'customer');

            $this->result['message'] = 'Booking berhasil dibatalkan!';
            $this->result['response'] = [
                'booking_id' => $bookingData->id,
                'status_id' => (int) $bookingData->status_id,
                'canceled_at' => $bookingData->canceled_at?->toDateTimeString(),
            ];

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/BookingController - cancel ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan internal sistem';

            return Response::json($this->result, 500);
        }
    }

    private function canCustomerCancelBooking(?BookingHeader $bookingHeader): bool
    {
        if ($bookingHeader === null) {
            return false;
        }

        if ((int) $bookingHeader->status_id !== 3) {
            return false;
        }

        if (! empty($bookingHeader->payment_proof)) {
            return false;
        }

        if (! empty($bookingHeader->transaction_id)) {
            return false;
        }

        return true;
    }

    private function storeProofImage(UploadedFile $file): string
    {
        $filename = 'Proof_'.Carbon::now()->format('Y-m-d_H-i-s').'.'.$file->getClientOriginalExtension();
        $file->storeAs('', $filename, 'proofs');

        return $filename;
    }

    private function deleteProofImageIfExists(?string $filename): void
    {
        if (! empty($filename) && Storage::disk('proofs')->exists($filename)) {
            Storage::disk('proofs')->delete($filename);
        }
    }

    public function getMassageOptions(Request $request)
    {
        try {
            // Validate branch_id
            if (empty($request->query('branch_id'))) {
                $errors[] = 'branch_id tidak ditemukan';
            }

            if (count($errors) > 0) {
                $this->result['error'] = $errors;
                $this->result['message'] = 'Terjadi kesalahan parameter';

                return Response::json($this->result, 400);
            }

            $branchId = $request->query('branch_id');
            $options = DB::table('massage_options')
                ->where('branch_id', '=', $branchId)
                ->where('key', '=', 'strength')
                ->get();

            $optionArr = [];
            foreach ($options as $option) {
                $optionArr[] = [
                    'id' => $option->id,
                    'name' => $option->name,
                ];
            }

            $this->result['response'] = $optionArr;
            $this->result['message'] = 'Berhasil tarik data opsi refleksi';

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/BookingController - getMassageOptions ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan internal sistem';

            return Response::json($this->result, 500);
        }
    }

    public function getBookingSlots(Request $request)
    {
        try {
            $errors = [];

            // Validate date
            if (empty($request->query('date'))) {
                $errors[] = 'date tidak ditemukan';
            }

            // Validate branch_id
            if (empty($request->query('branch_id'))) {
                $errors[] = 'branch_id tidak ditemukan';
            }

            if (count($errors) > 0) {
                $this->result['error'] = $errors;
                $this->result['message'] = 'Terjadi kesalahan parameter';

                return Response::json($this->result, 400);
            }

            $branchId = $request->query('branch_id');
            $date = Carbon::createFromFormat('d-m-Y', $request->query('date'));

            $slotConfig = DB::table('configurations')
                ->where('key', '=', 'available_booking_slots')
                ->first();
            $availableSlots = intval($slotConfig->value_int);

            $slots = DB::table('master_booking_slots')
                ->where('branch_id', '=', $branchId)
                ->get();

            $bookingSlotArr = [];
            foreach ($slots as $slot) {
                $usedSlot = DB::table('transaction_booking_slots')
                    ->where('branch_id', '=', $branchId)
                    ->where('date', '=', $date->toDateTimeString())
                    ->where('time_start', '=', $slot->time_start)
                    ->where('status_id', '!=', 2)
                    ->count();

                if ($usedSlot < $availableSlots) {
                    $bookingSlotArr[] = $slot->time_start.' - '.$slot->time_end;
                }

                //                $bookingSlotArr[] = [
                //                    'time' => $slot->time,
                //                    'is_available' => $usedSlot < $availableSlots ? 1 : 0
                //                ];
            }

            $this->result['response'] = $bookingSlotArr;
            $this->result['message'] = 'Berhasil tarik data slot booking';

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/BookingController - getBookingSlots ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan internal sistem';

            return Response::json($this->result, 500);
        }
    }

    /**
     * API Function to get payment type list
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function getPaymentTypes(Request $request){
    //     try {
    //         $paymentTypes = DB::table('payment_types')
    //             ->where('status_id', '=', 1)
    //             ->get();

    //         $paymentTypeArr = [];
    //         foreach ($paymentTypes as $paymentType){
    //             $paymentTypeArr[] = [
    //                 'code' => $paymentType->doku_code,
    //                 'name' => $paymentType->name,
    //                 'image_path' => Storage::disk('payments')->url($paymentType->img_url),
    //                 'extra_fee_percentage' => floatval($paymentType->extra_fee_percentage),
    //                 'extra_fee_value' => intval($paymentType->extra_fee_value)
    //             ];
    //         }

    //         $this->result['response'] = $paymentTypeArr;
    //         $this->result['message'] = 'Sukses ambil data tipe pembayaran';
    //         return Response::json($this->result, 200);
    //     }
    //     catch (\Exception $ex){
    //         Log::channel('api_exception')->error('Api/BookingController - getPaymentTypes ERROR EX '. $ex);

    //         $this->result['error'] = $ex->getMessage();
    //         $this->result['message'] = 'Internal server error';
    //         return Response::json($this->result, 500);
    //     }
    // }

    public function validateVoucher(Request $request)
    {
        try {
            $rules = [
                'branch_id' => 'required',
                'voucher_code' => 'required',
            ];

            $messages = [
                'branch_id.required' => 'branch_id tidak ditemukan',
                'voucher_code.required' => 'voucher_code tidak ditemukan',
            ];

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules, $messages);

            if ($validator->fails()) {
                $this->result['response'] = $validator->errors()->all();
                $this->result['message'] = 'Terjadi kesalahan validasi';

                return Response::json($this->result, 400);
            }

            // $branchId = $data['branch_id'];
            $voucherCode = $data['voucher_code'];
            $voucher = null;

            $now = Carbon::now('Asia/Jakarta');
            $errors = [];

            // Validate voucher_code;
            $isOwnerCoupon = false;
            if (empty($voucherCode)) {
                $errors[] = 'Kode Voucher kosong!';
            } else {
                $voucherItem = DB::table('coupon_lists')
                    // ->where('branch_id', '=', $branchId)
                    ->where('code', '=', $voucherCode)
                    ->first();

                if (empty($voucherItem)) {
                    $errors[] = 'Kode Voucher tidak ditemukan!';
                } else {
                    $couponMaster = DB::table('coupons')
                        ->where('id', '=', $voucherItem->coupon_id)
                        ->first();

                    if ($couponMaster->status_id === 2) {
                        $errors[] = 'Kode Voucher tidak ditemukan!';
                    } else {
                        if ($couponMaster->is_foc === 0) {
                            $expiredDate = Carbon::parse($voucherItem->exp_date);
                            if ($expiredDate->lessThanOrEqualTo($now)) {
                                $errors[] = 'Kode Voucher sudah kadaluarsa!';
                            }
                        } else {
                            $isOwnerCoupon = true;
                        }

                        if ($voucherItem->is_used === 1) {
                            $errors[] = 'Kode Voucher sudah terpakai!';
                        } else {
                            $voucher = DB::table('coupons')
                                ->where('id', '=', $voucherItem->coupon_id)
                                ->first();
                        }
                    }
                }
            }

            if (count($errors) > 0) {
                $this->result['error'] = $errors;
                $this->result['message'] = 'Terjadi kesalahan validasi!';

                return Response::json($this->result, 400);
            }

            $this->result['response'] = [
                'discount_percentage' => intval($voucher->discount_percentage),
                'discount_amount' => intval($voucher->discount_amount),
                'is_skip_payment' => $isOwnerCoupon,
            ];

            $this->result['message'] = 'Validasi voucher sukses!';

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/BookingController - validateVoucher ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan pada sistem!';

            return Response::json($this->result, 500);
        }
    }
}

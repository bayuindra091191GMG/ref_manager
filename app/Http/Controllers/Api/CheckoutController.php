<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Libs\AutoNumberGenerator;
use App\Libs\Doku;
use App\Libs\QrisUtilities;
use App\Libs\TaxUtilities;
use App\Models\Cart;
use App\Models\Configuration;
use App\Models\Employee;
use App\Models\OrderVoucherDetail;
use App\Models\OrderVoucherHeader;
use App\Models\PosPaymentType;
use App\Models\TransactionDetail;
use App\Models\TransactionHeader;
use App\Models\TransactionInvoice;
use App\Models\TransactionPayment;
use App\Models\UserVoucher;
use App\Models\UserVoucherCode;
use App\Models\VoucherPackage;
use App\Models\VoucherPromoCode;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    private $result;

    public function __construct(){
        $this->result = [
            'error'         => [],
            'message'       => '',
            'warning'       => '',
            'request_id'    => ApiUtilities::generateRandomShortString(20),
            'response'      => []
        ];
    }

    public function getPaymentOptions(Request $request){
        try {
            $paymentTypes = DB::table('app_payment_types')
                ->where('status_id', '=', 1)
                ->get();

            $paymentTypeArr = [];
            foreach ($paymentTypes as $paymentType){
                $paymentTypeArr[] = [
                    'id' => $paymentType->id,
                    'code' => $paymentType->doku_code,
                    'name' => $paymentType->name,
                    'image_path' => Storage::disk('payments')->url($paymentType->img_url),
                    'extra_fee_percentage' => floatval($paymentType->extra_fee_percentage),
                    'extra_fee_value' => intval($paymentType->extra_fee_value)
                ];
            }

            $this->result['response'] = $paymentTypeArr;
            $this->result['message'] = 'Sukses ambil data tipe pembayaran';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/BookingController - getPaymentTypes ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    public function checkout(Request $request)
    {
        try {
            $data = $request->json()->all();
            $validator = Validator::make($data, [
                'details' => 'required|array|min:1',
                'details.*.id' => 'required|integer|exists:voucher_packages,id',
                'details.*.qty' => 'required|integer|min:1',
            ], [
                'details.required' => 'Wajib memilih voucher!',
                'details.*.id.exists' => 'Voucher tidak ditemukan!',
                'details.*.qty.min' => 'Minimum Qty is 1!',
            ]);

            if ($validator->fails()) {
                $this->result['response'] = $validator->errors()->all();
                $this->result['message'] = 'Terjadi kesalahan validasi';

                return Response::json($this->result, 400);
            }

            DB::beginTransaction();

            $user = $request->user();
            $header = new OrderVoucherHeader();
            $header->created_by = $user->id;
            $header->code = AutoNumberGenerator::generateAutonumberTrx('VCR');
            $header->type = 'normal';
            $header->status_id = 3;
            $paymentDueAt = Carbon::now('Asia/Jakarta')->addMinutes(30);
            $header->payment_at = Carbon::now('Asia/Jakarta');
            $header->payment_due_at = $paymentDueAt;
            $header->save();

            $grandTotal = 0;

            foreach ($data['details'] as $detail) {
                $voucher = VoucherPackage::find($detail['id']);
                $qty = (int) $detail['qty'];
                $price = (int) ($voucher->price ?? $detail['price'] ?? 0);
                $subtotal = $qty * $price;

                OrderVoucherDetail::create([
                    'header_id' => $header->id,
                    'price' => $price,
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                    'voucher_id' => $voucher->id,
                ]);

                $grandTotal += $subtotal;
            }

            $ppnPercentage = TaxUtilities::getPpnPercentage();

            $header->grand_total = $grandTotal;
            $header->payment_charge_ppn_percentage = $ppnPercentage;
            $header->payment_charge_ppn_value = TaxUtilities::calculatePpnValue($grandTotal, $ppnPercentage);
            $header->total_payment = TaxUtilities::calculateTotalWithPpn($grandTotal, $ppnPercentage);
            $qrisUtility = new QrisUtilities;
            $staticQris = Configuration::where('key', 'static_qris')->value('value');
            $header->qr_payment_data = $qrisUtility->convertToDynamic($staticQris, (int) ceil($header->total_payment));
            $header->save();

            DB::commit();

            $this->result['response'] = [
                'id' => $header->id,
                'code' => $header->code,
                'status' => $header->status ? $header->status->display : 'Pending',
                'grand_total' => $header->grand_total,
                'payment_charge_ppn_percentage' => $header->payment_charge_ppn_percentage,
                'payment_charge_ppn_value' => $header->payment_charge_ppn_value,
                'total_payment' => $header->total_payment,
                'payment_due_at' => $paymentDueAt->format('d-m-Y H:i'),
                'qris' => $header->qr_payment_data,
            ];
            $this->result['message'] = 'Sukses order voucher! Silahkan selesaikan pembayaran!';

            return Response::json($this->result, 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadPhoto(Request $request)
    {
        $filename = null;
        $previousFilename = null;

        try {
            $validator = Validator::make($request->all(), [
                'voucher_order_id' => 'required',
                'image' => 'required|image|mimes:jpg,jpeg,png|max:4048',
            ], [
                'voucher_order_id.required' => 'Transaksi voucher tidak ditemukan',
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

            $order = OrderVoucherHeader::where('id', $request->input('voucher_order_id'))
                ->where('created_by', $request->user()->id)
                ->first();

            if (! $order) {
                $this->result['error'] = ['Transaksi voucher tidak ditemukan'];
                $this->result['message'] = 'Transaksi voucher tidak ditemukan';

                return Response::json($this->result, 404);
            }

            if (! Schema::hasColumn('order_voucher_headers', 'payment_proof')) {
                $this->result['error'] = ['Kolom payment_proof belum tersedia'];
                $this->result['message'] = 'Upload bukti pembayaran belum tersedia';

                return Response::json($this->result, 500);
            }

            if ($request->hasFile('image')) {
                $previousFilename = $order->payment_proof;
                $filename = $this->storeProofImage($request->file('image'));
                $order->payment_proof = $filename;
                if (in_array((int) $order->status_id, [13, 14], true)) {
                    $order->status_id = 3;
                }
                $order->save();

                if ($previousFilename !== $filename) {
                    $this->deleteProofImageIfExists($previousFilename);
                }

                $this->result['message'] = 'Upload Berhasil!';
                $this->result['response'] = [
                    'file_name' => $filename,
                    'file_url' => asset('storage/proofs/'.$filename),
                ];

                return Response::json($this->result, 200);
            }

            $this->result['message'] = 'Photo tidak ditemukan';

            return Response::json($this->result, 400);
        } catch (\Exception $ex) {
            if (! empty($filename) && $filename !== $previousFilename) {
                $this->deleteProofImageIfExists($filename);
            }

            Log::channel('api_exception')->error('Api/CheckoutController - uploadPhoto ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan internal sistem';

            return Response::json($this->result, 500);
        }
    }


    public function cancelOrder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'voucher_order_id' => 'required',
            ], [
                'voucher_order_id.required' => 'Transaksi voucher tidak ditemukan',
            ]);

            if ($validator->fails()) {
                $this->result['error'] = $validator->errors()->first();
                $this->result['message'] = 'Terjadi kesalahan validasi!';

                return Response::json($this->result, 400);
            }

            $orderData = OrderVoucherHeader::query()
                ->where('id', $request->input('voucher_order_id'))
                ->where('created_by', $request->user()->id)
                ->first();

            if (! $orderData) {
                $this->result['error'] = ['Transaksi voucher tidak ditemukan'];
                $this->result['message'] = 'Transaksi voucher tidak ditemukan';

                return Response::json($this->result, 404);
            }

            if (! $this->canCustomerCancelVoucherOrder($orderData)) {
                $this->result['error'] = ['Voucher tidak bisa dibatalkan'];
                $this->result['message'] = 'Voucher tidak bisa dibatalkan';

                return Response::json($this->result, 400);
            }

            $cancelledAt = Carbon::now('Asia/Jakarta');

            DB::transaction(function () use ($orderData, $cancelledAt): void {
                $voucherOrder = OrderVoucherHeader::query()
                    ->whereKey($orderData->id)
                    ->lockForUpdate()
                    ->first();

                if (! $this->canCustomerCancelVoucherOrder($voucherOrder)) {
                    return;
                }

                $voucherOrder->status_id = 10;
                if (Schema::hasColumn('order_voucher_headers', 'canceled_at')) {
                    $voucherOrder->canceled_at = $cancelledAt;
                }
                $voucherOrder->save();
            });

            $orderData->refresh();

            if ((int) $orderData->status_id !== 10) {
                $this->result['error'] = ['Voucher tidak bisa dibatalkan'];
                $this->result['message'] = 'Voucher tidak bisa dibatalkan';

                return Response::json($this->result, 400);
            }

            $this->result['message'] = 'Voucher berhasil dibatalkan!';
            $this->result['response'] = [
                'voucher_order_id' => $orderData->id,
                'status_id' => (int) $orderData->status_id,
                'canceled_at' => $orderData->canceled_at ? Carbon::parse($orderData->canceled_at)->toDateTimeString() : null,
            ];

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/CheckoutController - cancelOrder ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan internal sistem';

            return Response::json($this->result, 500);
        }
    }


    public function getOrders(Request $request){
        try{
            $errors = [];
            $skip = 0;
            $statusStr = null;
            $statusId = null;

            // Validate status
            if(empty($request->query('status'))){
                $errors[] = 'status tidak ditemukan';
            }
            else{
                $statusStr = $request->query('status');

                if($statusStr !== 'Semua'){
                    $status = DB::table('statuses')
                        ->where('display', 'like', '%'. $statusStr. '%')
                        ->first();

                    if(empty($status)){
                        $errors[] = 'status salah';
                    }
                    else{
                        $statusId = $status->id;
                    }
                }
            }

            if(count($errors) > 0){
                $this->result['error'] = $errors;
                $this->result['message'] = 'Terjadi kesalahan validasi';
                return Response::json($this->result, 400);
            }

            $user = $request->user();
            $headers = OrderVoucherHeader::with(['details', 'status'])
                ->where('created_by', $user->id);

            if($statusStr !== 'Semua'){
                $headers = $headers->where('status_id', '=', $statusId);
            }

            if(!empty($request->query('type'))){
                $headers = $headers->where('type', '=', $request->query('type'));
            }

            if(!empty($request->query('start_date')) && !empty($request->query('end_date'))){
                $startDate = Carbon::createFromFormat('d-m-Y', $request->query('start_date'));
                $startDate->startOfDay();

                $endDate = Carbon::createFromFormat('d-m-Y', $request->query('end_date'));
                $endDate->startOfDay()->addDays(1);

                $headers = $headers->whereBetween('created_at', array($startDate->toDateTimeString(), $endDate->toDateTimeString()));
            }

            if($request->exists('skip')){
                if($request->query('skip') != null){
                    $skip = $request->query('skip');
                }
            }

            $headers = $headers->skip($skip)
                ->take(20)
                ->orderBy('created_at', 'desc')
                ->get();
            $results = [];

            foreach($headers as $hd){
                if (empty($hd->qr_payment_data) && (float) ($hd->grand_total ?? 0) > 0 && Schema::hasColumn('order_voucher_headers', 'qr_payment_data')) {
                    $qrisUtility = new QrisUtilities;
                    $staticQris = Configuration::where('key', 'static_qris')->value('value');
                    $qrisAmount = $hd->total_payment ?: ($hd->grand_total + ($hd->payment_charge_ppn_value ?? 0));
                    $hd->qr_payment_data = $qrisUtility->convertToDynamic($staticQris, (int) ceil($qrisAmount));
                    $hd->save();
                }

                $rs = [
                    'id'                    => $hd->id,
                    'type'                  => $hd->type,
                    'code'                  => $hd->code,
                    'status'                => $hd->status->display,
                    'date'                  => Carbon::parse($hd->created_at)->format('d-m-Y H:i'),
                    'payment_due_at'        => ($hd->payment_due_at ?: Carbon::parse($hd->created_at)->addMinutes(30))->format('d-m-Y H:i'),
                    'grand_total'           => $hd->grand_total,
                    'payment_charge_ppn_percentage' => (float) ($hd->payment_charge_ppn_percentage ?? 0),
                    'payment_charge_ppn_value' => (float) ($hd->payment_charge_ppn_value ?? 0),
                    'total_payment'         => (float) ($hd->total_payment ?? $hd->grand_total ?? 0),
                    'discount'              => $hd->discount ?? 0,
                    'promo_code'            => $hd->promo_code ?? '-',
                    'pg_status'             => $hd->pg_status ?? '-',
                    'pg_service'            => $hd->pg_service ?? '-',
                    'pg_aquirer'            => $hd->pg_aquirer ?? '-',
                    'pg_settlement_date'    => $hd->pg_settlement_date ?? '-',
                    'point_bonus'           => $hd->point_bonus ?? 0,
                    'pg_cost'               => $hd->pg_cost ?? 0,
                    'payment_proof'         => $hd->payment_proof ?? '',
                    'payment_proof_url'     => ! empty($hd->payment_proof) ? asset('storage/proofs/'.$hd->payment_proof) : '',
                    'qris'                  => $hd->qr_payment_data ?? '',
                    'canceled_at'           => $hd->canceled_at ? '' : Carbon::parse($hd->canceled_at)->format('d-m-Y H:i'),
                    'details'               => [],
                    'payments'              => [],
                ];

                $tmpDetails = OrderVoucherDetail::where('header_id', $hd->id)->get();
                foreach($tmpDetails as $dt){
                    $voucher = VoucherPackage::find($dt->voucher_id);
                    $rs['details'][] = [
                        'voucher_id'    => $dt->voucher_id,
                        'voucher_code'  => $voucher ? $voucher->code : '-',
                        'voucher_name'  => $voucher ? ($voucher->name ?? $voucher->code) : '-',
                        'img'           => $voucher && $voucher->img != null ? asset('storage/images/vouchers/'.$voucher->img) : asset('images/logo-only.png'),
                        'qty'           => $dt->qty,
                        'price'         => $dt->price,
                        'subtotal'      => $dt->subtotal,
                    ];
                }

                $results[] = $rs;
            }

            $this->result['response'] = $results;
            $this->result['message'] = 'Success getting Order Vouchers!';
            return Response::json($this->result, 200);
        }
        catch(\Exception $ex){
            Log::channel('api_exception')->error('Api/CheckoutController - getOrders ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    private function storeProofImage(UploadedFile $file): string
    {
        $filename = 'Proof_'.Carbon::now()->format('Y-m-d_H-i-s').'.'.$file->getClientOriginalExtension();
        $file->storeAs('', $filename, 'proofs');

        return $filename;
    }

    private function canCustomerCancelVoucherOrder(?OrderVoucherHeader $voucherOrder): bool
    {
        if ($voucherOrder === null) {
            return false;
        }

        if ((int) $voucherOrder->status_id !== 3) {
            return false;
        }

        if (! empty($voucherOrder->payment_proof)) {
            return false;
        }

        return true;
    }

    private function deleteProofImageIfExists(?string $filename): void
    {
        if (! empty($filename) && Storage::disk('proofs')->exists($filename)) {
            Storage::disk('proofs')->delete($filename);
        }
    }
}

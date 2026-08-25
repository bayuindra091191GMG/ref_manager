<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendBookingCreatedNotification;
use App\Libs\ApiUtilities;
use App\Libs\AutoNumberGenerator;
use App\Libs\QrisUtilities;
use App\Libs\TaxUtilities;
use App\Models\Configuration;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Package;
use App\Models\PreweddingDetail;
use App\Models\PreweddingHeader;
use App\Models\TransactionDetail;
use App\Models\TransactionHeader;
use App\Models\TransactionInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PranikahBookingController extends Controller
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

    public function submitPranikah(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'gender_terapis' => 'required|in:L,P',
            'notes' => 'nullable|string',
            'pranikah_id' => 'required|integer|exists:prewedding_packages,id',
            'details' => 'required|array|min:1',
            'details.*.type' => 'required|in:package,item',
            'details.*.id' => 'required|integer',
            'details.*.date' => 'required|date',
            'details.*.time' => 'required',
        ], [
            'details.required' => 'Detail wajib diisi',
            'details.*.type.in' => 'Type harus package atau item',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->input('details', []) as $detail) {
            if ($detail['type'] === 'package' && ! Package::query()->whereKey($detail['id'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail package tidak ditemukan',
                ], 422);
            }

            if ($detail['type'] === 'item' && ! Item::query()->whereKey($detail['id'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail item tidak ditemukan',
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            $details = $request->input('details');
            $member = $request->user();
            $employee = Employee::where('name', 'Aplikasi')->first();
            $header = PreweddingHeader::create([
                'code' => AutoNumberGenerator::generateAutonumberTrx('PW'),
                'employee_id' => $employee ? $employee->id : null,
                'member_id' => $member->id,
                'member_name' => $member->name,
                'member_phone' => $member->phone,
                'package_id' => $request->pranikah_id,
                'is_from_application' => 1,
                'status_id' => 3,
                'notes' => $request->notes,
            ]);

            $posCode = AutoNumberGenerator::generateAutonumberTrx('POS');

            $transaction = TransactionHeader::create([
                'branch_id' => 1,
                'is_prewed' => 1,
                'code' => $posCode,
                'member_name' => $member->name,
                'member_phone' => $member->phone,
                'status_id' => 9,
                'foc_type' => 'no',
            ]);

            $header->update([
                'transaction_id' => $transaction->id,
            ]);

            $grandTotal = 0;

            foreach ($details as $detail) {
                if ($detail['type'] == 'package') {
                    $package = Package::findOrFail($detail['id']);

                    $price = $package->price ?? 0;
                    $duration = $package->duration ?? 60;
                    $packageId = $detail['id'];
                    $itemId = null;
                } else {
                    $item = Item::findOrFail($detail['id']);

                    $price = $item->price ?? 0;
                    $duration = $item->duration ?? 60;
                    $packageId = null;
                    $itemId = $detail['id'];
                }

                $grandTotal += $price;

                $preweddingDetail = PreweddingDetail::create([
                    'header_id' => $header->id,
                    'package_id' => $packageId,
                    'item_id' => $itemId,
                    'date' => $detail['date'],
                    'duration' => $duration,
                    'qty' => 1,
                    'price' => $price,
                    'subtotal' => $price,
                    'status_id' => 3,
                ]);

                $transactionDetail = TransactionDetail::create([
                    'header_id' => $transaction->id,
                    'package_id' => $packageId,
                    'item_id' => $itemId,
                    'status_id' => 9,
                    'qty' => 1,
                    'price' => 0,
                    'subtotal' => 0,
                    'start_time' => $detail['time'],
                    'end_time' => null,
                    'new_order_qty' => 1,
                ]);

                $preweddingDetail->update([
                    'transaction_detail_id' => $transactionDetail->id,
                ]);
            }

            TransactionInvoice::create([
                'branch_id' => 1,
                'header_ids' => $transaction->id,
                'code' => $posCode,
                'subtotal' => $grandTotal,
                'pb2' => 0,
                'grandtotal' => $grandTotal,
                'return_value' => 0,
                'member_name' => $member->name,
                'member_phone' => $member->phone,
                'status_id' => 9,
            ]);

            $qrisUtility = new QrisUtilities;
            $staticQris = Configuration::where('key', 'static_qris')->value('value');
            $paymentChargePpnPercentage = TaxUtilities::getPpnPercentage();
            $paymentChargePpnValue = TaxUtilities::calculatePpnValue($grandTotal, $paymentChargePpnPercentage);
            $totalPayment = TaxUtilities::calculateTotalWithPpn($grandTotal, $paymentChargePpnPercentage);
            $dynamicQris = $qrisUtility->convertToDynamic($staticQris, (int) ceil($totalPayment));

            $paymentPayload = [
                'qr_payment_data' => $dynamicQris,
            ];

            if (Schema::hasColumn('prewedding_headers', 'payment_charge_ppn_percentage')) {
                $paymentPayload['payment_charge_ppn_percentage'] = $paymentChargePpnPercentage;
            }

            if (Schema::hasColumn('prewedding_headers', 'payment_charge_ppn_value')) {
                $paymentPayload['payment_charge_ppn_value'] = $paymentChargePpnValue;
            }

            if (Schema::hasColumn('prewedding_headers', 'total_payment')) {
                $paymentPayload['total_payment'] = $totalPayment;
            }

            $header->update($paymentPayload);

            DB::commit();

            SendBookingCreatedNotification::dispatch($header, 'pranikah');

            $paymentDueAt = Carbon::now('Asia/Jakarta')->addMinutes(30);

            $this->result['message'] = 'Booking sukses!';
            $this->result['response'] = [[
                'pranikah_id' => $header->id,
                'code' => $header->code,
                'trxCode' => $header->code,
                'datetime' => $paymentDueAt->format('d-m-Y H:i'),
                'payment_due_at' => $paymentDueAt->format('d-m-Y H:i'),
                'qris' => $dynamicQris,
                'grand_total' => (float) $grandTotal,
                'payment_charge_ppn_percentage' => (float) $paymentChargePpnPercentage,
                'payment_charge_ppn_value' => (float) $paymentChargePpnValue,
                'total_payment' => (float) $totalPayment,
            ]];

            return Response::json($this->result, 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadPhotoPranikah(Request $request)
    {
        $filename = null;
        $previousFilename = null;

        try {
            $validator = Validator::make($request->all(), [
                'pranikah_id' => 'required',
                'image' => 'required|image|mimes:jpg,jpeg,png|max:4048',
            ], [
                'pranikah_id.required' => 'Pranikah Tidak ditemukan',
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

            $bookingData = PreweddingHeader::where('id', $request->input('pranikah_id'))
                ->where('member_id', $request->user()->id)
                ->where('is_from_application', 1)
                ->first();

            if (! $bookingData) {
                $this->result['error'] = ['Pranikah tidak ditemukan'];
                $this->result['message'] = 'Pranikah tidak ditemukan';

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

                $this->result['message'] = 'Upload Berhasil!';
                $this->result['response'] = [
                    'status' => true,
                    'message' => 'Upload image berhasil',
                    'data' => [
                        'file_name' => $filename,
                        'file_url' => asset('storage/proofs/'.$filename),
                    ],
                ];

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

            Log::channel('api_exception')->error('Api/PranikahBookingController - upload ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan internal sistem';

            return Response::json($this->result, 500);
        }
    }

    public function getPranikahStatus(Request $request, int $id)
    {
        try {
            $header = PreweddingHeader::with('status')
                ->where('id', $id)
                ->where('member_id', $request->user()->id)
                ->where('is_from_application', 1)
                ->first();

            if (! $header) {
                $this->result['error'] = ['Pranikah tidak ditemukan'];
                $this->result['message'] = 'Pranikah tidak ditemukan';

                return Response::json($this->result, 404);
            }

            $this->result['response'] = [
                'id' => $header->id,
                'code' => $header->code,
                'status_id' => $header->status_id,
                'status' => $header->status->display ?? '',
                'proof_uploaded' => ! empty($header->payment_proof),
                'payment_proof_url' => ! empty($header->payment_proof)
                    ? asset('storage/proofs/'.$header->payment_proof)
                    : '',
                'payment_approved' => (int) $header->status_id === 13,
                'payment_rejected' => (int) $header->status_id === 14,
            ];
            $this->result['message'] = 'Sukses ambil status pranikah!';

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/PranikahBookingController - getPranikahStatus ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan internal sistem';

            return Response::json($this->result, 500);
        }
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
}

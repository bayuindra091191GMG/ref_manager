<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Libs\AutoNumberGenerator;
use App\Models\BookingDetail;
use App\Models\BookingHeader;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use KMLaravel\GeographicalCalculator\Facade\GeoFacade;

class BookingControllerOld extends Controller
{
    private array $result;

    public function __construct(){
        $this->result = [
            'error'         => [],
            'message'       => '',
            'warning'       => '',
            'request_id'    => ApiUtilities::generateRandomShortString(20),
            'response'      => []
        ];
    }

    public function getBookingStatuses(){
        try {
            $statusArr = [
                'Semua',
                'Pending',
                'Siap Diproses',
                'Aktif',
                'Batal'
            ];

            $this->result['response'] = $statusArr;
            $this->result['message'] = 'Sukses ambil data status booking!';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/BookingController - getBookingStatuses ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    public function get(Request $request){
        try{
            $user = $request->user();

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

            $headers = BookingHeader::with(['booking_details', 'status'])
                ->where('user_id', $user->id);

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

            // Get oura cs phone
            $csPhoneConfig = DB::table('configurations')
                ->where('key', '=', 'oura_cs_phone')
                ->first();

            $headers = $headers->skip($skip)
                ->take(20)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($headers as $header){
                $bookingStatus = $header->status->display;
                // if($header->status_id == 9){
                //     $bookingStatus = 'Aktif';
                // }

                $startReviewDate = Carbon::createFromDate(2023, 12, 5, 'Asia/Jakarta');
                $bookingDate = Carbon::parse($header->created_at);

                $nResult = [
                    'date' => Carbon::parse($header->created_at)->format('d-m-Y H:i'),
                    'code' => $header->code,
                    'branch' => $header->branch->name,
                    'branch_lat' => $header->branch->latitude,
                    'branch_long' => $header->branch->longitude,
                    'status' => $bookingStatus,
                    'discount' => floatval($header->total_discount),
                    'total' => floatval($header->total_payment),
                    'payment_url' => $header->pg_url ?? '',
                    'details' => [],
                    'cs_phone' => $csPhoneConfig ? $csPhoneConfig->value : '0',
                ];

                //get details
                foreach ($header->booking_details as $detail){
                    $type = 'package';
                    if(!empty($detail->item_id)){
                        $type = 'item';
                    }

                    // Check for review
                    $userReview = DB::table('user_reviews')
                        ->where('booking_detail_id', '=', $detail->id)
                        ->first();

                    $star = 0;
                    $isReviewed = false;

                    if($bookingDate->lessThanOrEqualTo($startReviewDate)){
                        $isReviewed = true;
                    }
                    else{
                        if(!empty($userReview)){
                            $isReviewed = $userReview->status_id == 9;
                            $star = $userReview->star;
                        }
                        else{
                            $isReviewed = true;
                        }
                    }

                    $nDetail = [
                        'type' => $type,
                        'package_name' => $detail->package_name ?? '',
                        'item_name' => $detail->item_name ?? '',
                        // 'booking_time' => !empty($detail->start_time) ? Carbon::parse($detail->start_time)->format('d-m-Y H:i') : '',
                        'booking_time' => $detail->booking_time,
                        'price' => floatval($detail->price),
                        'discount' => floatval($detail->discount_amount),
                        'subtotal' => floatval($detail->subtotal),
                        'room' => '',
                        'therapist' => '',
                        'review' => [
                            'id' => $userReview ->id ?? 0,
                            'is_reviewed' => $isReviewed,
                            'star' => $star
                        ]
                    ];

                    $nResult['details'][] = $nDetail;
                }

                $this->result['response'][] = $nResult;
            }

            $this->result['message'] = 'Sukses ambil data booking!';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/BookingController - get ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    public function submit(Request $request){
        try{
            $rules = [
                'branch_id' => 'required',
                'package_ids' => 'required',
                'date' => 'required',
                'time' => 'required'
            ];

            $messages = [
                'branch_id.required' => 'branch_id tidak ditemukan',
            ];

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules, $messages);

            if ($validator->fails()) {
                $this->result['response'] = $validator->errors()->all();
                $this->result['message'] = 'Terjadi kesalahan validasi';
                return Response::json($this->result, 400);
            }

            $user = $request->user();
            $branchId = $data['branch_id'];
            $dateCarbon = null;

            $errors = [];

            $branch = DB::table('branches')
                ->where('id', '=', $branchId)
                ->first();

            // Validate date
            try {
                $today = Carbon::today('Asia/Jakarta');
                $dateCarbon = Carbon::createFromFormat('d-m-Y', $data['date']);

                if($dateCarbon->lessThanOrEqualTo($today)){
                    $errors[] = 'Hanya bisa pilih jadwal booking H+1!';
                }
            }
            catch (\Exception $ex){
                $errors[] = 'Terjadi kesalahan format tanggal!';
            }

            if(count($errors) > 0){
                $this->result['error'] = $errors;
                $this->result['message'] = 'Terjadi kesalahan validasi!';
                return Response::json($this->result, 400);
            }

            $tmpTotal = 0;
            $packageTotal = 0;
            foreach($data['package_ids'] as $packageId){
                $tmpPackage = DB::table('packages')
                    ->where('id', '=', $packageId)
                    ->first();

                $tmpTotal += $tmpPackage->price;
                $packageTotal += $tmpPackage->price;
            }

            $discount = 0;
            $discountPercentage = 0;
            $discountFlat = 0;

            // $ppnPercentStr = DB::table('configurations')
            //     ->where('key', '=', 'ppn_percentage')
            //     ->value('value');
            // $ppnPercent = intval($ppnPercentStr);

            // $fee = 0;
            // $feePpnValue = 0;

            // $isSkipPayment = false;
            // if($type === 'book' && $tmpTotal < 1){
            //     $isSkipPayment = true;
            // }

            $today = Carbon::today('Asia/Jakarta');
            $pattern = 'BOOK/'. $today->month;
            $autonumber = AutoNumberGenerator::generateBookAutoNumber($pattern);

            $newBooking = new BookingHeader();
            $newBooking->type = 'booking';
            $newBooking->code = $autonumber;
            $newBooking->branch_id = $branchId;
            $newBooking->branch_name = $branch->name;
            $newBooking->branch_code = $branch->code;
            $newBooking->user_id = $user->id;
            $newBooking->user_name = $user->name;
            $newBooking->user_phone = $user->phone;
            $newBooking->user_email = $user->email;
            $newBooking->booking_time = $data['time'];
            $newBooking->voucher_code = $voucher->code ?? null;
            $newBooking->voucher_value = $discountPercentage > 0 ? $discountPercentage : $discountFlat;
            $newBooking->booking_notes = $data['notes'] ?? null;
            $newBooking->app_coupon_code = $voucherItem->code ?? null;
            $newBooking->total_discount = $discount;
            $newBooking->grand_total = $tmpTotal;
            $newBooking->booking_date = $dateCarbon;
            // $newBooking->payment_charge_ppn_percentage = $ppnPercent;
            // $newBooking->payment_charge_ppn_value = $feePpnValue;
            // $newBooking->payment_charge = $fee;

            // $ppnPercent = intval($ppnPercentStr);
            $newBooking->payment_extra_charge = 0;
            $newBooking->total_payment_before = $tmpTotal + $newBooking->payment_charge;
            $newBooking->total_payment = ceil($newBooking->total_payment_before);
            $newBooking->udf_03 = Str::random(20);
            $newBooking->status_id = 3;

            $newBooking->is_birthday_discount = false;
            $newBooking->birthday_discount_percentage = 0;
            $newBooking->save();

            $tsNoteArr = [];
            if(isset($data['notes']) && !empty($data['notes'])){
                $tsNoteArr = $data['notes'];
            }

            $idx = 0;
            $tmpDiscountFlat = $discountFlat;
            foreach($data['package_ids'] as $packageId){
                $tmpPackage = DB::table('packages')
                    ->where('id', '=', $packageId)
                    ->first();

                $newDetail = new BookingDetail();
                $newDetail->header_id = $newBooking->id;
                $newDetail->package_id = $tmpPackage->id;
                $newDetail->package_name = $tmpPackage->name;
                $newDetail->package_code = $tmpPackage->code;
                if(!empty($tsNoteArr)){
                    $newDetail->ts_notes = $tsNoteArr[$idx];
                }

                $newDetail->qty = 1;
                $newDetail->price = $tmpPackage->price;

                $discountDetail = 0;
                if($discountPercentage > 0){
                    $discountDetail = $tmpPackage->price * ($discountPercentage / 100);
                    $subtotal = $tmpPackage->price - $discountDetail;
                }
                elseif($discountFlat > 0){
                    if($tmpPackage->price >= $tmpDiscountFlat){
                        $discountDetail = $tmpPackage->price - $tmpDiscountFlat;
                        $tmpDiscountFlat = 0;
                    }
                    else{
                        $discountDetail = $tmpPackage->price;
                        $tmpDiscountFlat -= $discountDetail;
                    }

                    $subtotal = $tmpPackage->price - $discountDetail;
                }
                else{
                    $subtotal = $tmpPackage->price;
                }

                $newDetail->discount_amount = $discountDetail;
                $newDetail->discount_percentage = $discountPercentage;
                $newDetail->subtotal = $subtotal;
                $newDetail->booking_time = $data['time'];
                $newDetail->status_id = 3;
                $newDetail->save();

                $idx++;
            }

            $this->result['message'] = 'Booking sukses!';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/BookingController - submit ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan internal sistem';
            return Response::json($this->result, 500);
        }
    }

    public function getMassageOptions(Request $request){
        try {
            // Validate branch_id
            if(empty($request->query('branch_id'))){
                $errors[] = 'branch_id tidak ditemukan';
            }

            if(count($errors) > 0){
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
            foreach ($options as $option){
                $optionArr[] = [
                    'id' => $option->id,
                    'name' => $option->name
                ];
            }

            $this->result['response'] = $optionArr;
            $this->result['message'] = 'Berhasil tarik data opsi refleksi';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/BookingController - getMassageOptions ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan internal sistem';
            return Response::json($this->result, 500);
        }
    }

    public function getBookingSlots(Request $request){
        try {
            $errors = [];

            // Validate date
            if(empty($request->query('date'))){
                $errors[] = 'date tidak ditemukan';
            }

            // Validate branch_id
            if(empty($request->query('branch_id'))){
                $errors[] = 'branch_id tidak ditemukan';
            }

            if(count($errors) > 0){
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
            foreach ($slots as $slot){
                $usedSlot = DB::table('transaction_booking_slots')
                    ->where('branch_id', '=', $branchId)
                    ->where('date', '=', $date->toDateTimeString())
                    ->where('time_start', '=', $slot->time_start)
                    ->where('status_id', '!=', 2)
                    ->count();

                if($usedSlot < $availableSlots){
                    $bookingSlotArr[] = $slot->time_start. ' - '. $slot->time_end;
                }

//                $bookingSlotArr[] = [
//                    'time' => $slot->time,
//                    'is_available' => $usedSlot < $availableSlots ? 1 : 0
//                ];
            }

            $this->result['response'] = $bookingSlotArr;
            $this->result['message'] = 'Berhasil tarik data slot booking';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/BookingController - getBookingSlots ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan internal sistem';
            return Response::json($this->result, 500);
        }
    }

    /**
     * API Function to get payment type list
     * @param Request $request
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

    public function validateVoucher(Request $request){
        try {
            $rules = [
                'branch_id' => 'required',
                'voucher_code' => 'required',
            ];

            $messages = [
                'branch_id.required' => 'branch_id tidak ditemukan',
                'voucher_code.required' => 'voucher_code tidak ditemukan'
            ];

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules, $messages);

            if ($validator->fails()) {
                $this->result['response'] = $validator->errors()->all();
                $this->result['message'] = 'Terjadi kesalahan validasi';
                return Response::json($this->result, 400);
            }

            //$branchId = $data['branch_id'];
            $voucherCode = $data['voucher_code'];
            $voucher = null;

            $now = Carbon::now('Asia/Jakarta');
            $errors = [];

            // Validate voucher_code;
            $isOwnerCoupon = false;
            if(empty($voucherCode)){
                $errors[] = 'Kode Voucher kosong!';
            }
            else{
                $voucherItem = DB::table('coupon_lists')
                    //->where('branch_id', '=', $branchId)
                    ->where('code', '=', $voucherCode)
                    ->first();

                if(empty($voucherItem)){
                    $errors[] = 'Kode Voucher tidak ditemukan!';
                }
                else{
                    $couponMaster = DB::table('coupons')
                        ->where('id', '=', $voucherItem->coupon_id)
                        ->first();

                    if($couponMaster->status_id === 2){
                        $errors[] = 'Kode Voucher tidak ditemukan!';
                    }
                    else{
                        if($couponMaster->is_foc === 0){
                            $expiredDate = Carbon::parse($voucherItem->exp_date);
                            if($expiredDate->lessThanOrEqualTo($now)){
                                $errors[] = 'Kode Voucher sudah kadaluarsa!';
                            }
                        }
                        else{
                            $isOwnerCoupon = true;
                        }

                        if($voucherItem->is_used === 1){
                            $errors[] = 'Kode Voucher sudah terpakai!';
                        }
                        else{
                            $voucher = DB::table('coupons')
                                ->where('id', '=', $voucherItem->coupon_id)
                                ->first();
                        }
                    }
                }
            }

            if(count($errors) > 0){
                $this->result['error'] = $errors;
                $this->result['message'] = 'Terjadi kesalahan validasi!';
                return Response::json($this->result, 400);
            }

            $this->result['response'] = [
                'discount_percentage' => intval($voucher->discount_percentage),
                'discount_amount' => intval($voucher->discount_amount),
                'is_skip_payment' => $isOwnerCoupon
            ];

            $this->result['message'] = 'Validasi voucher sukses!';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/BookingController - validateVoucher ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan pada sistem!';
            return Response::json($this->result, 500);
        }
    }
}

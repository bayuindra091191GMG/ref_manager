<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Libs\AutoNumberGenerator;
use App\Libs\Doku;
use App\Models\AppPaymentType;
use App\Models\Cart;
use App\Models\Employee;
use App\Models\OrderVoucherDetail;
use App\Models\OrderVoucherHeader;
use App\Models\OrderVoucherPayment;
use App\Models\VoucherPromoCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CheckoutControllerOld extends Controller
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

    public function checkout(Request $request){
        try{
            $rules = [
                'cart_ids'      => 'required',
                'payment_id'    => 'required'
            ];

            $messages = [
                'cart_ids.required'     => 'Wajib memilih Cart!',
                'payment_id.required'   => 'Wajib memilih pembayaran!',
            ];

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules, $messages);

            if ($validator->fails()) {
                $this->result['response'] = $validator->errors()->all();
                $this->result['message'] = 'Terjadi kesalahan validasi';
                return Response::json($this->result, 400);
            }

            $cartIds = explode(',', $data['cart_ids']);

            //validate cart
            $tmpCart = [];
            foreach($cartIds as $cartId){
                $cart = Cart::where('id', $cartId)->first();
                if($cart != null){
                    //process
                    $tmpCart[] = $cart;
                }
            }

            if(count($tmpCart) <= 0){
                $this->result['message'] = 'Data Cart tidak ditemukan!';
                return Response::json($this->result, 404);
            }

            //validate payment
            $appPaymentType = AppPaymentType::where('id', $data['payment_id'])->first();
            if($appPaymentType == null){
                $this->result['message'] = 'Data Payment tidak ditemukan!';
                return Response::json($this->result, 404);
            }

            //process
            $user = $request->user();
            $header = new OrderVoucherHeader();
            $header->created_by = $user->id;
            $header->code = AutoNumberGenerator::generateAutonumberTrx('VCR');
            $header->type = 'normal';
            $header->save();
            $grandTotal = 0;

            //counting total and saving details
            foreach($tmpCart as $cartId){
                OrderVoucherDetail::create([
                    'header_id' => $header->id,
                    'price'     => $cartId->coupon->price,
                    'qty'       => $cartId->qty,
                    'subtotal'  => $cartId->qty * $cartId->coupon->price,
                    'total'     => $cartId->qty * $cartId->coupon->price,
                    'voucher_id'=> $cartId->voucher_id,
                ]);
                $grandTotal += $cartId->qty * $cartId->coupon->price;
                //delete the cart
                $cartId->delete();
            }

            //check for promo code
            if(!empty($data['voucher_promo_code'])){
                $promo = VoucherPromoCode::where('code', $data['voucher_promo_code'])->first();
                if($promo != null){
                    $header->promo_code = $promo->code;
                    $header->promo_id = $promo->id;
                    if($promo->discount_percentage != 0){
                        $header->discount = $grandTotal * $promo->discount_percentage/100;
                    }
                    else{
                        $header->discount = $promo->discount_amount;
                    }

                    //counting commission
                    $employee = Employee::where('id', $promo->employee_id)->first();
                    if($employee != null){
                        //count the employee commission
                    }
                }
            }

            $pgCost = 0;
            if($appPaymentType->extra_fee_percentage > 0){
                $pgCost += ($grandTotal * $appPaymentType->extra_fee_percentage / 100);
            }
            if($appPaymentType->extra_fee_value > 0){
                $pgCost += $appPaymentType->extra_fee_value;
            }
            $header->pg_cost = $pgCost;
            $header->status_id = 3;
            $header->grand_total = $grandTotal + $pgCost;
            $header->save();

            //payment
            $payment = OrderVoucherPayment::create([
                'payment_id'    => $appPaymentType->id,
                'payment_code'  => $appPaymentType->doku_code,
                'amount'        => $header->grand_total,
                'header_id'     => $header->id,
            ]);

            //Checkout using web version
            $result = Doku::jokulCheckout($header->grand_total, $header->code,
            $appPaymentType->doku_code, ApiUtilities::generateRandomShortString(10),);
            if($result['error'] !== 'none'){
                $this->result['response'] = $result['response'];
                $this->result['error'] = 'There is something wrong with DOKU API';
                $this->result['message'] = 'Internal server error';
                return Response::json($this->result, 500);
            }
            $expiredDateString = $result['response']->response->payment->expired_date;

            $payment->external_request_id = $result['response']->response->headers->request_id;
            $payment->external_expired_date = Carbon::createFromFormat('YmdHis', $expiredDateString,'Asia/Jakarta')->toDateTimeString();
            $payment->pg_url = $result['response']->response->payment->url;
            $payment->save();

            $this->result['response'] = [
                'payment_type_code' => $appPaymentType->doku_code,
                'external_url'      => $payment->pg_url,
            ];
            $this->result['message'] = 'Sukses order voucher! Silahkan selesaikan pembayaran!';
            return Response::json($this->result, 200);
        }
        catch(\Exception $ex){
            Log::channel('api_exception')->error('Api/CheckoutController - checkout ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
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
                $rs = [
                    'id'                    => $hd->id,
                    'type'                  => $hd->type,
                    'code'                  => $hd->code,
                    'status'                => $hd->status->display,
                    'date'                  => Carbon::parse($hd->created_at)->format('d-m-Y H:i'),
                    'grand_total'           => $hd->grand_total,
                    'discount'              => $hd->discount ?? 0,
                    'promo_code'            => $hd->promo_code ?? '-',
                    'pg_status'             => $hd->pg_status ?? '-',
                    'pg_service'            => $hd->pg_service ?? '-',
                    'pg_aquirer'            => $hd->pg_aquirer ?? '-',
                    'pg_settlement_date'    => $hd->pg_settlement_date ?? '-',
                    'point_bonus'           => $hd->point_bonus ?? 0,
                    'pg_cost'               => $hd->pg_cost ?? 0,
                    'canceled_at'           => $hd->canceled_at ? '' : Carbon::parse($hd->canceled_at)->format('d-m-Y H:i'),
                    'details'               => [],
                    'payments'              => OrderVoucherPayment::where('header_id', $hd->id)->first() ?? [],
                ];

                $tmpDetails = OrderVoucherDetail::with('coupon')->where('header_id', $hd->id)->get();
                foreach($tmpDetails as $dt){
                    $rs['details'][] = [
                        'voucher_id'    => $dt->voucher_id,
                        'voucher_code'  => $dt->coupon->code,
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
}

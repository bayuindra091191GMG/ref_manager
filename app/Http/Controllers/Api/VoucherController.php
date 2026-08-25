<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Libs\AutoNumberGenerator;
use App\Models\Coupon;
use App\Models\OrderVoucherDetail;
use App\Models\OrderVoucherHeader;
use App\Models\User;
use App\Models\UserPointHistory;
use App\Models\UserVoucher;
use App\Models\UserVoucherCode;
use App\Models\VoucherPackage;
use App\Models\VoucherPromoCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class VoucherController extends Controller
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

    public function get(Request $request){
        try{
            $skip = 0;
            $take = 10;
            if($request->exists('skip')){
                if($request->query('skip') > 0){
                    $skip = $request->query(key: 'skip');
                }
            }

            if($request->exists('take')){
                if($request->query('take') > 0){
                    $take = $request->query('take');
                }
            }

            $coupons = VoucherPackage::where('status_id', 1)
                ->skip($skip)
                ->take($take)->get();

            $data = [];
            foreach($coupons as $voucher){
                $data[] = [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'name' => $voucher->name,
                    'price' => $voucher->price,
                    'expired' => $voucher->expired,
                    'img' => $voucher->img != null ? asset('storage/images/vouchers/'. $voucher->img) : asset('images/logo-only.png'),
                    // 'qty' => $qty,
                    // 'picked' => $picked
                ];
            }

            $this->result['message'] = 'Success getting Vouchers!';
            $this->result['response'] = $data;
            return Response::json($this->result, 200);
        }
        catch(\Exception $ex){
            Log::channel('api_exception')->error('Api/VoucherController - get ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    public function getUserVoucher(Request $request){
        try{
            $skip = 0;
            $take = 10;
            if($request->exists('skip')){
                if($request->query('skip') > 0){
                    $skip = $request->query(key: 'skip');
                }
            }

            if($request->exists('take')){
                if($request->query('take') > 0){
                    $take = $request->query('take');
                }
            }

            $vouchers = UserVoucher::with(['coupon', 'voucher_package', 'user_voucher_code'])
                ->where('user_id', $request->user()->id)
                ->orderByRaw('(
                    SELECT MIN(CASE
                        WHEN user_voucher_codes.expired_date IS NULL THEN "9999-12-31 23:59:59"
                        ELSE user_voucher_codes.expired_date
                    END)
                    FROM user_voucher_codes
                    WHERE user_voucher_codes.header_id = user_vouchers.id
                ) ASC')
                ->skip($skip)
                ->take($take)->get();

            $data = [];
            foreach($vouchers as $vc){
                $voucherPackage = $vc->voucher_package;
                $coupon = $vc->coupon;
                $image = '-';
                if ($voucherPackage && ! empty($voucherPackage->img)) {
                    $image = asset('storage/images/vouchers/'.$voucherPackage->img);
                } elseif ($coupon && ! empty($coupon->img_path)) {
                    $image = asset("images/coupons/{$coupon->img_path}");
                }

                $tmp = [
                    'id'                => $vc->id,
                    'code'              => $vc->voucher_code,
                    'name'              => $voucherPackage->name ?? $coupon->name ?? $vc->voucher_code,
                    'img'               => $image,
                    'terms_condition'   => $vc->terms_condition ?? $voucherPackage->name ?? $coupon->terms_condition ?? '-',
                    'e_vouchers'        => [],
                ];

                $vcDts = UserVoucherCode::where('header_id', $vc->id)
                    ->orderByRaw('expired_date IS NULL')
                    ->orderBy('expired_date')
                    ->get();
                foreach($vcDts as $vcDt){
                    $tmp['e_vouchers'][] = [
                        'code'      => $vcDt->code,
                        'is_used'   => $vcDt->is_used,
                        'exp_date'  => $vcDt->expired_date ? Carbon::parse($vcDt->expired_date)->format('d-m-Y H:i') : '-',
                    ];
                }
                $data[] = $tmp;
            }

            $this->result['message'] = 'Success getting coupons!';
            $this->result['response'] = $data;
            return Response::json($this->result, 200);
        }
        catch(\Exception $ex){
            Log::channel('api_exception')->error('Api/VoucherController - getUserVoucher ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    public function getRedeemVoucher(Request $request){
        try{
            $skip = 0;
            $take = 10;
            if($request->exists('skip')){
                if($request->query('skip') > 0){
                    $skip = $request->query(key: 'skip');
                }
            }

            if($request->exists('take')){
                if($request->query('take') > 0){
                    $skip = $request->query('take');
                }
            }

            $coupons = Coupon::where('status_id', 1)
                ->where('can_redeem', 1)
                ->skip($skip)
                ->take($take)->get();

            $data = [];
            foreach($coupons as $coupon){
                $data[] = [
                    'id'                => $coupon->id,
                    'code'              => $coupon->code,
                    'img'               => $coupon->img_path ? asset("images/coupons/{$coupon->img_path}") : '-',
                    'disc_percentage'   => $coupon->discount_percentage ?? 0,
                    'disc_amount'       => $coupon->discount_amount ?? 0,
                    'duration'          => $coupon->duration ?? 0,
                    'price'             => $coupon->price ?? 0,
                    'terms_condition'   => $coupon->terms_condition ?? '-',
                    'point_needed'      => $coupon->redeem_points,
                ];
            }

            $this->result['message'] = 'Success getting coupons!';
            $this->result['response'] = $data;
            return Response::json($this->result, 200);
        }
        catch(\Exception $ex){
            Log::channel('api_exception')->error('Api/VoucherController - getRedeemVoucher ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    public function redeemVoucher(Request $request){
        try{
            $rules = [
                'voucher_id'    => 'required',
                'qty'           => 'required|min:1'
            ];

            $messages = [
                'voucher_id.required'   => 'Wajib memilih voucher!',
                'qty.required'          => 'Harus ada Qty',
                'qty.min'               => 'Minimum Qty is 1!'
            ];

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules, $messages);

            if ($validator->fails()) {
                $this->result['response'] = $validator->errors()->all();
                $this->result['message'] = 'Terjadi kesalahan validasi';
                return Response::json($this->result, 400);
            }

            $user = $request->user();
            $userData = User::where('id', $user->id)->first();
            if($userData == null){
                $this->result['message'] = 'Sorry something went wrong!';
                $this->result['error'] = 'User Auth Error!';
                return Response::json($this->result, 400);
            }

            $voucher = Coupon::where('id', $data['voucher_id'])
                ->where('can_redeem', 1)
                ->first();

            if($voucher == null){
                $this->result['message'] = 'Voucher not found!';
                $this->result['error'] = 'Voucher not found!';
                return Response::json($this->result, status: 404);
            }

            if($user->point < ($voucher->redeem_points * $data['qty'])){
                $this->result['message'] = 'Not enough Points!';
                $this->result['error'] = 'Not enough Points!';
                return Response::json($this->result, status: 400);
            }

            //redeem voucher
            $header = new OrderVoucherHeader();
            $header->created_by = $user->id;
            $header->code = AutoNumberGenerator::generateAutonumberTrx('VCR');
            $header->type = 'redeem';
            $header->status_id = 20;
            $header->grand_total = $data['qty'] * $voucher->redeem_points;
            $header->save();

            OrderVoucherDetail::create([
                'header_id' => $header->id,
                'price'     => $voucher->redeem_points,
                'qty'       => $data['qty'],
                'subtotal'  => $data['qty'] * $voucher->redeem_points,
                'total'     => $data['qty'] * $voucher->redeem_points,
                'voucher_id'=> $voucher->id,
            ]);

            $userVc = UserVoucher::create(attributes: [
                'voucher_id'        => $voucher->id,
                'voucher_code'      => $voucher->code,
                'terms_condition'   => $voucher->terms_condition,
                'status_id'         => 1,
                'type'              => 'voucher',
                'user_id'           => $header->created_by,
            ]);
            $now = Carbon::now('Asia/Jakarta');
            $tmpDate = $now->addDays($voucher->duration);

            $allVc = [];
            for($i = 0; $i<$data['qty']; $i++){
                //generate voucher
                $tmpVc = UserVoucherCode::create(attributes: [
                    'code'          => ApiUtilities::generateUniqueVoucherCode(),
                    'header_id'     => $userVc->id,
                    'expired_date'  => $tmpDate,
                    'is_used'       => 0,
                ]);

                $userVcModel = [
                    'code'          => $tmpVc->code,
                    'header_id'     => $userVc->id,
                    'expired_date'  => Carbon::parse($tmpDate)->format('d-m-Y H:i'),
                    'is_used'       => 0,
                ];

                $allVc[] = $userVcModel;
            }

            //deduct point
            $user->point -= $header->grand_total;
            $user->save();

            UserPointHistory::create([
                'user_id'       => $user->id,
                'user_name'     => $user->name,
                'user_phone'    => $user->phone,
                'user_email'    => $user->email,
                'type'          => 'redeem_voucher',
                'amount_add'    => 0,
                'amount_sub'    => $header->grand_total,
                'amount_result' => $user->point,
                'reference_1'   => $header->code,
            ]);

            $this->result['response'] = $allVc;
            $this->result['message'] = 'Success redeem voucher!';
            return Response::json($this->result, 200);
        }
        catch(\Exception $ex){
            Log::channel('api_exception')->error('Api/VoucherController - redeemVoucher ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    public function checkPromoCode(Request $request){
        try{
            if(!$request->exists('promo_code')){
                $this->result['message'] = 'Promo Code is Required!';
                $this->result['error'] = 'Promo Code is Required!';
                return Response::json($this->result, 400);
            }

            $promoCode = $request->query('promo_code');
            $promo = VoucherPromoCode::where('code', $promoCode)->first();
            if($promo == null){
                $this->result['message'] = 'Promo Code not found!';
                $this->result['error'] = 'Promo Code not found!';
                return Response::json($this->result, 404);
            }

            $this->result['response'] = [
                'code'                  => $promo->code,
                'discount_percentage'   => floatval( $promo->discount_percentage),
                'discount_amount'       => floatval($promo->discount_amount),
            ];
            $this->result['message'] = 'Promo Code Found!';
            return Response::json($this->result, 200);
        }
        catch(\Exception $ex){
            Log::channel('api_exception')->error('Api/VoucherController - checkPromoCode ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }
}

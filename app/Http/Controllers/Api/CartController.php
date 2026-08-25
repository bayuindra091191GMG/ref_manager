<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
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
            $user = $request->user();
            $carts = Cart::where('user_id', $user->id)->get();
            $data = [];
            foreach($carts as $cart){
                $data[] = [
                    'id'                        => $cart->id,
                    'coupon_id'                 => $cart->voucher_id,
                    'coupon_qty'                => $cart->qty,
                    'coupon_code'               => $cart->coupon->code,
                    'coupon_img'                => asset("images/coupons/{$cart->coupon->img_path}"),
                    'coupon_disc_percentage'    => $cart->coupon->discount_percentage ?? 0,
                    'coupon_disc_amount'        => $cart->coupon->discount_amount ?? 0,
                    'coupon_duration'           => $cart->coupon->duration ?? 0,
                    'coupon_price'              => $cart->coupon->price ?? 0,
                    'coupon_is_check'           => $cart->is_check,
                ];
            }

            $this->result['message'] = 'Success getting carts!';
            $this->result['response'] = $data;
            return Response::json($this->result, 200);
        }
        catch(\Exception $ex){
            Log::channel('api_exception')->error('Api/CartController - get ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    public function add(Request $request){
        try{
            $rules = [
                'voucher_id'    => 'required',
                'qty'           => 'required',
                'is_check'      => 'required',
            ];

            $messages = [
                'voucher_id.required'   => 'Voucher wajib dipilih',
                'qty.required'          => 'Minimal qty 1!',
                'is_check.required'     => 'Harus dicheck?',
            ];

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules, $messages);

            if ($validator->fails()) {
                $this->result['response'] = $validator->errors()->all();
                $this->result['message'] = 'Terjadi kesalahan validasi';
                return Response::json($this->result, 400);
            }

            $user = $request->user();
            $coupon = Coupon::where('id', $data['voucher_id'])->first();
            if($coupon == null){
                $this->result['message'] = 'Voucher tidak ditemukan!';
                return Response::json($this->result, 404);
            }

            $cart = Cart::where('user_id', $user->id)->where('voucher_id', $coupon->id)->first();
            if($cart == null){
                $cart = Cart::create([
                    'user_id'   => $user->id,
                    'voucher_id' => $coupon->id,
                    'qty'       => $data['qty'],
                    'is_check'  => $data['is_check'],
                ]);
            }
            else{
                $cart->qty = $data['qty'];
                $cart->is_check = $data['is_check'];
                $cart->save();
            }

            $result = [
                'id'                        => $cart->id,
                'coupon_id'                 => $cart->voucher_id,
                'coupon_code'               => $coupon->code,
                'coupon_img'                => asset("images/coupons/{$coupon->img_path}"),
                'coupon_disc_percentage'    => $coupon->discount_percentage ?? 0,
                'coupon_disc_amount'        => $coupon->discount_amount ?? 0,
                'coupon_duration'           => $coupon->duration ?? 0,
                'coupon_price'              => $coupon->price ?? 0,
                'coupon_qty'                => $cart->qty,
                'coupon_is_check'           => $cart->is_check,
            ];

            $this->result['message'] = 'Success add/update cart!';
            $this->result['response'] = $result;
            return Response::json($this->result, 200);
        }
        catch(\Exception $ex){
            Log::channel('api_exception')->error('Api/CartController - add ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    public function delete(Request $request){
        try{
            $rules = [
                'cart_id'    => 'required',
            ];

            $messages = [
                'cart_id.required'   => 'Cart tidak ditemukan',
            ];

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules, $messages);

            if ($validator->fails()) {
                $this->result['response'] = $validator->errors()->all();
                $this->result['message'] = 'Terjadi kesalahan validasi';
                return Response::json($this->result, 400);
            }

            $cart = Cart::where('id', $data['cart_id'])->first();
            if($cart == null){
                $this->result['response'] = $validator->errors()->all();
                $this->result['message'] = 'Cart tidak ditemukan!';
                return Response::json($this->result, 404);
            }
            $cart->delete();

            $this->result['message'] = 'Success delete cart!';
            return Response::json($this->result, 200);
        }
        catch(\Exception $ex){
            Log::channel('api_exception')->error('Api/CartController - delete ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }
}

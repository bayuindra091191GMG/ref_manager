<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Mail\SendOTP;
use App\Models\User;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller{
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

    public function checkUser(Request $request){
        try{
            $rules = array(
                'phone'             => 'required',
            );

            $message = array(
                'phone.required'    => 'No telp wajib diisi!',
            );

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules, $message);

            if ($validator->fails()) {
                $this->result['response'] = $validator->errors()->all();
                $this->result['message'] = 'Terjadi kesalahan validasi';
                return Response::json($this->result, 400);
            }

            $user = User::where('phone', $data['phone'])->first();
            if($user == null){
                $this->result['error'] = 'User tidak ditemukan!';
                return Response::json($this->result, 404);
            }
            $otpCode = ApiUtilities::generateRandomShortNumeric(4);

            $user->forgot_password_otp_code = $otpCode;
            $user->forgot_password_otp_expired_at = Carbon::now('Asia/Jakarta')->addHours(3);
            $user->save();

            // send OTP to email
            Mail::to($user->email)
                ->send(new SendOTP($otpCode, $user, 'Kode Otp Anda', 'Forgot Password'));

            $this->result['response'] = $user->email;
            $this->result['message'] = 'Sukses kirim OTP!';

            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/ForgotPasswordController - checkUser ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan pada sistem!';
            return Response::json($this->result, 500);
        }
    }

    public function verifyOtp(Request $request){
        try{
            $rules = array(
                'phone' => 'required',
                'otp_code' => 'required'
            );

            $message = array(
                'phone.required' => 'Nomor HP wajib diisi!',
                'otp_code.required' => 'Kode OTP wajib diisi!',
            );

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules, $message);

            if ($validator->fails()) {
                $this->result['response'] = $validator->errors()->all();
                $this->result['message'] = 'Terjadi kesalahan validasi';
                return Response::json($this->result, 400);
            }

            $user = User::where('phone', $data['phone'])->first();
            if($user == null){
                $this->result['error'] = 'User tidak ditemukan!';
                $this->result['message'] = 'Terjadi kesalahan validasi!';
                return Response::json($this->result, 404);
            }

            if(empty($user->forgot_password_otp_expired_at)){
                $this->result['error'] = 'Mohon kirim ulang kode OTP!';
                $this->result['message'] = 'Terjadi kesalahan validasi!';
                return Response::json($this->result, 400);
            }

            $now = Carbon::now('Asia/Jakarta');
            $expiredAt = Carbon::parse($user->forgot_password_otp_expired_at);
            if($now->greaterThanOrEqualTo($expiredAt)){
                $this->result['error'] = 'Mohon kirim ulang kode OTP!';
                $this->result['message'] = 'Terjadi kesalahan validasi!';
                return Response::json($this->result, 400);
            }

            if($user->forgot_password_otp_code !== $data['otp_code']){
                $this->result['message'] = 'Kode OTP Salah!';
                return Response::json($this->result, 400);
            }

            $uniqueCode = ApiUtilities::generateRandomShortNumeric(8);
            $user->forgot_password_unique_code = $uniqueCode;
            $user->save();

            $this->result['response'] = [
                'phone' => $data['phone'],
                'unique_id' => $uniqueCode
            ];

            $this->result['message'] = 'Sukses verifikasi OTP!';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/ForgotPasswordController - verifyOtp ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan pada sistem!';
            return Response::json($this->result, 500);
        }
    }

    public function setNewPassword(Request $request){
        try{
            $rules = array(
                'phone' => 'required',
                'password' => 'required',
                'unique_id' => 'required'
            );

            $message = array(
                'phone.unique' => 'No telp sudah terdaftar!',
                'phone.required' => 'No telp wajib diisi!',
                'password.required' => 'Kata Sandi wajib diisi!',
                'unique_id.required' => 'Kode Unik wajib diisi!'
            );

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules, $message);

            if ($validator->fails()) {
                $this->result['response'] = $validator->errors()->all();
                $this->result['message'] = 'Terjadi kesalahan validasi';
                return Response::json($this->result, 400);
            }

            $user = User::where('phone', $data['phone'])->first();
            if($user == null){
                $this->result['error'] = 'User tidak ditemukan!';
                $this->result['message'] = 'Terjadi kesalahan validasi!';
                return Response::json($this->result, 404);
            }

            if($user->forgot_password_unique_code !== $data['unique_id']){
                $this->result['error'] = 'Kode Unik salah atau kosong!';
                $this->result['message'] = 'Terjadi kesalahan validasi!';
                return Response::json($this->result, 400);
            }

            $user->password = Hash::make($data['password']);
            $user->save();

            $this->result['message'] = 'Sukses ubah kata sandi!';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/ForgotPasswordController - setNewPassword ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan pada sistem!';
            return Response::json($this->result, 500);
        }
    }

    public function updatePassword(Request $request){
        try{
            $rules = array(
                'phone'                 => 'required',
                'password'              => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required',
            );

            $message = array(
                'phone.unique' => 'No telp sudah terdaftar!',
                'phone.required' => 'No telp wajib diisi!',

                'password.required'              => 'Password wajib diisi',
                'password.min'                   => 'Password minimal 6 karakter',
                'password.confirmed'             => 'Password dan konfirmasi password tidak sama',
                'password_confirmation.required' => 'Retype password wajib diisi',

            );

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules, $message);

            if ($validator->fails()) {
                $this->result['response'] = $validator->errors()->all();
                $this->result['message'] = 'Terjadi kesalahan validasi';
                return Response::json($this->result, 400);
            }

            $user = User::where('phone', $data['phone'])->first();
            if($user == null){
                $this->result['error'] = 'User tidak ditemukan!';
                $this->result['message'] = 'Terjadi kesalahan validasi!';
                return Response::json($this->result, 404);
            }

            $user->password = Hash::make($data['password']);
            $user->is_password_set = 1;
            $user->save();

            $this->result['message'] = 'Sukses ubah kata sandi!';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/ForgotPasswordController - setNewPassword ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan pada sistem!';
            return Response::json($this->result, 500);
        }
    }
}

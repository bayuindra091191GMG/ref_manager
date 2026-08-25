<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Libs\Utilities;
use App\Mail\SendOTP;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
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

    public function register(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name'          => 'required|string|max:100',
                'phone'         => 'required|string|max:20|unique:users,phone',
                'email'         => 'nullable|email|max:100|unique:users,email',
                'gender'        => 'required|in:Laki-laki,Perempuan',
                'birthday_date' => 'required|date',
                'password'              => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required',
                'image'         => 'nullable|image|mimes:jpg,jpeg,png|max:8192'
            ],[
                'name.required'          => 'Nama wajib diisi',
                'phone.required'         => 'Nomor handphone wajib diisi',
                'phone.unique'           => 'Nomor handphone sudah digunakan',
                'email.email'            => 'Format email tidak valid',
                'email.unique'           => 'Email sudah digunakan',
                'gender.required'        => 'Gender wajib dipilih',
                'gender.in'              => 'Gender tidak valid',
                'birthday_date.required' => 'Tanggal lahir wajib diisi',
                'birthday_date.date'     => 'Format tanggal lahir tidak valid',

                'password.required'              => 'Password wajib diisi',
                'password.min'                   => 'Password minimal 6 karakter',
                'password.confirmed'             => 'Password dan konfirmasi password tidak sama',
                'password_confirmation.required' => 'Retype password wajib diisi',

                'image.image'            => 'File harus berupa gambar',
                'image.mimes'            => 'Format harus jpg, jpeg, png',
                'image.max'              => 'Ukuran maksimal 8MB'
            ]);

            if ($validator->fails()) {
                $validationErrors = $validator->errors()->all();
                $this->result['error'] = $validationErrors;
                $this->result['message'] = $validationErrors;
                return Response::json($this->result, 400);
            }

            DB::beginTransaction();

            $gender = $request->gender == "Laki-laki" ? "L" : "P";

            $imagePath = null;


            if($request->hasFile('image')){
                $file = $request->file('image');
                $filename = time().'_'.$file->getClientOriginalName();
                $file->storeAs('public/images/members', $filename);
                $imagePath = $filename;
            }

            $user = User::create([
                'name'          => $request->name,
                'phone'         => $request->phone,
                'email'         => $request->email,
                'gender'        => $gender,
                'birthday_date' => $request->birthday_date,
                'membership_id' => 1,
                'point'         => 0,
                'image_path'    => $imagePath,
                'password'      => Hash::make($request->password),
                'is_password_set' => 1
            ]);

            DB::commit();

            $this->result['response'] = [
                'id'    => $user->id,
                'name'  => $user->name,
                'phone' => $user->phone
            ];

            $this->result['message'] = 'Registrasi berhasil!';

            return Response::json($this->result, 200);

        }
        catch (\Exception $ex){

            DB::rollBack();

            Log::channel('api_exception')
                ->error('Api/RegisterController - register ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';

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
                Log::error("Validator message register : ". $validator->errors()->first());
                $this->result['response'] = $validator->errors()->first();
                $this->result['message'] = $validator->errors()->first();
                return Response::json($this->result, 400);
            }

            $user = User::where('phone', $data['phone'])->first();
            if($user == null){
                $this->result['message'] = 'User tidak ditemukan!';
                return Response::json($this->result, 404);
            }

            if($user->otp_code == $data['otp_code']){
                $user->status_id = 1;
                $user->active_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
                $user->membership_id = 1;
                $user->membership_status = 'ACTIVE';
                $user->membership_start_at = Carbon::today('Asia/Jakarta')->toDateTimeString();
                $user->save();
            }
            else{
                $this->result['message'] = 'Kode OTP Salah!';
                return Response::json($this->result, 400);
            }

            $this->result['message'] = 'Verifikasi OTP Sukses!';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/RegisterController - verifyOtp ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    public function resendOtp(Request $request){
        try{
            $rules = array(
                'phone'     => 'required'
            );

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                Log::error("Validator message register : ". $validator->errors()->first());
                $this->result['response'] = $validator->errors()->first();
                $this->result['message'] = $validator->errors()->first();
                return Response::json($this->result, 400);
            }

            $user = User::where('phone', $data['phone'])->first();
            if($user == null){
                $this->result['message'] = 'User tidak ditemukan!';
                return Response::json($this->result, 404);
            }

            $otpCode = ApiUtilities::generateRandomShortNumeric(4);
            $user->otp_code = $otpCode;
            $user->save();

            $otpString = 'Kode OTP Anda adalah '. $otpCode;

            Mail::to($user->email)
                ->send(new SendOTP($otpCode, $user, $otpString, 'Kode OTP Registrasi'));

            $this->result['message'] = 'Sukses kirim ulang kode OTP!';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/RegisterController - resendOtp ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

}

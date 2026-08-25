<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\Utilities;
use App\Models\FcmToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function submit(Request $request)
    {
        $identifier = trim((string) ($request->input('identifier') ?? $request->input('phone', '')));

        $validator = Validator::make([
            'identifier' => $identifier,
            'password' => $request->input('password'),
        ], [
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $userTmp = $this->findUserForLogin($identifier);

        if (empty($userTmp)) {
            return response()->json(['error' => ['Email / Nomor HP atau Kata Sandi Anda Salah!']], 400);
        }

        if ($userTmp->status_id === 2) {
            $data = [
                'email' => $userTmp->email,
            ];

            return response()->json($data, 360);
        }

        if (Hash::check($request->input('password'), $userTmp->password)) {
            config(['auth.guards.api.provider' => 'user']);

            $token = $request->input('token');

            if (! empty($token)) {
                FcmToken::updateOrCreate(
                    ['token' => $token],
                    ['user_id' => $userTmp->id]
                );
            }

            $data = [
                'password_set' => $userTmp->is_password_set ?? 0,
                'is_data_completed' => $userTmp->birthday_date == null ? 0 : 1,
                'username' => $userTmp->phone,
                'token' => $userTmp->createToken('MyApp')->accessToken,
                'fcm_token' => $token,
            ];

            return response()->json($data, 200);
        } else {
            return response()->json(['error' => ['Percobaan Login Anda Gagal!']], 400);
        }
    }

    private function findUserForLogin(string $identifier): ?User
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return User::whereRaw('LOWER(email) = ?', [strtolower($identifier)])
                ->first();
        }

        return User::where('phone', '=', $identifier)
            ->first();
    }

    public function external(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $userTmp = User::where('email', '=', $request->input('email'))
            ->first();
        // $userTmp = DB::table('users')
        //     ->where('phone', '=', request('phone'))
        //     ->first();

        // if register new user
        if (empty($userTmp)) {
            $randomString = Utilities::generateRandomStr(10);
            $userTmp = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('email').$randomString.$request->input('name')),
                'is_password_set' => 1,
            ]);
        }

        $data = [
            'password_set' => $userTmp->is_password_set ?? 0,
            'is_data_completed' => $userTmp->birthday_date == null ? 0 : 1,
            'username' => $userTmp->phone,
            'token' => $userTmp->createToken('MyApp')->accessToken,
        ];

        return response()->json($data, 200);
    }

    public function logout(Request $request)
    {
        //        dd($request);
        try {
            $user = Auth::guard('user-api')->user();

            if ($user) {
                FcmToken::where('token', $request->input('fcm_token'))->delete();

                $accessToken = $user->token();

                // Revoke the token
                $accessToken->revoke();

                DB::table('oauth_access_tokens')->where('id', $accessToken->id)->delete();

                return Response::json([
                    'message' => 'Logout Berhasil!',
                ]);
            }
        } catch (\Exception $exception) {
            Log::channel('api_exception')->error('Api/LoginController - logout ERROR EX '.$exception);

            return Response::json([
                'error' => 'Internal server error',
                500,
            ]);
        }
    }
}

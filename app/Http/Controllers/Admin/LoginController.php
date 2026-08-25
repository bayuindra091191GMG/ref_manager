<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class LoginController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('guest:admin')->except('logout');
    }

    public function mainLoginShow(){
        $branches = DB::table('branches')->get();

        return view('admin.main', compact('branches'));
    }

    // public function showLoginForm($code){
    public function showLoginForm(){

        $branches = DB::table('branches')
            ->where('id', '>', 0)
            ->get();

        if($branches->count() == 0){
            return 'Internal System Error';
        }

        $data = [
            'branches' => $branches,
        ];

        return view('admin.login')->with($data);
    }

    public function login(Request $request){
        $this->validate($request, [
            'username' => 'required|max:30',
            'password' => 'required'
        ],[
            'username.required' => 'Username wajib diisi!',
            'username.max' => 'Username maksimal 30 karakter!',
            'password.required' => 'Kata Sandi wajib diisi!'
        ]);
//        $code = "CVPN";
//
//        session()->put('connection_name', 'mysql');
//
//        $branch = DB::table('branches')
//            ->where('code', '=', $code)
//            ->first();
//        if(empty($branch)){
//            dd($code);
//            return redirect()->back()->withErrors('Username atau Kata Sandi salah!', 'default')->withInput($request->only('email'));
//        }

        $branchId = $request->input('branch_id');

        $branch = DB::table('branches')
            ->where('id', '=', $branchId)
            ->first();

        if (empty($branch)) {
            return redirect()->back()->withErrors('Username atau Kata Sandi salah!', 'default')->withInput($request->only('email'));
        }

        $user = AdminUser::on(session()->get('connection_name'))
            ->where('username', '=', $request->username)
            ->where('branch_id', '=', $branch->id)
            ->where('status_id', '=', 1)
            ->first();

        if (!$user) {
//            dd($request->username, $branch->id);
            return redirect()->back()->withErrors('Username atau Kata Sandi salah!', 'default')->withInput($request->only('email'));
        }

        if(Hash::check($request->input('password'), $user->password)){
            Auth::guard('admin')->login($user);

//            session()->put('connection_name', $branch->code);

            return Redirect::route('admin.dashboard');
        }
        else{
            return redirect()->back()->withErrors('Username atau Kata Sandi salah!', 'default')->withInput($request->only('email'));
        }
    }

    public function logout(Request $request){
        $admin = Auth::guard('admin')->user();
        $branch = DB::table('branches')
            ->where('id', '=', $admin->branch_id)
            ->first();

        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login', ['code' => $branch->code]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Transformer\AppCustomerTransformer;
use App\Transformer\UserPointHistoryTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class AppCustomerController extends Controller
{
    public function index(){
        try {
            $admin = Auth::guard('admin')->user();
            $filterBranchId = $admin->branch_id;

            return view('admin.app_customer.index',compact('filterBranchId'));
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/AppCustomerController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndex(Request $request){
        $users = DB::table('users')
            ->join('statuses', 'users.status_id', '=', 'statuses.id')
            ->join('app_memberships', 'users.membership_id', '=', 'app_memberships.id')
            ->select('users.id as id',
                'users.name as name',
                'users.phone as phone',
                'users.email as email',
                'users.created_at as created_at',
                'users.membership_status as membership_status',
                'users.status_id as status_id',
                'users.point as point',
                'users.birthday_date as birthday_date',
                'statuses.display as status_display',
                'app_memberships.name as membership_name');

        return DataTables::of($users)
            ->setTransformer(new AppCustomerTransformer())
            ->make(true);
    }

    public function edit($id){
        $customer = User::find($id);
        if(empty($customer)){
            Session::flash('error', 'BAD REQUEST!');
            return redirect()->back();
        }

        return view('admin.app_customer.edit', compact('customer'));
    }

    public function update(Request $request, $id){
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:100',
            ],[
                'email.required' => 'Email wajib diisi!',
                'email.email' => 'Format email salah!',
                'email.max' => 'Email maksimal 100 karakter!'
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $admin = Auth::guard('admin')->user();

            $updatedCustomer = User::find($id);
            if($request->filled('new_password') && $request->filled('retry_password')){
                $newPassword = trim($request->input('new_password'));
                $retryPassword = trim($request->input('retry_password'));
                if($newPassword !== $retryPassword){
                    Session::flash('error', 'Kata Sandi tidak cocok!');
                    return redirect()->back()->withInput($request->all());
                }

                $updatedCustomer->password = Hash::make($newPassword);
            }

            $updatedCustomer->email = $request->input('email');
            $updatedCustomer->save();

            Session::flash('success', 'Berhasil ubah data customer!');
            return redirect()->route('admin.app_customer.edit', ['id' => $id]);
        }
        catch (\Exception $ex){
            dd($ex);
            Log::channel('master_data')->error('Admin/AppCustomerController - update '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }

    public function indexPointHistory(Request $request){
        try{
            $filterStartDate = Carbon::today()->subDays(7)->format('d-m-Y');
            $filterEndDate = Carbon::today()->format('d-m-Y');

            if($request->start_date != null && $request->end_date != null){
                $startDateDecoded = rawurldecode($request->start_date);
                $endDateDecoded = rawurldecode($request->end_date);
                $start = Carbon::createFromFormat('!d-m-Y', $startDateDecoded, 'Asia/Jakarta');
                $end = Carbon::createFromFormat('!d-m-Y', $endDateDecoded, 'Asia/Jakarta');

                if($end->greaterThanOrEqualTo($start)){
                    $filterStartDate = $startDateDecoded;
                    $filterEndDate = $endDateDecoded;
                }
            }

            $filterUserId = -1;
            $filterUser = null;
            if($request->user_id != null){
                $filterUserId = intval($request->user_id);
                $filterUser = User::find($filterUserId);
            }

            $data = [
                'filterStartDate' => $filterStartDate,
                'filterEndDate' => $filterEndDate,
                'filterUserId' => $filterUserId,
                'filterUser' => $filterUser
            ];

            return view('admin.app_customer.index_history')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('online_system')->error('Admin/AppCustomerController - indexPointHistory '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndexPointHistory(Request $request){
        $histories = DB::table('user_point_histories');

        if($request->filled('user_id')){
            $filterUserId = $request->input('user_id');
            if($filterUserId > 0){
                $histories = $histories->where('user_id', '=', $filterUserId);
            }
        }

        if($request->filled('start_date') || $request->filled('end_date')){
            // Filter date
            $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $end->addDays(1);


            $histories = $histories->whereBetween('created_at', array($start->toDateTimeString(), $end->toDateTimeString()));
        }

        return DataTables::of($histories)
            ->setTransformer(new UserPointHistoryTransformer)
            ->make(true);
    }

    public function checkAppUserForCashier(Request $request){
        try{
            if(!$request->filled('phone')){
                return Response::json('Nomor Telp User Aplikasi wajib diisi!', 400);
            }

            $phone = trim($request->input('phone'));
            $appUser = User::where('phone', '=', $phone)->first();
            if(empty($appUser)){
                return Response::json('User Aplikasi tidak ditemukan!', 400);
            }

            $isBirthday = false;
            $tmpBirthdayDate = null;
            if(!empty($appUser->birthday_date) && $appUser->membership_id > 1){
                $today = Carbon::today('Asia/Jakarta');
                $birthdayDate = Carbon::parse($appUser->birthday_date)->addMinutes(10);
                $tmpBirthdayDate = Carbon::createFromDate($today->year, $birthdayDate->month, $birthdayDate->day);
                $startDate = $tmpBirthdayDate->copy()->startOfMonth()->startOfDay();
                $endDate = $tmpBirthdayDate->copy()->endOfMonth()->addDays(1)->startOfDay();

                $isBirthday = $today->between($startDate, $endDate);

                // Check for birthday promo usage
                if(DB::table('app_birthday_promo_usages')
                    ->where('user_id', '=', $appUser->id)
                    ->where('year', '=', $today->year)
                    ->exists()){
                    $isBirthday = false;
                }
            }

            $membership = DB::table('app_memberships')
                ->where('id', '=', $appUser->membership_id)
                ->first();

            $responseArr = [
                'is_birthday' => $isBirthday,
                'bithday_date' => !empty($tmpBirthdayDate) ? $tmpBirthdayDate->format('d-m-Y') : '',
                'membership' => $membership->name,
                'point' => number_format($appUser->point, 0, ',', '.')
            ];

            return Response::json($responseArr, 200);
        }
        catch (\Exception $ex){
            Log::channel('online_system')->error('Admin/AppCustomerController - checkAppUserForCashier '. $ex);
            return Response::json($ex, 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class MembershipController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        try {
            $admin = Auth::guard('admin')->user();

            $filterBranchId = $admin->branch_id;

            $memberships = DB::table('memberships');
            if($filterBranchId > 0){
                $memberships = $memberships->where('branch_id', '=', $filterBranchId);
            }

            $memberships = $memberships->get();

            $data = [
                'memberships' => $memberships,
            ];

            return view('admin.membership.index')->with($data);
        }
        catch (\Exception $ex){
            dd($ex);
            Log::channel('master_data')->error('Admin/MembershipController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function get(Request $request){
        try{
            //get membership
            $skip = 0;
            $take = 10;
            if($request->exists('skip')){
                $skip = $request->query('skip');
            }

            if($request->exists('take')){
                $take = $request->query('take');
            }

            $arr = [];
            //position id should be based on branch
            $user = Auth::guard('admin')->user();

            $userData = AdminUser::where('id', $user->id)->first();

            $memberships = Membership::where('id', '>', 0);
            if($request->exists('search') && $request->query('search') != ''){
                $memberships = $memberships->where('name', 'like', '%' . $request->query('search') . '%')
                    ->orWhere('code', 'like', '%' . $request->query('search') . '%');
            }
            $memberships = $memberships->skip($skip)->take($take)->get();
            $count = DB::table('memberships')->count();
            $pages = [];
            $tmpPageCount = ceil($count / 10);
            for($i=1; $i<=$tmpPageCount; $i++){
                $pages[] = $i;
            }

            foreach ($memberships as $membership){
                $arr[] = [
                    'id'        => $membership->id,
                    'code'      => $membership->code,
                    'price'     => $membership->price,
                    'price_str' => number_format($membership->price, '2',',','.')
                ];
            }

            $data = [
                'membership'    => $arr,
                'count'         => $count,
                'pages'         => $pages,
            ];
            return Response::json($data, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }
}

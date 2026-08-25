<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Transformer\CommissionPartnerAddonHistoryTransformer;
use App\Transformer\CommissionPartnerHistoryTransformer;
use App\Transformer\CommissionPartnerItemHistoryTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class CommissionPartnerHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request){
        try {
            $admin = Auth::guard('admin')->user();

            $filterBranchId = $admin->branch_id;

            $filterStartDate = Carbon::today()->subMonths(1)->format('d-m-Y');
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

            $filterPartnerId = -1;
            $filterPartner = null;
            if($filterBranchId > 0){
                // Filter partner
                if($request->partner_id != null){
                    $filterPartnerId = $request->partner_id;
                    $filterPartner = DB::table('partners')
                        ->where('id', '=', $filterPartnerId)
                        ->first();

                    if(empty($filterPartner)){
                        $filterPartnerId = -1;
                    }
                }
            }

            $filterPackageId = -1;
            $filterPackage = null;
            if($filterBranchId > 0){
                // Filter package
                if($request->package_id != null){
                    $filterPackageId = intval($request->package_id);
                    $filterPackage = DB::table('packages')
                        ->where('id', '=', $filterPackageId)
                        ->first();

                    if(empty($filterPackage)){
                        $filterPackageId = -1;
                    }
                }
            }

            $branches = DB::table('branches')
                ->orderBy('name')
                ->get();

            $data = [
                'isSuperAdmin' => false,
                'filterStartDate' => $filterStartDate,
                'filterEndDate' => $filterEndDate,
                'filterBranchId' => $filterBranchId,
                'filterPartnerId' => $filterPartnerId,
                'filterPartner' => $filterPartner,
                'filterPackageId' => $filterPackageId,
                'filterPackage' => $filterPackage,
                'branches' => $branches
            ];

            return view('admin.commission.index_partner_history')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/CommissionPartnerHistoryController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function indexItemCommission(Request $request){
        try {
            $admin = Auth::guard('admin')->user();

            $filterBranchId = $admin->branch_id;

            $filterStartDate = Carbon::today()->subMonths(1)->format('d-m-Y');
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

            $filterPartnerId = -1;
            $filterPartner = null;
            if($filterBranchId > 0){
                // Filter partner
                if($request->partner_id != null){
                    $filterPartnerId = $request->partner_id;
                    $filterPartner = DB::table('partners')
                        ->where('id', '=', $filterPartnerId)
                        ->first();

                    if(empty($filterPartner)){
                        $filterPartnerId = -1;
                    }
                }
            }

            $filterItemId = -1;
            $filterItem = null;
            if($filterBranchId > 0){
                // Filter item
                if($request->item_id != null){
                    $filterItemId = intval($request->item_id);
                    $filterItem = DB::table('items')
                        ->where('id', '=', $filterItemId)
                        ->first();

                    if(empty($filterItem)){
                        $filterItemId = -1;
                    }
                }
            }

            $branches = DB::table('branches')
                ->orderBy('name')
                ->get();

            $data = [
                'isSuperAdmin' => false,
                'filterStartDate' => $filterStartDate,
                'filterEndDate' => $filterEndDate,
                'filterBranchId' => $filterBranchId,
                'filterPartnerId' => $filterPartnerId,
                'filterPartner' => $filterPartner,
                'filterItemId' => $filterItemId,
                'filterItem' => $filterItem,
                'branches' => $branches
            ];

            return view('admin.commission.index_partner_addon_history_oura')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/CommissionPartnerHistoryController - indexItemCommission '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndex(Request $request){
        $histories = DB::table('commission_partner_histories');

        $admin = Auth::guard('admin')->user();

        // Filter branch id
        if(!$admin->can('branch.scope_all')){
            $filterBranchId = $admin->branch_id;
            $histories = $histories->where('branch_id', '=', $filterBranchId);
        }
        else{
            if($request->filled('filter_branch_id')){
                $filterBranchId = $request->input('filter_branch_id');
                if($filterBranchId > 0){
                    $histories = $histories->where('branch_id', '=', $filterBranchId);
                }
            }
        }

        // Filter date
        $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
        $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
        $end->addDays(1);

        $histories = $histories->whereBetween('transaction_date', array($start->toDateTimeString(), $end->toDateTimeString()));

        // Filter employee
        if($request->filled('filter_partner_id')){
            $filterPartnerId = $request->input('filter_partner_id'). '';
            if($filterPartnerId != -1){
                $histories = $histories->where('partner_id', '=', $filterPartnerId);
            }
        }

        // Filter item id
        if($request->filled('filter_package_id')){
            $filterPackageId = $request->input('filter_package_id');
            if($filterPackageId > 0){
                $histories = $histories->where('package_id', '=', $filterPackageId);
            }
        }

        return DataTables::of($histories)
            ->setTransformer(new CommissionPartnerHistoryTransformer)
            ->make(true);
    }

    public function getIndexItemCommission(Request $request){
        $histories = DB::table('commission_partner_histories')
            ->whereNotNull('item_id');

        $admin = Auth::guard('admin')->user();

        // Filter branch id
        if(!$admin->can('branch.scope_all')){
            $filterBranchId = $admin->branch_id;
            $histories = $histories->where('branch_id', '=', $filterBranchId);
        }
        else{
            if($request->filled('filter_branch_id')){
                $filterBranchId = $request->input('filter_branch_id');
                if($filterBranchId > 0){
                    $histories = $histories->where('branch_id', '=', $filterBranchId);
                }
            }
        }

        // Filter date
        $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
        $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
        $end->addDays(1);

        $histories = $histories->whereBetween('transaction_date', array($start->toDateTimeString(), $end->toDateTimeString()));

        // Filter employee
        if($request->filled('filter_partner_id')){
            $filterPartnerId = $request->input('filter_partner_id'). '';
            if($filterPartnerId != -1){
                $histories = $histories->where('partner_id', '=', $filterPartnerId);
            }
        }

        // Filter item id
        if($request->filled('filter_item_id')){
            $filterItemId = $request->input('filter_item_id');
            if($filterItemId > 0){
                $histories = $histories->where('item_id', '=', $filterItemId);
            }
        }

        return DataTables::of($histories)
            ->setTransformer(new CommissionPartnerItemHistoryTransformer)
            ->make(true);
    }
}

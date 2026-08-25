<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\Utilities;
use App\Models\CommissionPartnerLevelPackage;
use App\Models\PartnerLevel;
use App\Transformer\CommissionPartnerLevelTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class CommissionPartnerLevelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request){
        try {
            $admin = Auth::guard('admin')->user();

            $filterBranchId = $admin->branch_id;;
//            if(!$admin->is_super){
//                $filterBranchId = $admin->branch_id;
//            }
//            else{
//                // Filter branch
//                if($request->branch_id != null){
//                    $filterBranchId = intval($request->branch_id);
//                }
//            }

            $filterPositionId = -1;
            if($filterBranchId > 0){
                // Filter position
                if($request->position_id != null){
                    $filterPositionId = intval($request->position_id);
                }
            }

//            $filterPartnerId = -1;
//            $filterPartner = null;
//            if($filterBranchId > 0){
//                // Filter partner
//                if($request->partner_id != null){
//                    $filterPartnerId = intval($request->partner_id);
//                    $filterPartner = DB::table('partners')
//                        ->where('id', '=', $filterPartnerId)
//                        ->first();
//
//                    if(empty($filterPartner)){
//                        $filterPartnerId = -1;
//                    }
//                }
//            }

            $filterPartnerLevelId = -1;
            if($filterBranchId > 0){
                // Filter position
                if($request->partner_level_id != null){
                    $filterPartnerLevelId = intval($request->partner_level_id);
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

            $filterStatusId = -1;
            if($filterBranchId > 0){
                // Filter position
                if($request->status_id != null){
                    $filterStatusId = intval($request->status_id);
                }
            }

            $branches = DB::table('branches')
                ->orderBy('name')
                ->get();

            $partnerLevels = DB::table('partner_levels')
                ->where('branch_id', '=', $admin->branch_id)
                ->orderBy('name')
                ->get();

            // dd($partnerLevels);

            $data = [
//                'isSuperAdmin' => $admin->is_super,
                'isSuperAdmin' => false,
                'filterBranchId' => $filterBranchId,
                'filterPackageId' => $filterPackageId,
                'filterPackage' => $filterPackage,
                'filterPartnerLevelId' => $filterPartnerLevelId,
                'filterStatusId' => $filterStatusId,
                'branches' => $branches,
                'partnerLevels' => $partnerLevels
            ];

            return view('admin.commission.index_package_partner_level')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('setting')->error('Admin/CommissionPositionController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndex(Request $request){
        $commissions = DB::table('commission_partner_level_packages')
            ->join('packages', 'commission_partner_level_packages.package_id', '=', 'packages.id')
//            ->join('partner_levels', 'commission_partner_level_packages.partner_level_id', '=', 'partner_levels.id')
            ->select('commission_partner_level_packages.id as id',
                'packages.id as package_id',
                'packages.name as package_name',
                'packages.code as package_code',
//                'partner_levels.code as partner_level_code',
                'commission_partner_level_packages.partner_level as partner_level',
                'commission_partner_level_packages.commission_percentage as commission_percentage',
                'commission_partner_level_packages.commission_flat as commission_flat',
                'commission_partner_level_packages.extend_flat as extend_flat',
                'commission_partner_level_packages.status_id as status_id',
                'commission_partner_level_packages.updated_at as updated_at');

        $admin = Auth::guard('admin')->user();

        // Filter branch id
        if(!$admin->can('branch.scope_all')){
            $filterBranchId = $admin->branch_id;
            $commissions = $commissions->where('commission_partner_level_packages.branch_id', '=', $filterBranchId);
        }
        else{
            if($request->filled('filter_branch_id')){
                $filterBranchId = $request->input('filter_branch_id');
                if($filterBranchId > 0){
                    $commissions = $commissions->where('commission_partner_level_packages.branch_id', '=', $filterBranchId);
                }
            }
        }

        // Filter partner id
//        if($request->filled('filter_partner_level_id')){
//            $filterPartnerLevelId = $request->input('filter_partner_level_id');
//            if($filterPartnerLevelId > 0){
//                $commissions = $commissions->where('commission_partner_level_packages.partner_level_id', '=', $filterPartnerLevelId);
//            }
//        }

        // Filter partner level
        if($request->filled('filter_partner_level_id')){
            $filterPartnerLevelId = intval($request->input('filter_partner_level_id'));
            if($filterPartnerLevelId > 0){
                $partnerLevel = PartnerLevel::find($filterPartnerLevelId);
                $commissions = $commissions->where('commission_partner_level_packages.partner_level', '=', $partnerLevel->name);
            }
        }

        // Filter package id
        if($request->filled('filter_package_id')){
            $filterPackageId = $request->input('filter_package_id');
            if($filterPackageId > 0){
                $commissions = $commissions->where('commission_partner_level_packages.package_id', '=', $filterPackageId);
            }
        }

        return DataTables::of($commissions)
            ->setTransformer(new CommissionPartnerLevelTransformer())
            ->make(true);
    }

    public function create(Request $request){
        try {
            $admin = Auth::guard('admin')->user();

            $activeBranchId = $admin->branch_id;;
//            if(!$admin->is_super){
//                $activeBranchId = $admin->branch_id;
//            }
//            else{
//                // Filter branch
//                if($request->branch_id != null){
//                    $activeBranchId = intval($request->branch_id);
//                }
//            }

            $branches = DB::table('branches')
                ->orderBy('name')
                ->get();

            $partnerLevels = DB::table('partner_levels')
                ->where('branch_id', '=', $admin->branch_id)
                ->orderBy('name')
                ->get();

            $data = [
                'activeBranchId' => $activeBranchId,
//                'isSuperAdmin' => $admin->is_super,
                'isSuperAdmin' => false,
                'branches' => $branches,
                'partnerLevels' => $partnerLevels
            ];

            return view('admin.commission.create_package_partner_level')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('setting')->error('Admin/CommissionPartnerLevelController - create '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function store(Request $request){
        try {
            if(!$request->filled('partner_level') || !$request->filled('branch')){
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back()->withInput($request->all());
            }

            // Validate employee position
            $partnerLevelId = intval($request->input('partner_level'));
            if(!DB::table('partner_levels')
                ->where('id', '=', $partnerLevelId)
                ->exists()){
                Session::flash('error', 'Wajib pilih Level Mitra!');
                return redirect()->back()->withInput($request->all());
            }

            $admin = Auth::guard('admin')->user();
            $partnerLevel = DB::table('partner_levels')
                ->where('id', '=', $partnerLevelId)
                ->value('name');

//            if($partnerLevelId === 1){
//                $partnerLevel = 'silver';
//            }
//            else{
//                $partnerLevel = 'gold';
//            }

            // Validate branch
            $branchId = $request->input('branch');
            if($branchId < 1){
                Session::flash('error', 'Wajib pilih Cabang!');
                return redirect()->back()->withInput($request->all());
            }

            $commissions = $request->input('commissions');
            foreach ($commissions as $commission){
                $packageId = intval($commission['package_id']);
                if(!DB::table('commission_partner_level_packages')
                    ->where('partner_level', '=', $partnerLevel)
                    ->where('package_id', '=', $packageId)
                    ->exists()){
                    $newCommission = new CommissionPartnerLevelPackage();
                    $newCommission->branch_id = $branchId;
                    $newCommission->partner_level = $partnerLevel;
                    $newCommission->package_id = $packageId;
                    $newCommission->commission_percentage = Utilities::toFloat($commission['commission_percentage']);
                    $newCommission->commission_flat = Utilities::toFloat($commission['commission_flat']);
                    $newCommission->extend_flat = Utilities::toFloat($commission['commission_extend_flat']);
                    $newCommission->status_id = 1;
                    $newCommission->save();
                }
            }

            Session::flash('success', 'Berhasil buat pengaturan Komisi!');
            return redirect()->route('admin.commission.partner_level.package.index', ['branch_id' => $branchId]);
        }
        catch (\Exception $ex){
            Log::channel('setting')->error('Admin/CommissionPartnerLevelController - store '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }

    public function update(Request $request){
        try {
            if(!$request->filled('edited_id') ||
                !$request->filled('edited_status_id') ||
                !$request->filled('edited_commission_percentage') ||
                !$request->filled('edited_commission_flat')){
                return response()->json(collect([
                    'code' => 400,
                    'error' => 'BAD REQUEST'
                ]), 200);
            }

            $id = intval($request->input('edited_id'));
            $editedCommission = CommissionPartnerLevelPackage::find($id);
            if(empty($editedCommission)){
                return response()->json(collect([
                    'code' => 400,
                    'error' => 'BAD REQUEST'
                ]), 200);
            }

            $editedCommission->commission_percentage = Utilities::toFloat($request->input('edited_commission_percentage'));
            $editedCommission->commission_flat = Utilities::toFloat($request->input('edited_commission_flat'));
            $editedCommission->status_id = $request->input('edited_status_id');
            $editedCommission->save();

            return response()->json(collect([
                'code' => 200,
                'error' => 'none'
            ]), 200);
        }
        catch (\Exception $ex){
            Log::channel('setting')->error('Admin/CommissionPartnerLevelController - update '. $ex);
            return response()->json(collect([
                'code' => 500,
                'error' => $ex->getMessage()
            ]), 500);
        }
    }
}

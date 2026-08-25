<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\Utilities;
use App\Models\CommissionPositionItem;
use App\Transformer\CommissionPositionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class CommissionPositionController extends Controller
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

            $positions = null;
            if($filterBranchId > 0){
                $positions = DB::table('employee_positions')
                    ->where('branch_id', '=', $filterBranchId)
                    ->orderBy('name')
                    ->get();
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

            $data = [
//                'isSuperAdmin' => $admin->is_super,
                'isSuperAdmin' => false,
                'filterBranchId' => $filterBranchId,
                'filterPositionId' => $filterPositionId,
                'filterItemId' => $filterItemId,
                'filterItem' => $filterItem,
                'filterStatusId' => $filterStatusId,
                'branches' => $branches,
                'positions' => $positions
            ];

            return view('admin.commission.index_item_position')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('setting')->error('Admin/CommissionPositionController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndex(Request $request){
        $commissions = DB::table('commission_position_items')
            ->join('employee_positions', 'commission_position_items.position_id', '=', 'employee_positions.id')
            ->join('items', 'commission_position_items.item_id', '=', 'items.id')
            ->select('commission_position_items.id as id',
                'employee_positions.id as position_id',
                'employee_positions.name as position_name',
                'employee_positions.code as position_code',
                'items.code as item_code',
                'items.name as item_name',
                'commission_position_items.commission_percentage as commission_percentage',
                'commission_position_items.commission_flat as commission_flat',
                'commission_position_items.status_id as status_id',
                'commission_position_items.updated_at as updated_at');

        $admin = Auth::guard('admin')->user();

        // Filter branch id
        if(!$admin->can('branch.scope_all')){
            $filterBranchId = $admin->branch_id;
            $commissions = $commissions->where('commission_position_items.branch_id', '=', $filterBranchId);
        }
        else{
            if($request->filled('filter_branch_id')){
                $filterBranchId = $request->input('filter_branch_id');
                if($filterBranchId > 0){
                    $commissions = $commissions->where('commission_position_items.branch_id', '=', $filterBranchId);
                }
            }
        }

        // Filter status id
        if($request->filled('filter_status_id')){
            $filterStatusId = $request->input('filter_status_id');
            if($filterStatusId > 0){
                $commissions = $commissions->where('commission_position_items.status_id', '=', $filterStatusId);
            }
        }

        // Filter position id
        if($request->filled('filter_position_id')){
            $filterPositionId = $request->input('filter_position_id');
            if($filterPositionId > 0){
                $commissions = $commissions->where('commission_position_items.position_id', '=', $filterPositionId);
            }
        }

        // Filter item id
        if($request->filled('filter_item_id')){
            $filterItemId = $request->input('filter_item_id');
            if($filterItemId > 0){
                $commissions = $commissions->where('commission_position_items.item_id', '=', $filterItemId);
            }
        }

        return DataTables::of($commissions)
            ->setTransformer(new CommissionPositionTransformer())
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

            $positions = null;
            if($activeBranchId > 0){
                $positions = DB::table('employee_positions')
                    ->where('branch_id', '=', $activeBranchId)
                    ->orderBy('name')
                    ->get();
            }

            $data = [
                'activeBranchId' => $activeBranchId,
//                'isSuperAdmin' => $admin->is_super,
                'isSuperAdmin' => false,
                'branches' => $branches,
                'positions' => $positions
            ];

            return view('admin.commission.create_item_position')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('setting')->error('Admin/CommissionPositionController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function store(Request $request){
        try {
            if(!$request->filled('position') || !$request->filled('branch')){
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back()->withInput($request->all());
            }

            // Validate employee position
            $positionId = $request->input('position');
            if($positionId < 1){
                Session::flash('error', 'Wajib pilih Jabatan!');
                return redirect()->back()->withInput($request->all());
            }

            // Validate branch
            $branchId = $request->input('branch');
            if($branchId < 1){
                Session::flash('error', 'Wajib pilih Cabang!');
                return redirect()->back()->withInput($request->all());
            }

            $commissions = $request->input('commissions');
            foreach ($commissions as $commission){
                $itemId = intval($commission['item_id']);
                if(!DB::table('commission_position_items')
                    ->where('position_id', '=', $positionId)
                    ->where('item_id', '=', $itemId)
                    ->exists()){
                    $newCommission = new CommissionPositionItem();
                    $newCommission->branch_id = $branchId;
                    $newCommission->position_id = $positionId;
                    $newCommission->item_id = $itemId;
                    $newCommission->commission_percentage = Utilities::toFloat($commission['commission_percentage']);
                    $newCommission->commission_flat = Utilities::toFloat($commission['commission_flat']);
                    $newCommission->status_id = 1;
                    $newCommission->save();
                }
            }

            Session::flash('success', 'Berhasil buat pengaturan Komisi!');
            return redirect()->route('admin.commission.position.item.index', ['branch_id' => $branchId]);
        }
        catch (\Exception $ex){
            Log::channel('setting')->error('Admin/CommissionPositionController - store '. $ex);
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
            $editedCommission = CommissionPositionItem::find($id);
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
            Log::channel('setting')->error('Admin/CommissionPositionController - update '. $ex);
            return response()->json(collect([
                'code' => 500,
                'error' => $ex->getMessage()
            ]), 500);
        }
    }
}

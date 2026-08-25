<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\Utilities;
use App\Models\CommissionItemManualDetail;
use App\Models\CommissionItemManualHeader;
use App\Transformer\CommissionItemManualTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class CommissionItemManualController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request){
        try {
            $admin = Auth::guard('admin')->user();

            $filterDate = Carbon::today()->format('d-m-Y');

            if($request->query('filter_date') != null){
                $date = Carbon::createFromFormat('!d-m-Y', $request->query('filter_date'), 'Asia/Jakarta');
                $filterDate = $date->format('d-m-Y');
            }

            $data = [
                'filterDate'    => $filterDate,
            ];

            return view('admin.commission.index_item_manual')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/CommissionPositionController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndex(Request $request){
        try{
            $admin = Auth::guard('admin')->user();

            // Filter date
            if($request->filled('filter_date')){
                $date = Carbon::createFromFormat('!d-m-Y', $request->input('filter_date'), 'Asia/Jakarta');
            }
            else{
                $date = Carbon::today('Asia/Jakarta');
            }

            $startDate = $date->copy();
            $endDate = $date->copy()->addDays(1)->subHours(2);

            $commissions = DB::connection('hrd')
                ->table('commission_item_manual_headers')
                ->where('branch_id', '=', $admin->branch_id)
//                ->where('date', '=', $date->toDateTimeString())
                ->whereBetween('date', array($startDate->toDateTimeString(), $endDate->toDateTimeString()))
                ->get();

            return DataTables::of($commissions)
                ->setTransformer(new CommissionItemManualTransformer)
                ->addIndexColumn()
                ->make(true);
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/CommissionItemManualController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function create(Request $request){
        try{
            $admin = Auth::guard('admin')->user();

            $items = DB::table('items')
                ->where('branch_id', '=', $admin->branch_id)
                ->where(function($query){
                    $query->where('manual_commission_amount', '>', 0)
                        ->orWHere('manual_partner_commission_amount', '>', 0);
                })
                ->get();

            $itemCommissionListArr = [];
            foreach ($items as $item){
                $itemCommissionListArr[] = [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'qty' => 0,
                    // 'commission_amount' => 0
                ];
            }

            $today = Carbon::today('Asia/Jakarta');

            $data = [
                'itemCommissionListArr' => json_encode($itemCommissionListArr),
                'branchId' => $admin->branch_id,
                'today' => $today->format('d-m-Y')
            ];

            return view('admin.commission.create_item_manual')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/CommissionItemManualController - create '. $ex);
            return back()->withErrors('Sorry something went wrong!');
        }
    }

    public function store(Request $request){
        try{
            if($request->filled('employee') && $request->filled('partner')){
                return back()->withErrors('Hanya bisa pilih salah satu karyawan atau therapis!');
            }

            if(!$request->filled('employee') && !$request->filled('partner')){
                return back()->withErrors('Mohon pilih salah satu karyawan atau therapis!');
            }

            $itemCommissions = $request->input('item_commissions');

            $isInputted = false;
            foreach ($itemCommissions as $itemCommission){
                // if($itemCommission['qty'] > 0 && $itemCommission['commission_amount'] > 0){
                //     $isInputted = true;
                // }

                if($itemCommission['qty'] > 0){
                    $isInputted = true;
                }
            }

            if(!$isInputted){
                return back()->withErrors('Mohon input angka qty!');
            }

            $admin = Auth::guard('admin')->user();
            $branch = DB::table('branches')
                ->where('id','=', $admin->branch_id)
                ->first();

            $employeeId = null;
            $partnerId = null;

            $select = '';
            if($request->filled('employee')){
                $select = 'employee';
                $employeeId = $request->input('employee');
            }

            if($request->filled('partner')){
                $select = 'partner';
                $partnerId = $request->input('partner');
            }

            $date = Carbon::createFromFormat('!d-m-Y', $request->input('date'), 'Asia/Jakarta');

            $newHeader = new CommissionItemManualHeader();
            $newHeader->branch_id = $admin->branch_id;
            $newHeader->branch_code = $branch->code;
            $newHeader->branch_name = $branch->name;
            $newHeader->date = $date->toDateTimeString();

            if($select === 'employee'){
                $newHeader->employee_id = $employeeId;

                $employee = DB::table('employees')
                    ->where('id','=', $employeeId)
                    ->first();
                $newHeader->employee_code = $employee->code;
                $newHeader->employee_name = $employee->name;

                $department = DB::table('departments')
                    ->where('id','=', $employee->department_id)
                    ->first();
                $newHeader->employee_department = $department->name;
            }

            if($select === 'partner'){
                $newHeader->partner_id = $partnerId;

                $partner = DB::table('partners')
                    ->where('id','=', $partnerId)
                    ->first();
                $newHeader->partner_code = $partner->code;
                $newHeader->partner_name = $partner->name;
                $newHeader->partner_alias = $partner->alias;
                $newHeader->partner_number = $partner->number;
            }
            $newHeader->save();
            $savedId = $newHeader->id;

            $totalQty = 0;
            $totalCommissionAmount = 0;
            foreach ($itemCommissions as $itemCommission){
                $qty = Utilities::toFloat($itemCommission['qty']);
                // $commissionAmount = Utilities::toFloat($itemCommission['commission_amount']);
                if($qty > 0){
                    $item = DB::table('items')
                        ->where('id', '=', $itemCommission['item_id'])
                        ->first();

                    // if($select === 'employee' && $item->manual_commission_amount < 1){
                    //     continue;
                    // }

                    // if($select === 'partner' && $item->manual_partner_commission_amount < 1){
                    //     continue;
                    // }

                    $commissionAmount = $select === 'employee' ? $item->manual_commission_amount : $item->manual_partner_commission_amount;

                    $totalQty += $qty;
                    $totalCommissionAmount += $commissionAmount;

                    $newDetail = new CommissionItemManualDetail();
                    $newDetail->header_id = $savedId;
                    $newDetail->branch_id = $admin->branch_id;
                    $newDetail->branch_code = $branch->code;
                    $newDetail->branch_name = $branch->name;
                    $newDetail->item_id = $itemCommission['item_id'];


                    $newDetail->item_code = $item->code;
                    $newDetail->item_name = $item->name;
                    $newDetail->qty = $qty;
                    $newDetail->commission_amount = $commissionAmount;
                    $newDetail->save();
                }
            }

            DB::connection('hrd')
                ->table('commission_item_manual_headers')
                ->where('id','=', $savedId)
                ->update([
                    'total_qty' => $totalQty,
                    'total_commission_amount' => $totalCommissionAmount
                ]);

            Session::flash('success', 'Berhasil buat komisi manual baru!');
            return redirect()->route('admin.commission_manual.index');
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/CommissionPositionController - store '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function delete(Request $request){
        try {
            if(!$request->filled('deleted_id')){
                return response()->json(collect([
                    'code' => 400,
                    'error' => 'BAD REQUEST'
                ]), 200);
            }

            $deletedId = intval($request->input('deleted_id'));

            // Delete details
            DB::connection('hrd')
                ->table('commission_item_manual_details')
                ->where('header_id', '=', $deletedId)
                ->delete();

            // Delete header
            DB::connection('hrd')
                ->table('commission_item_manual_headers')
                ->where('id', '=', $deletedId)
                ->delete();

            return response()->json(collect([
                'code' => 200,
                'error' => 'none'
            ]), 200);
        }
        catch (\Exception $ex){
            Log::channel('setting')->error('Admin/CommissionPositionController - delete '. $ex);
            return response()->json(collect([
                'code' => 500,
                'error' => $ex->getMessage()
            ]), 500);
        }
    }
}

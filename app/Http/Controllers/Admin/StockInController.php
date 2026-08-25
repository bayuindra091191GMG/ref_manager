<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Models\StockIn;
use App\Models\StockInDetail;
use App\Models\StockInHeader;
use App\Models\Warehouse;
use App\Transformer\InventoryTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class StockInController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request){
        try {
            return view('admin.stock_in.index');
        }
        catch (\Exception $ex){
            Log::channel('inventory')->error('Admin/StockInController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndex(Request $request){
        $stockIns = DB::table('stock_ins')
            ->join('warehouses', 'stock_ins.warehouse_id', '=', 'warehouses.id')
            ->join('admin_users', 'stock_ins.created_by', '=', 'admin_users.id')
            ->select('stock_ins.code as code',
                'stock_ins.requested_by as requested_by',
                'stock_ins.purpose as purpose',
                'warehouses.name as warehouse_name',
                'admin_users.username as created_by');

        $admin = Auth::guard('admin')->user();
        $stockIns = $stockIns->where('stock_ins.branch_id', '=', $admin->branch_id);

        return DataTables::of($stockIns)
            ->setTransformer(new InventoryTransformer)
            ->make(true);
    }

    public function create(Request $request){
        try {
            return view('admin.stock_in.create');
        }
        catch (\Exception $ex){
            Log::channel('inventory')->error('Admin/StockInController - create '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'warehouse_origin_id' => 'required'
            ],[
                'warehouse_origin_id.required'          => 'Gudang Pengeluaran wajib dipilih!'
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $admin = Auth::guard('admin')->user();
            $newStockIn = new StockInHeader();
            $newStockIn->branch_id = $admin->branch_id;
            $newStockIn->code = AutoNumberGenerator::generateAutonumberTrx('IWT');
            $newStockIn->requested_by = $request->filled('requested_by') ? trim($request->input('requested_by')) : null;
            $newStockIn->description = $request->filled('description') ? trim($request->input('description')) : null;
            $newStockIn->purpose = $request->filled('purpose') ? trim($request->input('purpose')) : null;
            $newStockIn->warehouse_id = $request->input('warehouse_origin_id');
            $newStockIn->warehouse_name = Warehouse::find($request->input('warehouse_origin_id'))->name;
            $newStockIn->created_by = $admin->id;
            $newStockIn->save();

            $itemIds = $request->input('items');
            $qtys = $request->input('qtys');

            $ct = 0;
            foreach ($itemIds as $itemId){
                $itemModel = DB::table('items')
                    ->where('id', '=', $itemId)
                    ->first();

                $newDetail = new StockInDetail();
                $newDetail->header_id = $newStockIn->id;
                $newDetail->item_id = $itemId;
                $newDetail->item_code = $itemModel->code;
                $newDetail->item_name = $itemModel->name;
                $newDetail->qty = $qtys[$ct];
                $newDetail->save();

                $ct++;
            }

            Session::flash('success', 'Sukses membuat Stock In baru!');
            return redirect()->route('admin.stock_in.show');
        }
        catch (\Exception $ex){
            Log::channel('inventory')->error('Admin/StockInController - store '. $ex);
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }
}

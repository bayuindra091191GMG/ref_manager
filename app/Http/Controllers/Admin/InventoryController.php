<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\TransactionHeader;
use App\Transformer\InventoryHistoryTransformer;
use App\Transformer\InventoryTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Rap2hpoutre\FastExcel\FastExcel;
use Yajra\DataTables\DataTables;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request){
        try {
            $admin = Auth::guard('admin')->user();

            $filterWarehouseId = -1;
            // Filter warehouse
            if($request->warehouse_id != null){
                $filterWarehouseId = intval($request->warehouse_id);
            }

            $warehouses = DB::table('warehouses')
                ->where('branch_id', '=', $admin->branch_id)
                ->get();

            $data = [
                'filterWarehouseId' => $filterWarehouseId,
                'warehouses' => $warehouses
            ];

            return view('admin.inventory.index')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/InventoryController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndex(Request $request){
        $inventories = DB::table('inventories')
            ->join('items', 'inventories.item_id', '=', 'items.id')
            ->join('warehouses', 'inventories.warehouse_id', '=', 'warehouses.id')
            ->join('item_units', 'inventories.item_id', '=', 'item_units.item_id')
            ->join('unit_materials', 'item_units.unit_id', '=', 'unit_materials.id')
            ->select('items.code as item_code',
                'items.name as item_name',
                'inventories.warehouse_id',
                'warehouses.name as warehouse_name',
                'unit_materials.name as unit_name',
                'inventories.qty as qty');

        $admin = Auth::guard('admin')->user();
        $inventories = $inventories->where('inventories.branch_id', '=', $admin->branch_id);

        // Filter warehouse id
        if($request->filled('filter_warehouse_id')){
            $filterWarehouseId = $request->input('filter_warehouse_id');
            if($filterWarehouseId > 0){
                $inventories = $inventories->where('warehouse_id', '=', $filterWarehouseId);
            }
        }

        return DataTables::of($inventories)
            ->setTransformer(new InventoryTransformer)
            ->make(true);
    }

    public function indexHistory(Request $request){
        try {
            $admin = Auth::guard('admin')->user();

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

            $filterWarehouseId = -1;
            // Filter warehouse
            if($request->warehouse_id != null){
                $filterWarehouseId = intval($request->warehouse_id);
            }

            $warehouses = DB::table('warehouses')
                ->where('branch_id', '=', $admin->branch_id)
                ->get();

            $data = [
                'warehouses' => $warehouses,
                'filterStartDate' => $filterStartDate,
                'filterEndDate' => $filterEndDate,
                'filterWarehouseId' => $filterWarehouseId
            ];

            return view('admin.inventory.history')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/InventoryController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndexHistory(Request $request){
        $admin = Auth::guard('admin')->user();

        $histories = DB::table('inventory_histories');

        // Filter date
        $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
        $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
        $end->addDays(1);

        $histories = $histories->whereBetween('created_at', array($start->toDateTimeString(), $end->toDateTimeString()))
            ->where('branch_id', '=', $admin->branch_id);

        // Filter warehouse id
        if($request->filled('filter_warehouse_id')){
            $filterWarehouseId = $request->input('filter_warehouse_id');
            if($filterWarehouseId > 0){
                $histories = $histories->where('warehouse_id', '=', $filterWarehouseId);
            }
        }

        return DataTables::of($histories)
            ->setTransformer(new InventoryHistoryTransformer)
            ->make(true);
    }

    public function filterInventoryUsageReport(){
        $startDate = Carbon::today('Asia/Jakarta');
        $itemCategories = DB::table('item_categories')
            ->get();

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate' => $startDate->copy()->format('d-m-Y'),
            'itemCategories' => $itemCategories
        ];

        return view('admin.report.report_inventory_usage_filter')->with($data);
    }

    public function generateInventoryUsageReport(Request $request){
        try{
            $rules = array(
                'start_date'=> 'required',
                'end_date'=> 'required',
            );

            $message = array(
                'start_date.required' => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required' => 'Tanggal Akhir Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if($validator->fails()){
                return Response::json(['error' => $validator->errors()->all()]);
            }

            $startDateStr = $request->input('start_date');
            $endDateStr = $request->input('end_date');

            // Get range date
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDays(1);

            $admin = Auth::guard('admin')->user();

            $categoryId = intval($request->input('category'));
            if($categoryId > 0){
                $items = DB::select("select i.id, i.code, i.name, ic.name as category_name, sum(dt.qty) as total_qty, sum(dt.subtotal) as total_sales
                        from items i
                        inner join item_categories ic on i.category_id = ic.id
                        inner join transaction_details dt on dt.item_id = i.id
                        inner join transaction_headers th on dt.header_id = th.id
                        where i.branch_id = ". $admin->branch_id ."
                        and th.status_id != 16
                        and th.created_at >= '". $startDate->toDateTimeString() ."'
                        and th.created_at <= '". $endDate->toDateTimeString() ."'
                        and ic.id = ". $categoryId ."
                        group by i.id, i.code, i.name, ic.name;");
            }
            else{
                $items = DB::select("select i.id, i.code, i.name, ic.name as category_name, sum(dt.qty) as total_qty, sum(dt.subtotal) as total_sales
                        from items i
                        inner join item_categories ic on i.category_id = ic.id
                        inner join transaction_details dt on dt.item_id = i.id
                        inner join transaction_headers th on dt.header_id = th.id
                        where i.branch_id = ". $admin->branch_id ."
                        and th.status_id != 16
                        and th.created_at >= '". $startDate->toDateTimeString() ."'
                        and th.created_at <= '". $endDate->toDateTimeString() ."'
                        group by i.id, i.code, i.name, ic.name;");
            }

//            dd($items);

            $result = [];
            $no = 1;
            foreach ($items as $item){
                $nResult = [
                    'no' => $no,
                    'code' => $item->code,
                    'name' => $item->name,
                    'category_name' => $item->category_name,
                    'qty' => $item->total_qty,
                ];

                $result[] = $nResult;
                $no++;
            }

            $data = [
                'startDate' => $startDateStr,
                'endDate' => $endDateStr,
                'itemResults' => $result
            ];

            return view('admin.report.report_inventory_usage_content')->with($data);
        }
        catch (\Exception $ex){
            Session::flash('error', $ex);
            return back();
        }
    }

    public function filterInvFocReport(){
        $startDate = Carbon::now('Asia/Jakarta');
        $endDate = Carbon::now('Asia/Jakarta');
        $endDate->addDay();

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate'   => $endDate->format('d-m-Y'),
        ];

        return view('admin.report.report_recap_filter')->with($data);
    }

    public function generateInvFocReport(Request $request){
        try {
            $rules = array(
                'start_date' => 'required',
                'end_date' => 'required',
            );

            $message = array(
                'start_date.required' => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required' => 'Tanggal Akhir Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()->all()]);
            }

            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $headers = TransactionHeader::where('branch_id', $userData->branch_id)
                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->whereNotIn('locker_id', ['1', '2', '3', '5', '6', '100', '101', '102', '103', '104', '105', '106', '107', '108', '109', '110'])
                ->get();

            foreach ($headers as $header){

            }
        }
        catch (\Exception $ex){
            Session::flash('error', $ex);
            return back();
        }
    }

    public function exportToExcel(Request $request){
        try {
            $admin = Auth::guard('admin')->user();

            $inventories = DB::table('inventories')
                ->join('warehouses', 'inventories.warehouse_id', '=', 'warehouses.id')
                ->join('items', 'inventories.item_id', '=', 'items.id')
                ->select('inventories.id as id',
                    'warehouses.name as warehouse_name',
                    'items.code as item_code',
                    'items.name as item_name',
                    'inventories.qty as qty')
                ->where('inventories.branch_id', '=', $admin->branch_id)
                ->orderBy('warehouses.name')
                ->orderBy('items.name')
                ->get();

            $now = Carbon::now('Asia/Jakarta');
            $filename = 'INVENTORY_'.$now->year. $now->month. $now->day. $now->hour. $now->minute. '.xlsx';

            $export =  (new FastExcel($inventories))->download($filename, function ($inventory) {
                return [
                    'Gudang' => $inventory->warehouse_name,
                    'Nama Barang' => $inventory->item_name,
                    'Kode Barang' => $inventory->item_code,
                    'Stok' => $inventory->qty
                ];
            });

            $export->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
            $export->headers->set('Content-Disposition', 'attachment; ' .
                'filename="' . rawurldecode($filename) . '"; ' .
                'filename*=UTF-8\'\'' . rawurldecode($filename), true);
            $export->headers->set('Cache-Control', 'max-age=0', true);
            $export->headers->set('Pragma', 'public', true);

            return $export;
        }
        catch (\Exception $ex){
            dd($ex);
            Log::channel('master_data')->error('Admin/InventoryController - exportToExcel '. $ex);
            Session::flash('Terjadi kesalahan pada sistem!');
            return back();
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\Utilities;
use App\Models\Branch;
use App\Models\Configuration;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\ItemTransferDetail;
use App\Models\ItemTransferHeader;
use App\Models\ItemUnit;
use App\Models\UnitMaterial;
use App\Models\Warehouse;
use App\Transformer\ItemTransferTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ItemTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.item_transfer.index');
    }

    public function getIndex(Request $request){
        $admin = Auth::guard('admin')->user();
//        $itemTransfers = ItemTransferHeader::where('status_id', '!=', 2)
//            ->where('branch_id', '=', $admin->branch_id);

        $itemTransfers = DB::table('item_transfer_headers')
            ->join('statuses', 'item_transfer_headers.status_id', '=', 'statuses.id')
            ->select('item_transfer_headers.id as id',
                'item_transfer_headers.code as code',
                'item_transfer_headers.created_at as created_at',
                'item_transfer_headers.created_by as created_by',
                'item_transfer_headers.updated_at as updated_at',
                'item_transfer_headers.updated_by as updated_by',
                'item_transfer_headers.status_id as status_id',
                'statuses.display as status',
                'item_transfer_headers.warehouse_origin_name as warehouse_origin_name',
                'item_transfer_headers.warehouse_destination_name as warehouse_destination_name')
            ->where('item_transfer_headers.branch_id', '=', $admin->branch_id);

        return DataTables::of($itemTransfers)
            ->setTransformer(new ItemTransferTransformer)
            ->make(true);
    }

    public function create(){
        $branches = Branch::all();

        $data = [
            'branches'      => $branches,
        ];

        return view('admin.item_transfer.create')->with($data);
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'warehouse_origin_id'       => 'required',
                'warehouse_destination_id'  => 'required',
                'transfer_date'             => 'required',
            ],[
                'warehouse_origin_id.required'          => 'Nama Gudang Asal wajib dipilih!',
                'warehouse_destination_id.required'     => 'Nama Gudang Tujuan wajib dipilih!',
                'transfer_date.required'                => 'Tanggal Pengiriman wajib dipilih!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }
            if($request->input('warehouse_origin_id') == $request->input('warehouse_destination_id')){
                return back()->withErrors("Gudang Asal dan Gudang Tujuan Tidak boleh sama")->withInput($request->all());
            }

            $admin = Auth::guard('admin')->user();
            $originWarehouseId = $request->input('warehouse_origin_id');
            $destinationWarehouseId = $request->input('warehouse_destination_id');
            $itemIds = $request->input('transfer_product');
            $unitIds = $request->input('unitIds');
            $itemQty = $request->input('transfer_qty');

            // Validate stock
            $ct = 0;
            $errors = [];
            foreach ($itemIds as $itemId){
                $originInv = DB::table('inventories')
                    ->where('branch_id', '=', $admin->branch_id)
                    ->where('warehouse_id', '=', $originWarehouseId)
                    ->where('item_id', '=', $itemId)
                    ->first();
                if(empty($originInv)){
                    $warehouseName = Warehouse::find($originWarehouseId)->name;
                    $itemName = Item::find($itemId)->name;
                    $errors[] = "Stok (". $itemId .")". $itemName ." di ". $warehouseName ." tidak ditemukan!";
//                    return back()->withErrors("Stok di Gudang Asal tidak mencukupi!")->withInput($request->all());
                }
                else{
                    $tmpQty = intval($itemQty[$ct]);
                    if($originInv->qty < $tmpQty){
                        $warehouseName = Warehouse::find($originWarehouseId)->name;
                        $itemName = Item::find($itemId)->name;
                        $errors[] = "Stok (". $itemId .")". $itemName ." di ". $warehouseName ." = ".$originInv->qty.", request = ".$tmpQty;
//                    return back()->withErrors("Stok di Gudang Asal tidak mencukupi!")->withInput($request->all());
                    }
                }

                $ct++;
            }
            if(count($errors) > 0){
                return back()->withErrors($errors)->withInput($request->all());
            }


            $data = new ItemTransferHeader();
            $data->branch_id = $admin->branch_id;
            $data->code = AutoNumberGenerator::generateAutonumberTrx('IWT');
            $data->transfer_date = Carbon::parse($request->input('transfer_date'))->toDateTimeString();
            $data->description = $request->input('description');
            $data->warehouse_origin_id = $originWarehouseId;
            $data->warehouse_origin_name = Warehouse::find($originWarehouseId)->name;
            $data->warehouse_destination_id = $destinationWarehouseId;
            $data->warehouse_destination_name = Warehouse::find($destinationWarehouseId)->name;
            $data->total_qty = Utilities::toFloat($request->input('total_qty'));
            $data->status_id = 1;
            $data->created_by = $admin->id;
            $data->save();



            $ct = 0;
            foreach ($itemIds as $itemId){
                $dataDetail = new ItemTransferDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->item_id = $itemId;
                $dataDetail->unit_id = $unitIds[$ct];
                $dataDetail->qty_transfer = $itemQty[$ct];
                $dataDetail->cost_price = 0;
                $dataDetail->sell_price = 0;
                $dataDetail->total_cost = 0;
                $dataDetail->total_sell = 0;
                $dataDetail->created_by = $admin->id;
                $dataDetail->save();

                // // Stock out from origin warehouse
                // Utilities::updateStock($admin->branch_id,
                //     'sub',
                //     $itemQty[$ct],
                //     $itemId,
                //     $data->warehouse_origin_id,
                //     $data->code,
                //     'Stock Transfer Out');

                // // Stock in destination warehouse
                // Utilities::updateStock($admin->branch_id,
                //     'add',
                //     $itemQty[$ct],
                //     $itemId,
                //     $data->warehouse_destination_id,
                //     $data->code,
                //     'Stock Transfer In');

                $ct++;
            }

            Session::flash('success', 'Sukses membuat data transfer barang baru!');
            return redirect()->route('admin.item_transfer.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function edit($id){
        $branches = Branch::all();
        $dataDB = ItemTransferHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];

        foreach ($dataDB->item_transfer_details as $dataDetail){
            $stock = Inventory::where('branch_id', Auth::user()->branch_id)
                ->where('item_id', $dataDetail->item_id)
                ->where('warehouse_id', $dataDB->warehouse_origin_id)
                ->first();
            $unit = UnitMaterial::where('id', $dataDetail->unit_id)->first();
            $vueData = [
                "id"        => $dataDetail->id,
                "itemId"    => $dataDetail->item_id,
                "name"    => $dataDetail->item->code ." - ". $dataDetail->item->name,
                'unitId' => $dataDetail->unit_id,
                'unitName' => isset($unit) ? $unit->name : "",
                'url' => route('admin.item.unit.select') . '?id=',
                "qty"       => $dataDetail->qty_transfer,
                "stock"       => isset($stock) ? $stock->qty : 0,
                "location"       => $dataDetail->item->wh_location,
                "description"       => $dataDetail->description,
            ];
            array_push($detailData, $vueData);
        }

        $vueData = [
            'itemList' => $detailData
        ];

        $data = [
            'branches'  => $branches,
            'data'      => $dataDB,
            'vueData'      => $vueData,
        ];

        return view('admin.item_transfer.edit')->with($data);
    }

    public function update(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'warehouse_origin_id'       => 'required',
                'warehouse_destination_id'  => 'required',
                'transfer_date'             => 'required',
            ],[
                'warehouse_origin_id.required'          => 'Nama Gudang Asal wajib dipilih!',
                'warehouse_destination_id.required'     => 'Nama Gudang Tujuan wajib dipilih!',
                'transfer_date.required'                => 'Tanggal Pengiriman wajib dipilih!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }
            if($request->input('warehouse_origin_id') == $request->input('warehouse_destination_id')){
                return back()->withErrors("Gudang Asal dan Gudang Tujuan Tidak boleh sama")->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $data = ItemTransferHeader::where('id', $request->input('id'))->first();
            if($data == null){
                Session::flash('error', 'Data Transfer Barang tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $data->branch_id = $user->branch_id;
            $data->transfer_date = Carbon::parse($request->input('transfer_date'))->toDateTimeString();
            $data->description = $request->input('description');
            $data->warehouse_origin_id = $request->input('warehouse_origin_id');
            $data->warehouse_origin_name = Warehouse::find($request->input('warehouse_origin_id'))->name;
            $data->warehouse_destination_id = $request->input('warehouse_destination_id');
            $data->warehouse_destination_name = Warehouse::find($request->input('warehouse_destination_id'))->name;
            $data->total_qty = Utilities::toFloat($request->input('total_qty'));
            $data->updated_by = $user->id;
            $data->save();

            $itemIds = $request->input('transfer_product');
            $itemQty = $request->input('transfer_qty');
            $unitIds = $request->input('unitIds');

            $ct = 0;
            $oldData = ItemTransferDetail::where('header_id', $request->input('id'))->get();
            if(count($oldData) > 0){
                foreach($oldData as $oldDetaildata){
                    $oldDetaildata->delete();
                }
            }
            foreach ($itemIds as $itemId){
                $dataDetail = new ItemTransferDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->item_id = $itemId;
                $dataDetail->unit_id = $unitIds[$ct];
                $dataDetail->qty_transfer = $itemQty[$ct];
                $dataDetail->cost_price = 0;
                $dataDetail->sell_price = 0;
                $dataDetail->total_cost = 0;
                $dataDetail->total_sell = 0;
                $dataDetail->created_by = $user->id;
                $dataDetail->save();

                $ct++;
            }

            Session::flash('success', 'Sukses mengubah data transfer barang!');
            return redirect()->route('admin.item_transfer.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function show($id){
        $branches = Branch::all();
        $dataDB = ItemTransferHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];

        foreach ($dataDB->item_transfer_details as $dataDetail){
            $stock = Inventory::where('branch_id', Auth::user()->branch_id)
                ->where('item_id', $dataDetail->item_id)
                ->where('warehouse_id', $dataDB->warehouse_origin_id)
                ->first();
            $unit = UnitMaterial::where('id', $dataDetail->unit_id)->first();
            $vueData = [
                "id"        => $dataDetail->id,
                "itemId"    => $dataDetail->item_id,
                "name"    => $dataDetail->item->code ." - ". $dataDetail->item->name,
                'unitId' => $dataDetail->unit_id,
                'unitName' => isset($unit) ? $unit->name : "",
                'url' => route('admin.item.unit.select') . '?id=',
                "qty"       => $dataDetail->qty_transfer,
                "stock"       => isset($stock) ? $stock->qty : 0,
                "location"       => $dataDetail->item->wh_location,
                "description"       => $dataDetail->description,
            ];
            array_push($detailData, $vueData);
        }

        $vueData = [
            'itemList' => $detailData
        ];

        $data = [
            'branches'  => $branches,
            'data'      => $dataDB,
            'vueData'   => $vueData,
        ];

        return view('admin.item_transfer.show')->with($data);
    }

    public function delete(Request $request){
        try{
            $warehouse = Warehouse::where('id', $request->input('id'))->first();
            if($warehouse == null){
                Session::flash('error', 'Gudang tidak ditemukan!');
                return back();
            }
            $warehouse->status_id = 2;
            $warehouse->save();

            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json('error', 200);
        }
    }

    public function approve(Request $request){
        try{

            //check password first
            $config = Configuration::where('key', 'spv_password')->first();
            if(!Hash::check($request->input('password'), $config->value)){
                Session::flash('error', 'Password yang diinput Salah');
                return redirect()->route('admin.item_transfer.index');
            }

            $admin = Auth::guard('admin')->user();
            $ids = $request->input('ids');
//            dd($request, $ids);

            if(empty($ids)){
                Session::flash('error', 'Tidak Ada Data yang Dipilih');
                return redirect()->route('admin.item_transfer.index');
            }

            $successString = "";
            $failedString = "";
            foreach ($ids as $id) {
                $dataDB = ItemTransferHeader::on(session()->get('connection_name'))->where('id', $id)->first();
                if($dataDB == null){
                    Session::flash('error', 'Data tidak ditemukan!');
                    return back();
                }

                foreach ($dataDB->item_transfer_details as $itemDetail) {
                    $stock = Inventory::where('branch_id', Auth::user()->branch_id)
                        ->where('item_id', $itemDetail->item_id)
                        ->where('warehouse_id', $dataDB->warehouse_origin_id)
                        ->first();
                    if($stock == null){
                        $failedString .= $itemDetail->item->code ." - ". $itemDetail->item->name.": qty stock 0 kurang dari qty transfer ". $itemDetail->qty_transfer.', ';
                    }
                    else{
                        if($stock->qty < $itemDetail->qty_transfer){
                            $failedString .= $itemDetail->item->code ." - ". $itemDetail->item->name.": qty stock ". $stock->qty ." kurang dari qty transfer ". $itemDetail->qty_transfer.', ';
                        }
                    }
                }
                if (!empty($failedString)) {
                    Session::flash('error', 'Gagal approve data ' . $failedString);
                    return redirect()->route('admin.item_transfer.index');
                }


                try{
                    DB::beginTransaction();
                    foreach ($dataDB->item_transfer_details as $itemDetail){
                        $unit = ItemUnit::where('item_id', $itemDetail->item_id)
                            ->where('unit_id', $itemDetail->unit_id)
                            ->first();
                            // dd($unit->qty);
                        // Stock in destination warehouse
                        Utilities::updateStock($admin->branch_id,
                            'add',
                            // $itemDetail->qty_transfer,
                            $itemDetail->qty_transfer * $unit->qty,
                            $itemDetail->item_id,
                            $dataDB->warehouse_destination_id,
                            $dataDB->id,
                            $dataDB->code,
                            $dataDB->description.' ; '.$dataDB->code,
                            $dataDB->transfer_date);

                        sleep(4);

                        // Stock out from origin warehouse
                        Utilities::updateStock($admin->branch_id,
                            'sub',
                            // $itemDetail->qty_transfer,
                            $itemDetail->qty_transfer * $unit->qty,
                            $itemDetail->item_id,
                            $dataDB->warehouse_origin_id,
                            $dataDB->id,
                            $dataDB->code,
                            $dataDB->description.' ; '.$dataDB->code,
                            $dataDB->transfer_date);
                    }
                    $dataDB->status_id = 6;
                    $dataDB->updated_by = $admin->id;
                    $dataDB->save();

                    $successString .= $dataDB->code . " | ";

                } catch (\Exception $ex) {
                    DB::rollback();
                    $failedString .= "[" . $dataDB->code . "] - " . $ex->getMessage() . " ";
                }
            }
            if (empty($successString)) {
                DB::rollback();
                Session::flash('error', 'Gagal approve data ' . $failedString);
            } else {
                DB::commit();
                Session::flash('success', 'Sukses approve data ' . $successString);
            }
            return redirect()->route('admin.item_transfer.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back();
        }
    }

    public function getStock(Request $request){
        $inventory = Inventory::where('branch_id', Auth::user()->branch_id)
            ->where('item_id', $request->get('item_id'))
            ->where('warehouse_id', $request->get('warehouse_id'))
            ->first();

        return \response([
            'stock' => isset($inventory) ? $inventory->qty : 0,
        ]);
    }
}

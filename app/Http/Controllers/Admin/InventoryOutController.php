<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\Utilities;
use App\Models\Branch;
use App\Models\Coa;
use App\Models\InternalRequestHeader;
use App\Models\Inventory;
use App\Models\InventoryOutDetail;
use App\Models\InventoryOutHeader;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\JournalDetail;
use App\Models\JournalHeader;
use App\Models\JournalMap;
use App\Models\Warehouse;
use App\Transformer\InventoryOutTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class InventoryOutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $filterStartDate = Carbon::today()->startOfYear()->format('d-m-Y');
        $filterEndDate = Carbon::today()->format('d-m-Y');

        if ($request->start_date != null && $request->end_date != null) {
            $startDateDecoded = rawurldecode($request->start_date);
            $endDateDecoded = rawurldecode($request->end_date);
            $start = Carbon::createFromFormat('!d-m-Y', $startDateDecoded, 'Asia/Jakarta');
            $end = Carbon::createFromFormat('!d-m-Y', $endDateDecoded, 'Asia/Jakarta');

            if ($end->greaterThanOrEqualTo($start)) {
                $filterStartDate = $startDateDecoded;
                $filterEndDate = $endDateDecoded;
            }
        }

        $data = [
            'filterStartDate' => $filterStartDate,
            'filterEndDate' => $filterEndDate,
        ];

        return view('admin.inventory_out.index')->with($data);
    }

    public function getIndex(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        // Filter date
        $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
        $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
        $end->addDays(1)->addMinutes(-1);

        $datas = InventoryOutHeader::with(['status'])
            ->where('branch_id', $admin->branch_id)
            // ->where('status_id', '!=', 2)
            ->whereBetween('date', array($start->toDateTimeString(), $end->toDateTimeString()));

        return DataTables::of($datas)
            ->setTransformer(new InventoryOutTransformer)
            ->make(true);
    }

    public function create(Request $request){
        $admin = Auth::guard('admin')->user();

        $irId = null;
        $isInternal = 0;
        $internalRequest = null;
        if($request->exists('reference_id')){
            $irId = intval($request->input('reference_id'));
            $internalRequest = InternalRequestHeader::with(['warehouse', 'internal_request_details'])
                ->where('branch_id', '=', $admin->branch_id)
                ->where('id', '=', $irId)
                ->first();
            $isInternal = 1;
        }

        $itemList = [];
        if(!empty($internalRequest)){
            foreach ($internalRequest->internal_request_details as $detail){
                $inventory = Inventory::where('item_id', $detail->item_id)
                    ->where('warehouse_id', $internalRequest->warehouse_id)
                    ->first();
                $itemList[] = [
                    'id' => $this->generateRandomString(10),
                    'itemId' => $detail->item_id,
                    'stock' => $inventory != null ? $inventory->qty : 0,
                    'qty' => $detail->qty
                ];
            }
        }

        $vueData = [
            'itemList' => $itemList
        ];

        $data = [
            'vueData' => $vueData,
            'internalRequest' => $internalRequest,
            'isInternal' => $isInternal,
        ];

        return view('admin.inventory_out.create')->with($data);
    }

    public function getStock(Request $request){
        $inventory = Inventory::where('item_id', $request->get('item'))
            ->where('warehouse_id', $request->get('warehouse'))
            ->first();

        $stock = $inventory ? $inventory->qty : 0;

//        if($request->get('header')){
//            $detail = InventoryOutDetail::where('header_id', $request->get('header'))
//                ->whereHas('inventory_out_header', function ($query) use ($request){
//                    return $query->where('warehouse_id', $request->get('warehouse'));
//                })->first();
//
//            if($detail){
//                $stock += $detail->qty;
//            }
//        }

        return \response([
           'stock' => $stock,
        ]);
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'warehouse_id'       => 'required',
                'date'             => 'required',
            ],[
                'warehouse_id.required'          => 'Nama Gudang wajib dipilih!',
                'date.required'                => 'Tanggal Pengiriman wajib dipilih!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $admin = Auth::guard('admin')->user();
            $warehouseId = $request->input('warehouse_id');
            $itemIds = $request->input('transfer_product');
            $itemQty = $request->input('transfer_qty');
            $errors = [];

            // Validate from IR
            $internalRequestId = null;
            if($request->filled('reference_id')){
                $internalRequestId = $request->input('reference_id');
                $irDetailCount = DB::table('internal_request_details')
                    ->where('header_id', '=', $internalRequestId)
                    ->count();

                if(count($itemIds) != $irDetailCount){
                    $errors[] = 'Jumlah jenis barang harus sama dengan Internal Request yang berkaitan!';
                }
            }

            // Validate stock
            $ct = 0;
            foreach ($itemIds as $itemId){
                $originInv = Inventory::where('branch_id', $admin->branch_id)
                    ->where('warehouse_id', intval($warehouseId))
                    ->where('item_id', intval($itemId))
                    ->first();

                if(empty($originInv)){
                    $warehouseName = Warehouse::find($warehouseId)->name;
                    $itemName = Item::find($itemId)->name;
                    $errors[] = "Stok (". $itemId .")". $itemName ." di ". $warehouseName ." tidak ditemukan!";
                }
                else{
                    $tmpQty = intval($itemQty[$ct]);
                    if($originInv->qty < $tmpQty){
                        $warehouseName = Warehouse::find($warehouseId)->name;
                        $itemName = Item::find($itemId)->name;
                        $errors[] = "Stok (". $itemId .")". $itemName ." di ". $warehouseName ." = ".$originInv->qty.", request = ".$tmpQty;
                    }
                }

                $ct++;
            }
            if(count($errors) > 0){
                return back()->withErrors($errors)->withInput($request->all());
            }

            $data = new InventoryOutHeader();
            $data->branch_id = $admin->branch_id;
            $data->code = AutoNumberGenerator::generateAutonumberTrx('INV/OUT');
            $data->date = Carbon::parse($request->input('date'))->toDateTimeString();
            $data->description = $request->input('description');
            $data->warehouse_id = $warehouseId;
            $data->warehouse_name = Warehouse::find($warehouseId)->name;
            $data->internal_request_id = $request->filled('reference_id') ? $request->input('reference_id') : null;
            $data->total_qty = Utilities::toFloat($request->input('total_qty'));
            $data->status_id = 1;
            $data->created_by = $admin->id;
            $data->save();

            $ct = 0;
            foreach ($itemIds as $itemId){
                $itemUnit = ItemUnit::where('item_id', $itemId)->first();
                $dataDetail = new InventoryOutDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->item_id = $itemId;
                $dataDetail->qty = Utilities::toFloat($itemQty[$ct]);
                $dataDetail->unit_id = $itemUnit != null ? $itemUnit->unit_id : null;
                $dataDetail->qty_unit = $itemUnit != null ? $itemUnit->qty : 1;
                $dataDetail->created_by = $admin->id;
                $dataDetail->save();

                Log::channel('info')->info('admin_id: '. $admin->id.
                    ' code: '. $data->code.
                    ' item_id: '. $itemId.
                    ' IO DETAIL CREATED');

                // Stock exit out of warehouse
//                Utilities::updateStock($admin->branch_id,
//                    'sub',
//                    Utilities::toFloat($itemQty[$ct]),
//                    $itemId,
//                    $data->warehouse_id,
//                    $data->code,
//                    'Inventory Out');

                $ct++;
            }

            Session::flash('success', 'Sukses membuat data Inventory Out baru!');
            return redirect()->route('admin.inventory_out.index');
        }
        catch (\Exception $ex){
            Log::channel('error')->error('InventoryOurController store - error EX: '. $ex);
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function edit($id){
        $branches = Branch::all();
        $dataDB = InventoryOutHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];

        foreach ($dataDB->inventory_out_details as $dataDetail){
            $inventory = Inventory::where('item_id', $dataDetail->item_id)
                ->where('warehouse_id', $dataDB->warehouse_id)
                ->first();

            $vueData = [
                'id' => $this->generateRandomString(10),
                "itemId"    => $dataDetail->item_id,
                "unitId"    => $dataDetail->unit_id,
                'stock'     => $inventory ? $inventory->qty : 0,
                "qty"       => $dataDetail->qty,
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

        return view('admin.inventory_out.edit')->with($data);
    }

    public function update(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'warehouse_id'       => 'required',
                'date'             => 'required',
            ],[
                'warehouse_id.required'          => 'Nama Gudang wajib dipilih!',
                'date.required'                => 'Tanggal Pengiriman wajib dipilih!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $admin = Auth::guard('admin')->user();
            $warehouseId = $request->input('warehouse_id');
            $itemIds = $request->input('transfer_product');
            $itemQty = $request->input('transfer_qty');

            // Validate stock
            $ct = 0;
            $errors = [];
            foreach ($itemIds as $itemId){
                $originInv = Inventory::where('branch_id', $admin->branch_id)
                    ->where('warehouse_id', intval($warehouseId))
                    ->where('item_id', intval($itemId))
                    ->first();
//                dd($originInv,$admin->branch_id,intval($warehouseId), intval($itemId));
                if(empty($originInv)){
                    $warehouseName = Warehouse::find($warehouseId)->name;
                    $itemName = Item::find($itemId)->name;
                    $errors[] = "Stok (". $itemId .")". $itemName ." di ". $warehouseName ." tidak ditemukan!";
                }
                else{
                    $tmpQty = intval($itemQty[$ct]);
                    if($originInv->qty < $tmpQty){
                        $warehouseName = Warehouse::find($warehouseId)->name;
                        $itemName = Item::find($itemId)->name;
                        $errors[] = "Stok (". $itemId .")". $itemName ." di ". $warehouseName ." = ".$originInv->qty.", request = ".$tmpQty;
                    }
                }

                $ct++;
            }
            if(count($errors) > 0){
                return back()->withErrors($errors)->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $data = InventoryOutHeader::where('id', $request->input('id'))->first();
            if($data == null){
                Session::flash('error', 'Data Inventory Out tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $data->branch_id = $user->branch_id;
            $data->date = Carbon::parse($request->input('date'))->toDateTimeString();
            $data->description = $request->input('description');
            $data->warehouse_id = $request->input('warehouse_id');
            $data->warehouse_name = Warehouse::find($request->input('warehouse_id'))->name;
            $data->total_qty = Utilities::toFloat($request->input('total_qty'));
            $data->updated_by = $user->id;
            $data->save();

            $ct = 0;
            $oldData = InventoryOutDetail::where('header_id', $request->input('id'))->get();
            if(count($oldData) > 0){
                foreach($oldData as $oldDetaildata){
                    $oldDetaildata->delete();
                }
            }
            foreach ($itemIds as $itemId){
                $itemUnit = ItemUnit::where('item_id', $itemId)->first();

                $dataDetail = new InventoryOutDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->item_id = $itemId;
                $dataDetail->qty = Utilities::toFloat($itemQty[$ct]);
                $dataDetail->unit_id = $itemUnit != null ? $itemUnit->unit_id : null;
                $dataDetail->qty_unit = $itemUnit != null ? $itemUnit->qty : 1;
                $dataDetail->created_by = $user->id;
                $dataDetail->save();

                Log::channel('info')->info('admin_id: '. $admin->id.
                    ' code: '. $data->code.
                    ' item_id: '. $itemId.
                    ' IO DETAIL UPDATED');

                $ct++;
            }

            Session::flash('success', 'Sukses mengubah data Inventory Out!');
            return redirect()->route('admin.inventory_out.index');
        }
        catch (\Exception $ex){
            Log::channel('error')->error('InventoryOurController update - error EX: '. $ex);
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function show($id){
        $branches = Branch::all();
        $dataDB = InventoryOutHeader::where('id', $id)->first();
        if(empty($dataDB)){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];

        foreach ($dataDB->inventory_out_details as $dataDetail){
            $vueData = [
                "itemId"    => $dataDetail->item_id,
                "unitId"    => $dataDetail->unit_id,
                "qty"       => $dataDetail->qty,
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

        return view('admin.inventory_out.show')->with($data);
    }

    public function delete(Request $request){
        try{
            $warehouse = InventoryOutHeader::where('id', $request->input('id'))->first();
            if($warehouse == null){
                Session::flash('error', 'Data tidak ditemukan!');
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
            $userData = Auth::guard('admin')->user();
            $ids = $request->input('ids');
            if(empty($ids)){
                Session::flash('error', 'Tidak Ada Data yang Dipilih');
                return redirect()->route('admin.inventory_out.index');
            }
            foreach ($ids as $id) {
                $dataDB = InventoryOutHeader::where('id', $id)->first();
                if($dataDB == null){
                    Session::flash('error', 'Data tidak ditemukan!');
                    return back();
                }
                $dataDB->status_id = 13;
                $dataDB->updated_by = $userData->id;
                $dataDB->save();

                if(!empty($dataDB->internal_request_id)){
                    $irData = InternalRequestHeader::where('id', $dataDB->internal_request_id)->first();
                    $irData->is_pr = 1;
                    $irData->save();
                }


                // Journal Header Process START
                $journal = new JournalHeader();
                $journal->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                $journal->transaction_type = 'INVENTORY_OUT';
                $journal->transaction_date = $dataDB->date;
                $journal->notes = "Jurnal Inventory Out ".$dataDB->code;
                $journal->created_by = $userData->id;
                $journal->branch_id = $userData->branch_id;
                $journal->reference_id = $dataDB->id;
                $journal->reference_code = $dataDB->code;
                $journal->status_id = 1;
                $journal->save();
                // Journal Header Process END

                foreach ($dataDB->inventory_out_details as $detail){
                    Utilities::updateStock($userData->branch_id,
                        'sub',
                        $detail->qty,
                        $detail->item_id,
                        $dataDB->warehouse_id,
                        $dataDB->code,
                        'Inventory Out');

                    // Journal Detail Process Item START
                    $itemsDB = Item::where('id', $detail->item_id)->first();

                    if(!empty($itemsDB->category_2_id)){
                        $journalMaps = JournalMap::where('branch_id', $userData->branch_id)
                            ->where('transaction_type', 'INVENTORY_OUT')
                            ->where('category_2_id', $itemsDB->category_2_id)
                            ->get();

                        if(count($journalMaps) > 0){
                            foreach ($journalMaps as $journalMap){
                                $coa = Coa::where('id', $journalMap->coa_id)->first();

                                $jDt = new JournalDetail();
                                $jDt->branch_id = $userData->branch_id;
                                $jDt->header_id = $journal->id;
                                $jDt->account_code = $coa->code;
                                $jDt->account_type_id = $coa->type_id;
                                $jDt->account_name = $coa->name;
                                $jDt->account_id = $coa->id;
                                $jDt->account_type_name = $coa->type;
                                $jDt->notes = 'Inventory Out ' . $dataDB->code. ' item ('.$itemsDB->code.')'.$itemsDB->name;
                                $jDt->reference_code = $dataDB->code;
                                $jDt->transaction_code = $journal->transaction_code;
                                $jDt->transaction_type = 'INVENTORY_OUT';

                                $totalPrice = $itemsDB->cost_price * $detail->qty;
                                if($journalMap->journal_type == "DEBET"){
                                    $jDt->debit = $totalPrice;
                                    $jDt->credit = 0;
                                    $jDt->original_amount = $totalPrice;
                                }
                                else{
                                    $jDt->debit = 0;
                                    $jDt->credit = $totalPrice;
                                    $jDt->original_amount = $totalPrice;
                                }
                                $jDt->save();
                            }
                        }
                    }
                    // Journal Detail Process Item END
                }
            }


            Session::flash('success', 'Sukses approve data Inventory Out!');
            return redirect()->route('admin.inventory_out.index');
        }
        catch (\Exception $ex){
            Log::channel('error')->error('InventoryOurController approve - error EX: '. $ex);
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back();
        }
    }





    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

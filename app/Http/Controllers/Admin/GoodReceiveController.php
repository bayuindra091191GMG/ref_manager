<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\Branch;
use App\Models\Coa;
use App\Models\Configuration;
use App\Models\GoodReceiveDetail;
use App\Models\GoodReceiveHeader;
use App\Models\Item;
use App\Models\JournalDetail;
use App\Models\JournalHeader;
use App\Models\JournalMap;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrderHeader;
use App\Transformer\GoodReceiveTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class GoodReceiveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function select(Request $request){
        $user = Auth::guard('admin')->user();

        $term = trim($request->search);

        $searchDatas = DB::table('good_receive_headers')
            ->select('id','code','description')
            ->where(function($q) use ($term){
                $q->Where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('description', 'LIKE', '%'. $term. '%');
            })
            ->where('status_id', 13)
            ->where('branch_id', $user->branch_id)
            ->orderByDesc('id')
            ->get();

        $formatted_tags = [];

        foreach ($searchDatas as $searchData) {
            $formatted_tags[] = ['id' => $searchData->id, 'text' => $searchData->code];
        }

        return Response::json($formatted_tags);
    }

    public function selectInvoice(Request $request){
        $user = Auth::guard('admin')->user();

        $term = trim($request->search);
        $supplierId = trim($request->supplierId);

        $searchDatas = DB::table('good_receive_headers')
            ->select('id','code','description', 'po_code')
            ->where(function($q) use ($term){
                $q->whereRaw('UPPER(code) LIKE ?', ['%' . strtoupper($term) . '%'])
                    ->orWhereRaw('UPPER(po_code) LIKE ?', ['%' . strtoupper($term) . '%'])
                    ->orWhereRaw('UPPER(description) LIKE ?', ['%' . strtoupper($term) . '%']);
            })
            ->where('supplier_id', $supplierId)
            ->where('branch_id', $user->branch_id)
            ->where('status_id', 13)
            ->where('is_close', 0)
            ->orderByDesc('id')
            ->get();

        $formatted_tags = [];

        foreach ($searchDatas as $searchData) {
            $formatted_tags[] = ['id' => $searchData->id, 'text' => $searchData->code. " - ".$searchData->po_code];
        }

        return Response::json($formatted_tags);
    }

    public function getTotalforInvoice(Request $request){
        $grId = trim($request->gr_id);
        $returnData = null;
        if(!empty($grId)){
            $data = GoodReceiveHeader::where('id',  $grId)
                ->first();
//            $amount = GoodReceiveDetail::where('header_id', $grId)->sum('total');

            $dataPO = PurchaseOrderHeader::where('id', $data->po_id)->first();
            $amount = PurchaseOrderDetail::where('header_id', $dataPO->id)->sum('total');
            $returnData = [
                'gr_id' => $data->id,
                'gr_code' => $data->code,
                'po_code' => $dataPO->code,
                'amount' => $dataPO->grandtotal,
                'desc' => $data->description,
            ];
        }
        return Response::json($returnData);
    }

    public function getIndex(Request $request){
        $user = Auth::guard('admin')->user();
        $datas = GoodReceiveHeader::with(['status', 'supplier', 'warehouse'])
            ->where('status_id', '!=', 2)
            ->where('branch_id', $user->branch_id)
            ->orderByDesc('id');

        return DataTables::of($datas)
            ->setTransformer(new GoodReceiveTransformer)
            ->make(true);
    }

    public function index(){
        return view('admin.good_receive.index');
    }

    public function create(Request $request){
        $user = Auth::guard('admin')->user();
        if($user->can('branch.scope_all') == 1){
            $branches = Branch::all();
        }
        else{
            $branches = Branch::find($user->branch_id);
        }

        $isPo = 0;
        $referenceId = trim($request->reference_id);
        $referenceCode = "";
        $prId = 0;
        $prCode = "";
        $supplierId = 0;
        $supplierName = "";
        $warehouseId = 0;
        $warehouseName = "";
        $poDescription = "";

        $detailData = [];
        if(!empty($referenceId)){
            $referenceData = PurchaseOrderHeader::where('id', $referenceId)->first();

            $supplierId = $referenceData->supplier_id;
            $supplierName = $referenceData->supplier->name;
            $warehouseId = $referenceData->warehouse_id;
            $warehouseName = $referenceData->warehouse->name;
            $poDescription = $referenceData->description;
            $prId = $referenceData->reference_id;
            $prCode = $referenceData->reference_code;

            $isPo = 1;
            $referenceCode = $referenceData->code;
            foreach ($referenceData->purchase_order_details as $dataDetail){
                // Sum qty dari GoodReceiveDetail berdasarkan header_id yang berasal dari GoodReceiveHeader
                $grDetailSum = GoodReceiveDetail::whereHas('good_receive_header', function($q) use ($referenceId) {
                    $q->where('po_id', $referenceId)
                    ->where('status_id', 1);
                })->where('item_id', $dataDetail->item_id)
                ->where('id', "!=", $dataDetail->id)
                ->sum('qty');

                $data = [
                    "itemId"        => $dataDetail->item_id,
                    "itemName"        => $dataDetail->item->name,
                    "unitId"        => $dataDetail->unit_id,
                    "unitName"        => $dataDetail->unit_material->name,
                    "qtyPo"         => $dataDetail->qty,
                    "qtyGr"           => $dataDetail->qty_gr + $grDetailSum,
                    "qty"           => $dataDetail->qty - $dataDetail->qty_gr - $grDetailSum,
                    "price"         => $dataDetail->price,
                    "subtotal"         => $dataDetail->subtotal,
                ];
                if($dataDetail->qty_gr + $grDetailSum != $dataDetail->qty){
                    array_push($detailData, $data);
                }
            }

        }

        $vueData = [
            'itemList' => $detailData,
        ];

        $data = [
            'branches'      => $branches,
            'isPo'          => $isPo,
            'referenceId'   => $referenceId,
            'referenceCode' => $referenceCode,
            'vueData'       => $vueData,
            'prId'          => $prId,
            'prCode'        => $prCode,
            'supplierId'    => $supplierId,
            'supplierName'  => $supplierName,
            'warehouseId'   => $warehouseId,
            'warehouseName' => $warehouseName,
            'poDescription' => $poDescription,
        ];

        return view('admin.good_receive.create')->with($data);
    }

    public function store(Request $request){
        try{
//            dd($request);
            $itemIds = $request->input('itemIds');
            $qtys = $request->input('qtys');
            $unitIds = $request->input('unitIds');
            $prices = $request->input('prices');
            $totals = $request->input('totals');
            $maxQtys = $request->input('maxQtys');

            if(count($itemIds) > 0){
                if(count($itemIds) != count($qtys) && count($itemIds)  != count($unitIds) && count($itemIds)  != count($prices) ){
                    return back()->withErrors("Detail Item tidak lengkap")->withInput($request->all());
                }
            }

            $user = Auth::guard('admin')->user();
            $data = new GoodReceiveHeader();
            $data->code = AutoNumberGenerator::generateAutonumberTrx('GR');
            $data->branch_id = $user->branch_id;
            $data->date = Carbon::parse($request->input('date'))->toDateTimeString();
            $data->supplier_id = $request->input('supplier_id');
            $data->warehouse_id = $request->input('warehouse_id');
            $data->description = $request->input('description') ?? "";
            $data->status_id = 1;
            $data->is_close = 0;
            $data->created_by = $user->id;
            if(!empty($request->input('po_id'))){
                $data->po_id = $request->input('po_id');
                $data->po_code = PurchaseOrderHeader::find($request->input('po_id'))->code;
                $data->pr_id = $request->input('pr_id');
                $data->pr_code = $request->input('pr_code');
            }
            $data->save();

//            AutoNumberGenerator::nextAutonumber('GR');

            $ct = 0;
            foreach ($itemIds as $itemId){
                $dataDetail = new GoodReceiveDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->item_id = $itemId;
                $dataDetail->unit_id = $unitIds[$ct];
                $dataDetail->qty = $qtys[$ct] == null ? 1 : Utilities::toFloat($qtys[$ct]);
                $dataDetail->purchase_price = $prices[$ct] == null ? 1 :  Utilities::toFloat($prices[$ct]);
                $dataDetail->total = $totals[$ct] == null ? 1 :  Utilities::toFloat($totals[$ct]);
                $dataDetail->max_qty = $maxQtys[$ct] == null ? 1 :  Utilities::toFloat($maxQtys[$ct]);
                $dataDetail->created_by = $user->id;
                $dataDetail->save();

                $ct++;
            }

            Session::flash('success', 'Sukses membuat data Good Receive baru!');
            return redirect()->route('admin.good_receive.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex->getMessage());
            return back()->withInput($request->all());
        }
    }

    public function edit($id){
        $user = Auth::guard('admin')->user();
        if($user->can('branch.scope_all') == 1){
            $branches = Branch::all();
        }
        else{
            $branches = Branch::find($user->branch_id);
        }

        $dataDB = GoodReceiveHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }

        $supplierId = 0;
        $supplierName = "";
        $warehouseId = 0;
        $warehouseName = "";
        $poDescription = "";

        $detailData = [];

        foreach ($dataDB->good_receive_details as $dataDetail){

            $supplierId = $dataDB->supplier_id;
            $supplierName = $dataDB->supplier->name;
            $warehouseId = $dataDB->warehouse_id;
            $warehouseName = $dataDB->warehouse->name;

            $dataPODetail = PurchaseOrderDetail::where('header_id',$dataDB->po_id)
                ->where('item_id', $dataDetail->item_id)
                ->first();
            $poDescription = $dataDB->purchase_order_header->description;

            $grDetailSum = GoodReceiveDetail::whereHas('good_receive_header', function($q) use ($dataDB) {
                $q->where('po_id', $dataDB->po_id);
            })->where('item_id', $dataDetail->item_id)
            ->where('id', "!=", $dataDetail->id)
            ->sum('qty');

            $data = [
                "itemId"        => $dataPODetail->item_id,
                "itemName"        => $dataPODetail->item->name,
                "unitId"        => $dataPODetail->unit_id,
                "unitName"        => $dataPODetail->unit_material->name,
                "qtyPo"         => $dataPODetail->qty,
                "qtyGr"         => $grDetailSum,
                "qty"           => $dataDetail->qty,
                "price"         => $dataDetail->purchase_price,
                "subtotal"         => $dataDetail->total,
            ];
            array_push($detailData, $data);
        }

        $vueData = [
            'itemList' => $detailData
        ];

        $data = [
            'data'      => $dataDB,
            'branches'      => $branches,
            'vueData'       => $vueData,
            'prId'          => $dataDB->pr_id,
            'prCode'        => $dataDB->pr_code,
            'supplierId'    => $supplierId,
            'supplierName'    => $supplierName,
            'warehouseId'    => $warehouseId,
            'warehouseName'    => $warehouseName,
            'poDescription'    => $poDescription,
        ];

        return view('admin.good_receive.edit')->with($data);
    }

    public function update(Request $request){
        try{
            $itemIds = $request->input('itemIds');
            $qtys = $request->input('qtys');
            $unitIds = $request->input('unitIds');
            $prices = $request->input('prices');
            $totals = $request->input('totals');
            $maxQtys = $request->input('maxQtys');

            if(count($itemIds) > 0){
                if(count($itemIds) != count($qtys) && count($itemIds)  != count($unitIds) && count($itemIds)  != count($prices) ){
                    return back()->withErrors("Detail Item tidak lengkap")->withInput($request->all());
                }
            }

            $user = Auth::guard('admin')->user();
            $data = GoodReceiveHeader::where('id', $request->input('id'))->first();
            if($data == null){
                Session::flash('error', 'Data tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $data->branch_id = $user->branch_id;
            $data->date = Carbon::parse($request->input('date'))->toDateTimeString();
            $data->supplier_id = $request->input('supplier_id');
            $data->warehouse_id = $request->input('warehouse_id');
            $data->description = $request->input('description') ?? "";
            $data->updated_by = $user->id;
            $data->save();

            $ct = 0;
            $oldData = GoodReceiveDetail::where('header_id', $request->input('id'))->get();
            if(count($oldData) > 0){
                foreach($oldData as $oldDetaildata){
                    $oldDetaildata->delete();
                }
            }
            foreach ($itemIds as $itemId){
                $dataDetail = new GoodReceiveDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->item_id = $itemId;
                $dataDetail->unit_id = $unitIds[$ct];
                $dataDetail->qty = $qtys[$ct] == null ? 1 : Utilities::toFloat($qtys[$ct]);
                $dataDetail->purchase_price = $prices[$ct] == null ? 1 :  Utilities::toFloat($prices[$ct]);
                $dataDetail->total = $totals[$ct] == null ? 1 :  Utilities::toFloat($totals[$ct]);
                $dataDetail->max_qty = $maxQtys[$ct] == null ? 1 :  Utilities::toFloat($maxQtys[$ct]);
                $dataDetail->created_by = $user->id;
                $dataDetail->save();

                $ct++;
            }

            Session::flash('success', 'Sukses mengubah data Good Receive!');
            return redirect()->route('admin.good_receive.index');
        }
        catch (\Exception $ex){
            dd($ex);
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function show($id, Request $request){
        $user = Auth::guard('admin')->user();
        $branches = Branch::find($user->branch_id);

        $dataDB = GoodReceiveHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }

        $supplierId = 0;
        $supplierName = "";
        $warehouseId = 0;
        $warehouseName = "";
        $poDescription = "";

        $detailData = [];

        foreach ($dataDB->good_receive_details as $dataDetail){

            $supplierId = $dataDB->supplier_id;
            $supplierName = $dataDB->supplier->name;
            $warehouseId = $dataDB->warehouse_id;
            $warehouseName = $dataDB->warehouse->name;

            $dataPODetail = PurchaseOrderDetail::where('header_id',$dataDB->po_id)
                ->where('item_id', $dataDetail->item_id)
                ->first();
            $poDescription = $dataDB->purchase_order_header->description;

            $data = [
                "itemId"        => $dataPODetail->item_id,
                "itemName"        => $dataPODetail->item->name,
                "unitId"        => $dataPODetail->unit_id,
                "unitName"        => $dataPODetail->unit_material->name,
                "qtyPo"         => $dataPODetail->qty,
                "qtyGr"         => $dataPODetail->qty_gr,
                "qty"           => $dataDetail->qty,
                "price"         => $dataDetail->purchase_price,
                "total"         => $dataDetail->purchase_price * $dataDetail->qty,
                "subtotal"         => $dataDetail->total,
            ];
            array_push($detailData, $data);
        }

        $vueData = [
            'itemList' => $detailData
        ];

        $createdBy = AdminUser::where('id', $dataDB->created_by)->first();
        $data = [
            'data'      => $dataDB,
            'branches'      => $branches,
            'vueData'       => $vueData,
            'itemList'       => $detailData,
            'prId'          => $dataDB->pr_id,
            'prCode'        => $dataDB->pr_code,
            'supplierId'    => $supplierId,
            'supplierName'    => $supplierName,
            'warehouseId'    => $warehouseId,
            'warehouseName'    => $warehouseName,
            'poDescription'    => $poDescription,
            'createdBy' => $createdBy != null ? $createdBy->name : "-",
        ];
        $type = trim($request->type);
        if(!empty($type)){
            return view('admin.good_receive.print')->with($data);
        }

        return view('admin.good_receive.show')->with($data);
    }

    public function delete(Request $request){
        try{
            if(!$request->filled('password')){
                return Response::json('error', 200);
            }

            //check password first
            $config = Configuration::where('key', 'spv_password')->first();
            if(!Hash::check($request->input('password'), $config->value)){
                return Response::json('error', 200);
            }

            $dataDB = GoodReceiveHeader::where('id', $request->input('id'))->first();
            if($dataDB == null){
                return Response::json('Data tidak ditemukan!', 200);
            }
            $dataDB->status_id = 2;
            $dataDB->save();

            foreach ($dataDB->good_receive_details as $detail){
                $poDetail = PurchaseOrderDetail::where('item_id', $detail->item_id)->where('header_id',$dataDB->po_id)->first();
                if(!empty($poDetail)){
                    if($poDetail->qty_gr > 0){
                        $poDetail->qty_gr = $poDetail->qty_gr - $detail->qty;
                        $poDetail->save();
                    }
                }
            }
            $poData = PurchaseOrderHeader::where('id', $dataDB->po_id)->first();
            $poData->is_gr = 0;
            $poData->save();

            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json('BAD REQUEST!', 200);
        }
    }

    public function unapprove(Request $request){
        try{
            if(!$request->filled('password')){
                return Response::json('error', 200);
            }

            //check password first
            $config = Configuration::where('key', 'spv_password')->first();
            if(!Hash::check($request->input('password'), $config->value)){
                return Response::json('error', 200);
            }

            $admin = Auth::guard('admin')->user();
            $dataDB = GoodReceiveHeader::where('id', $request->input('id'))->first();
            if($dataDB == null){
                Session::flash('error', 'Data tidak ditemukan!');
                return back();
            }
            $dataDB->status_id = 1;
            $dataDB->save();

            foreach ($dataDB->good_receive_details as $detail){
                $poDetail = PurchaseOrderDetail::where('item_id', $detail->item_id)->where('header_id',$dataDB->po_id)->first();
                if(!empty($poDetail)){
                    $poDetail->qty_gr = $poDetail->qty_gr - $detail->qty;
                    $poDetail->save();
                }


                Utilities::updateStock($admin->branch_id,
                    'min',
                    $detail->qty,
                    $detail->item_id,
                    $dataDB->warehouse_id,
                    $dataDB->code,
                    'Good Receive Unapprove '.$dataDB->code);
            }
            $poData = PurchaseOrderHeader::where('id', $dataDB->po_id)->first();
            $poData->is_gr = 0;
            $poData->save();

            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json('error', 200);
        }
    }

    public function approveOld(Request $request){
        try{
            $admin = Auth::guard('admin')->user();
            $userData = AdminUser::find($admin->id);
            $ids = $request->input('ids');
            if(empty($ids)){
                Session::flash('error', 'Tidak Ada Data yang Dipilih');
                return redirect()->route('admin.purchase_order.index');
            }
            foreach ($ids as $id) {
                $dataDB = GoodReceiveHeader::where('id', $id)->first();
                $poData = PurchaseOrderHeader::where('id', $dataDB->po_id)->first();
                if($dataDB == null){
                    Session::flash('error', 'Data tidak ditemukan!');
                    return back();
                }

                //allow to update
                $canUpdate = false;
                $successString = "";
                $failedString = "";
                try{
                    //check qty
                    foreach ($dataDB->good_receive_details as $detail){
                        $poDetail = PurchaseOrderDetail::where('item_id', $detail->item_id)->where('header_id',$dataDB->po_id)->first();
                        if(!empty($poDetail)){
                            $poDetail->qty_gr = $poDetail->qty_gr + $detail->qty;
                            $poDetail->save();
                        }
                    }

                    //check on all pr details and update PR
                    $donePoDtCount = PurchaseOrderDetail::where('header_id', $dataDB->po_id)
                        ->whereRaw('purchase_order_details.qty = purchase_order_details.qty_gr')
                        ->count();
                    $poDtCount = PurchaseOrderDetail::where('header_id', $dataDB->po_id)->count();
                    if($donePoDtCount > 0 && $donePoDtCount == $poDtCount){
                        $poData->is_po = 1;
                        $poData->save();
                    }
                    $canUpdate = true;
                    $successString .= $dataDB->code. " | ";
                }
                catch (\Exception $ex){
                    $failedString .= "[".$dataDB->code. "] - ". $ex->getMessage() ." ";
                }
                if($canUpdate){
                    $dataDB->is_approve = 1;
                    $dataDB->status_id = 13;
                    $dataDB->updated_by = $admin->id;
                    $dataDB->save();

                    foreach ($dataDB->good_receive_details as $detail){
                        Utilities::updateStock($admin->branch_id,
                            'add',
                            $detail->qty,
                            $detail->item_id,
                            $dataDB->warehouse_id,
                            $dataDB->code,
                            'Good Receive '.$dataDB->code);

                    }
                }
            }

            if(empty($successString)){
                Session::flash('error', 'Gagal approve data Good Receive '. $failedString);
            }
            else{
                Session::flash('success', 'Sukses approve data Good Receive '. $successString);
            }
            return redirect()->route('admin.good_receive.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back();
        }
    }

    public function approve(Request $request){
        try{
            if(!$request->filled('password')){
                Session::flash('error', 'Password wajib diisi!');
                return redirect()->route('admin.good_receive.index');
            }

            //check password first
            $config = Configuration::where('key', 'spv_password')->first();
            if(!Hash::check($request->input('password'), $config->value)){
                Session::flash('error', 'Password Supervisor Salah!');
                return redirect()->route('admin.good_receive.index');
            }

            $admin = Auth::guard('admin')->user();
            $userData = AdminUser::find($admin->id);
            $ids = $request->input('ids');
            if(empty($ids)){
                Session::flash('error', 'Tidak Ada Data yang Dipilih');
                return redirect()->route('admin.good_receive.index');
            }
            foreach ($ids as $id) {
                $dataDB = GoodReceiveHeader::where('id', $id)->first();
                $poData = PurchaseOrderHeader::where('id', $dataDB->po_id)->first();
//                dd($poData);
                if($dataDB == null){
                    Session::flash('error', 'Data tidak ditemukan!');
                    return back();
                }

                //allow to update
                $canUpdate = false;
                $successString = "";
                $failedString = "";
                try{
                    //check qty
                    foreach ($dataDB->good_receive_details as $detail){
                        $poDetail = PurchaseOrderDetail::where('item_id', $detail->item_id)->where('header_id',$dataDB->po_id)->first();
                        if(!empty($poDetail)){
                            $poDetail->qty_gr = $poDetail->qty_gr + $detail->qty;
                            $poDetail->save();

                            //check dan input harga terbaru barang
                            // $itemDB = Item::where('id', $detail->item_id)->first();
                            // if(!empty($itemDB)){
                            //     $itemDB->cost_price = $poDetail->price;
                            //     $itemDB->save();
                            // }
                        }
                    }

                    //check on all pr details and update PR
                    $donePoDtCount = PurchaseOrderDetail::where('header_id', $dataDB->po_id)
                        ->whereRaw('purchase_order_details.qty = purchase_order_details.qty_gr')
                        ->count();
                    $poDtCount = PurchaseOrderDetail::where('header_id', $dataDB->po_id)->count();
                    if($donePoDtCount == $poDtCount){
                        $poData->is_gr = 1;
                        $poData->save();
                    }
                    $canUpdate = true;
                    $successString .= $dataDB->code. " | ";
                }
                catch (\Exception $ex){
                    $failedString .= "[".$dataDB->code. "] - ". $ex->getMessage() ." ";
                }
                if($canUpdate){
                    $dataDB->is_approve = 1;
                    $dataDB->status_id = 13;
                    $dataDB->save();

                    // Journal Header Process START
                    $journal = new JournalHeader();
                    $journal->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                    $journal->transaction_type = 'GOOD_RECEIVE';
                    $journal->transaction_date = $dataDB->date;
                    $journal->notes = "Jurnal Good Receive ".$dataDB->code;
                    $journal->created_by = $userData->id;
                    $journal->branch_id = $userData->branch_id;
                    $journal->reference_id = $dataDB->id;
                    $journal->reference_code = $dataDB->code;
                    $journal->status_id = 1;
                    $journal->save();
                    // Journal Header Process END

                    $totalHutangBerelasi = 0;
                    $totalHutangUsaha = 0;

                    foreach ($dataDB->good_receive_details as $detail){
                        Utilities::updateStock($admin->branch_id,
                            'add',
                            $detail->qty,
                            $detail->item_id,
                            $dataDB->warehouse_id,
                            $dataDB->code,
                            'Good Receive '.$dataDB->code);

                        // Journal Detail Process Item START
                        $itemsDB = Item::where('id', $detail->item_id)->first();

                        if(!empty($itemsDB->category_2_id)){
                            $journalMap = JournalMap::where('branch_id', $userData->branch_id)
                                ->where('transaction_type', 'GOOD_RECEIVE')
                                ->where('journal_type', 'DEBET')
                                ->where('coa_name', '!=', 'BEBAN ONGKIR')
                                ->where('category_2_id', $itemsDB->category_2_id)
                                ->first();
//                            dd($journalMap);
                            if(!empty($journalMap)){
                                $coa = Coa::where('id', $journalMap->coa_id)->first();

                                $jDt = new JournalDetail();
                                $jDt->branch_id = $userData->branch_id;
                                $jDt->header_id = $journal->id;
                                $jDt->account_code = $coa->code;
                                $jDt->account_type_id = $coa->type_id;
                                $jDt->account_name = $coa->name;
                                $jDt->account_id = $coa->id;
                                $jDt->account_type_name = $coa->type;
                                $jDt->debit = $detail->total;
                                $jDt->credit = 0;
                                $jDt->notes = 'Good Receive ' . $dataDB->code. ' item '.$itemsDB->name;
                                $jDt->reference_code = $dataDB->code;
//                            $jDt->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                                $jDt->transaction_code = $journal->transaction_code;
                                $jDt->transaction_type = 'GOOD_RECEIVE';
                                $jDt->original_amount = $detail->total;
                                $jDt->save();

                                if($itemsDB->category_2_id == 14){
                                    $totalHutangBerelasi += $detail->total;
                                }
                                else{
                                    $totalHutangUsaha += $detail->total;
                                }
                            }
                        }
                        // Journal Detail Process Item END
                    }

                    // Journal Detail Process beban ongkir START
                    if($dataDB->beban_ongkir != 0){
                        $journalMapBebanOngkir = JournalMap::where('branch_id', $userData->branch_id)
                            ->where('transaction_type', 'GOOD_RECEIVE')
                            ->where('coa_name', 'BEBAN ONGKIR')
                            ->first();
                        if(!empty($journalMapBebanOngkir)){
                            $coa = Coa::where('id', $journalMapBebanOngkir->coa_id)->first();

                            $jDt = new JournalDetail();
                            $jDt->branch_id = $userData->branch_id;
                            $jDt->header_id = $journal->id;
                            $jDt->account_code = $coa->code;
                            $jDt->account_type_id = $coa->type_id;
                            $jDt->account_name = $coa->name;
                            $jDt->account_id = $coa->id;
                            $jDt->account_type_name = $coa->type;
                            $jDt->debit = $dataDB->beban_ongkir;
                            $jDt->credit = 0;
                            $jDt->notes = 'Good Receive : BEBAN ONGKIR ' . $dataDB->code;
                            $jDt->reference_code = $dataDB->code;
//                            $jDt->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                            $jDt->transaction_code = $journal->transaction_code;
                            $jDt->transaction_type = 'GOOD_RECEIVE';
                            $jDt->original_amount = $dataDB->beban_ongkir;
                            $jDt->save();
                        }
                    }
                    // Journal Detail Process beban ongkir END


                    // Journal Detail Process hutang START
                    if($totalHutangBerelasi != 0){
                        $journalMapHutangBerelasi = JournalMap::where('branch_id', $userData->branch_id)
                            ->where('transaction_type', 'GOOD_RECEIVE')
                            ->where('coa_name', 'Hutang - Pihak Berelasi (Pendek)')
                            ->first();
                        if(!empty($journalMapHutangBerelasi)){
                            $coa = Coa::where('id', $journalMapHutangBerelasi->coa_id)->first();

                            $jDt = new JournalDetail();
                            $jDt->branch_id = $userData->branch_id;
                            $jDt->header_id = $journal->id;
                            $jDt->account_code = $coa->code;
                            $jDt->account_type_id = $coa->type_id;
                            $jDt->account_name = $coa->name;
                            $jDt->account_id = $coa->id;
                            $jDt->account_type_name = $coa->type;
                            $jDt->debit = 0;
                            $jDt->credit = $totalHutangBerelasi;
                            $jDt->notes = 'Good Receive : Hutang - Pihak Berelasi ' . $dataDB->code;
                            $jDt->reference_code = $dataDB->code;
//                            $jDt->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                            $jDt->transaction_code = $journal->transaction_code;
                            $jDt->transaction_type = 'GOOD_RECEIVE';
                            $jDt->original_amount = $totalHutangBerelasi;
                            $jDt->save();
                        }
                    }
                    if($totalHutangUsaha != 0){
                        $journalMapHutangUsaha = JournalMap::where('branch_id', $userData->branch_id)
                            ->where('transaction_type', 'GOOD_RECEIVE')
                            ->where('coa_name', 'Hutang Usaha')
                            ->first();
                        if(!empty($journalMapHutangUsaha)){
                            $coa = Coa::where('id', $journalMapHutangUsaha->coa_id)->first();

                            $jDt = new JournalDetail();
                            $jDt->branch_id = $userData->branch_id;
                            $jDt->header_id = $journal->id;
                            $jDt->account_code = $coa->code;
                            $jDt->account_type_id = $coa->type_id;
                            $jDt->account_name = $coa->name;
                            $jDt->account_id = $coa->id;
                            $jDt->account_type_name = $coa->type;
                            $jDt->debit = 0;
                            $jDt->credit = $totalHutangUsaha;
                            $jDt->notes = 'Good Receive : Hutang Usaha ' . $dataDB->code;
                            $jDt->reference_code = $dataDB->code;
//                            $jDt->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                            $jDt->transaction_code = $journal->transaction_code;
                            $jDt->transaction_type = 'GOOD_RECEIVE';
                            $jDt->original_amount = $totalHutangUsaha;
                            $jDt->save();
                        }
                    }
                    // Journal Detail Process hutang END
                }
            }

            if(empty($successString)){
                Session::flash('error', 'Gagal approve data Good Receive '. $failedString);
            }
            else{
                Session::flash('success', 'Sukses approve data Good Receive '. $successString);
            }
            return redirect()->route('admin.good_receive.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back();
        }
    }

    public function filter(){
        $startDate = Carbon::now('Asia/Jakarta');
        $endDate = Carbon::now('Asia/Jakarta');
        $endDate->addDay();

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate'   => $endDate->format('d-m-Y'),
        ];

        return view('admin.report.report_gr_filter')->with($data);
    }

    public function report(Request $request){
        try{
            $rules = array(
                'start_date'=> 'required',
                'end_date'=> 'required',
                'type '=> 'required',
            );

            $message = array(
                'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required'     => 'Tanggal Akhir Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if($validator->fails()){
                return Response::json(['error' => $validator->errors()->all()]);
            }
            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDays(1);

            $result = [];
            $type = $request->input('type');

            $apDetails = DB::table('good_receive_headers')
                ->join('good_receive_headers', 'good_receive_headers.id', '=', 'good_receive_details.header_id')
                ->select(
                    'ap_detail1s.header_id as id',
                    'ap_detail1s.header_code as code',
                    'ap_headers.date as date',
                    'ap_headers.pr_code as pr_code',
                    'ap_headers.po_code as po_code',
                    'ap_headers.date as date',
                    'ap_detail1s.debit as debit',
                )
                ->where('ap_headers.status_id', '!=' , 2)
                ->whereBetween('ap_headers.created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->get();

            $headers = GoodReceiveHeader::where('status_id', '!=' , 2)
                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->orderby('supplier_id')->get();

            $user = Auth::guard('admin')->user();
            $branch = Branch::where('id', $user->branch_id)->first();

            $currentSupp = 0;
            $nResult = [];
            $totalQty = 0;
            $no = 1;
            foreach ($headers as $header){
                foreach($header->good_receive_details as $detail){
                    $nTrx = [
                        'no'                => $no,
                        'code'              => $header->code,
                        'date'              => $header->date->locale('id_ID')->toDateTimeString(),
                        'pr_code'           => $header->pr_code,
                        'po_code'           => $header->po_code,
                        'warehouse'           => $header->warehouse->name,
                        'item_code'           => $detail->item->code,
                        'item_name'           => $detail->item->name,
                        'unit_name'           => $detail->unit_material->name,
                        'qty'       => number_format($detail->qty, '0', ',', '.'),
                        'description'           => $detail->description,
                    ];
                    if($currentSupp != $header->supplier_id){
                        $nResult = [
                            'supplier_code'     => $header->supplier->code,
                            'supplier_name'     => $header->supplier->name,
                            'transactions'      => [],
                            'subtotal_qty'  => $header->qty,
                        ];
                        $nResult['transactions'][] = $nTrx;
                        $currentSupp = $header->supplier_id;
                        $no = 1;
                    }
                    else{

                        if(!in_array($nTrx, $nResult['transactions'])){
                            $nResult['transactions'][] = $nTrx;
                            $nResult['subtotal_qty'] += $header->qty;
                        }
                    }
                    $result[] = $nResult;
                    $no++;
                }

                $totalQty += $header->grandtotal;
            }

            $data = [
                'result' => $result,
                'type' => $type,
                'branch' => $branch,
                'start_date' => $startDate->locale('id_ID')->toDateTimeString(),
                'end_date' => $endDate->locale('id_ID')->toDateTimeString(),
            ];
            return view('admin.good_receive.report')->with($data);
        }
        catch (\Exception $ex){
            Session::flash('error', $ex);
            return back();
        }
    }
}

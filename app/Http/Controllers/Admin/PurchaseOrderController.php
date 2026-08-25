<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\Branch;
use App\Models\Configuration;
use App\Models\Item;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrderHeader;
use App\Models\PurchaseRequestDetail;
use App\Models\PurchaseRequestHeader;
use App\Transformer\PurchaseOrderTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function select(Request $request){
        $user = Auth::guard('admin')->user();

        $term = trim($request->search);

        $searchDatas = DB::table('purchase_order_headers')
            ->select('id','code','description')
            ->where('branch_id', $user->branch_id)
            ->where(function($q) use ($term){
                $q->Where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('description', 'LIKE', '%'. $term. '%');
            })
            ->where('status_id', 13)
            ->where('is_gr', '!=', 1)
            ->orderByDesc('id')
            ->get();

        $formatted_tags = [];

        foreach ($searchDatas as $searchData) {
            $formatted_tags[] = ['id' => $searchData->id, 'text' => $searchData->code];
        }

        return Response::json($formatted_tags);
    }

    public function getIndex(Request $request){
        $user = Auth::guard('admin')->user();
        $datas = PurchaseOrderHeader::with(['status', 'supplier', 'warehouse'])
            // ->where('status_id', '!=', 2)
            ->where('branch_id', $user->branch_id)
            ->orderByDesc('id');

        return DataTables::of($datas)
            ->setTransformer(new PurchaseOrderTransformer)
            ->make(true);
    }

    public function index(){
        return view('admin.purchase_order.index');
    }

    public function create(Request $request){
        $user = Auth::guard('admin')->user();
        if($user->can('branch.scope_all') == 1){
            $branches = Branch::all();
        }
        else{
            $branches = Branch::find($user->branch_id);
        }

        $isPr = 0;
        $referenceId = trim($request->reference_id);
        $referenceCode = "";
        $supplierId = 0;
        $supplierName = "";
        $warehouseId = 0;
        $warehouseName = "";
        $prDescription = "";

        $detailData = [];
        if(!empty($referenceId)){
            $referenceData = PurchaseRequestHeader::where('id', $referenceId)->first();

            if(!empty($referenceData->supplier_id)){
                $supplierId = $referenceData->supplier_id;
                $supplierName = $referenceData->supplier->name;
            }
            $warehouseId = $referenceData->warehouse_id;
            $warehouseName = $referenceData->warehouse->name;
            $prDescription = $referenceData->description;

            $isPr = 1;
            $referenceCode = $referenceData->code;
            foreach ($referenceData->purchase_request_details as $dataDetail){
                $data = [
                    "itemId"        => $dataDetail->item_id,
                    "unitId"        => $dataDetail->unit_id,
                    "qtyPr"         => $dataDetail->qty - $dataDetail->qty_po,
                    "qty"           => 0,
                    "price"         => 0,
                    "disc1"         => 0,
                    "disc1Value"    => 0,
                    "disc2"         => 0,
                    "disc2Value"    => 0,
                ];
                array_push($detailData, $data);
            }

        }

        $vueData = [
            'itemList' => $detailData,
        ];

        $data = [
            'branches'      => $branches,
            'isPr'          => $isPr,
            'referenceId'   => $referenceId,
            'referenceCode' => $referenceCode,
            'vueData'       => $vueData,
            'supplierId'    => $supplierId,
            'supplierName'    => $supplierName,
            'warehouseId'    => $warehouseId,
            'warehouseName'    => $warehouseName,
            'prDescription'    => $prDescription,
        ];

        return view('admin.purchase_order.create')->with($data);
    }

    public function store(Request $request){
        try{
            $itemIds = $request->input('itemIds');
            $qtys = $request->input('qtys');
            $qtyPrs = $request->input('qtyPrs');
            $unitIds = $request->input('unitIds');
            $prices = $request->input('prices');
            $totals = $request->input('totals');
            $disc1Percents = $request->input('disc1_percents');
            $disc1Values = $request->input('disc1_values');
            $disc2Percents = $request->input('disc2_percents');
            $disc2Values = $request->input('disc2_values');
            $subtotals = $request->input('subtotals');

            if(count($itemIds) > 0){
                if(count($itemIds) != count($qtys) && count($itemIds)  != count($unitIds) && count($itemIds)  != count($prices) ){
                    return back()->withErrors("Detail Item tidak lengkap")->withInput($request->all());
                }
            }

            // Validate item journal mappings
            // $feedbackMessage = '';
            // foreach($itemIds as $itemId){
            //     $item = DB::table('items')
            //         ->where('id', '=', $itemId)
            //         ->first();

            //     if(empty($item->category_2_id)){
            //         $feedbackMessage .= $item->name. ', ';
            //     }
            // }

            // if(!empty($feedbackMessage)){
            //     return back()->withErrors("Item ". $feedbackMessage. ' belum memiliki pemetaan jurnal!')->withInput($request->all());
            // }

            $user = Auth::guard('admin')->user();
            $data = new PurchaseOrderHeader();
            $data->code = AutoNumberGenerator::generateAutonumberTrx('PO');
            $data->branch_id = $user->branch_id;
            $data->transaction_date = Carbon::parse($request->input('transaction_date'))->toDateTimeString();
            $data->supplier_id = $request->input('supplier_id');
            $data->warehouse_id = $request->input('warehouse_id');
            $data->top = Utilities::toFloat($request->input('top'));
            $data->subtotal = Utilities::toFloat($request->input('subtotal'));
            $data->disc1_percent = Utilities::toFloat($request->input('disc1_percent'));
            $data->disc1_value = Utilities::toFloat($request->input('disc1_value'));
            $data->disc2_percent = Utilities::toFloat($request->input('disc2_percent'));
            $data->disc2_value = Utilities::toFloat($request->input('disc2_value'));
            $data->ppn_type = $request->input('ppn_type');
            $data->ppn_percent = Utilities::toFloat($request->input('ppn_percent'));
            $data->ppn_value = Utilities::toFloat($request->input('ppn_value'));
            $data->grandtotal = Utilities::toFloat($request->input('grandtotal'));
            $data->description = $request->input('description') ?? "";
            $data->status_id = 1;
            $data->is_gr = 0;
            $data->created_by = $user->id;
            if(!empty($request->input('reference_id'))){
                $data->reference_id = $request->input('reference_id');
                $data->reference_code = PurchaseRequestHeader::find($request->input('reference_id'))->code;
            }
            $data->save();

//            AutoNumberGenerator::nextAutonumber('PO');

            $ct = 0;
            foreach ($itemIds as $itemId){
                $dataDetail = new PurchaseOrderDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->item_id = $itemId;
                $dataDetail->unit_id = $unitIds[$ct];
                $dataDetail->qty_pr = $qtyPrs[$ct] == null ? 0 : Utilities::toFloat($qtyPrs[$ct]);
                $dataDetail->qty = $qtys[$ct] == null ? 0 : Utilities::toFloat($qtys[$ct]);
                $dataDetail->price = $prices[$ct] == null ? 0 :  Utilities::toFloat($prices[$ct]);
                $dataDetail->total = $totals[$ct] == null ? 0 :  Utilities::toFloat($totals[$ct]);
                $dataDetail->disc1_percent = $disc1Percents[$ct] == null ? 0 :  Utilities::toFloat($disc1Percents[$ct]);
                $dataDetail->disc1_value = $disc1Values[$ct] == null ? 0 :  Utilities::toFloat($disc1Values[$ct]);
                $dataDetail->disc2_percent = $disc2Percents[$ct] == null ? 0 :  Utilities::toFloat($disc2Percents[$ct]);
                $dataDetail->disc2_value = $disc2Values[$ct] == null ? 0 :  Utilities::toFloat($disc2Values[$ct]);
                $dataDetail->subtotal = $subtotals[$ct] == null ? 0 :  Utilities::toFloat($subtotals[$ct]);
                $dataDetail->qty_gr = 0;
                $dataDetail->created_by = $user->id;
                $dataDetail->save();

                $ct++;
            }

            Session::flash('success', 'Sukses membuat data Purchase Order baru!');
            return redirect()->route('admin.purchase_order.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
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

        $dataDB = PurchaseOrderHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }

        $supplierId = 0;
        $supplierName = "";
        $warehouseId = 0;
        $warehouseName = "";
        $prDescription = "";

        $detailData = [];

        foreach ($dataDB->purchase_order_details as $dataDetail){

            $supplierId = $dataDB->supplier_id;
            $supplierName = $dataDB->supplier->name;
            $warehouseId = $dataDB->warehouse_id;
            $warehouseName = $dataDB->warehouse->name;
            $prDescription = $dataDB->purchase_request_header->description;

            $data = [
                "id"            => $dataDetail->id,
                "itemId"        => $dataDetail->item_id,
                "unitId"        => $dataDetail->unit_id,
                "qtyPr"         => $dataDetail->qty_pr,
                "qty"           => $dataDetail->qty,
                "price"         => $dataDetail->price,
                "disc1"         => $dataDetail->disc1_percent,
                "disc1Value"    => $dataDetail->disc1_value,
                "disc2"         => $dataDetail->disc2_percent,
                "disc2Value"    => $dataDetail->disc2_value,
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
            'supplierId'    => $supplierId,
            'supplierName'    => $supplierName,
            'warehouseId'    => $warehouseId,
            'warehouseName'    => $warehouseName,
            'prDescription'    => $prDescription,
        ];

        return view('admin.purchase_order.edit')->with($data);
    }

    public function update(Request $request){
        try{

            $itemIds = $request->input('itemIds');
            $qtys = $request->input('qtys');
            $qtyPrs = $request->input('qtyPrs');
            $unitIds = $request->input('unitIds');
            $prices = $request->input('prices');
            $totals = $request->input('totals');
            $disc1Percents = $request->input('disc1_percents');
            $disc1Values = $request->input('disc1_values');
            $disc2Percents = $request->input('disc2_percents');
            $disc2Values = $request->input('disc2_values');
            $subtotals = $request->input('subtotals');

            if(count($itemIds) > 0){
                if(count($itemIds) != count($qtys) && count($itemIds)  != count($unitIds) && count($itemIds)  != count($prices) ){
                    return back()->withErrors("Detail Item tidak lengkap")->withInput($request->all());
                }
            }

            $user = Auth::guard('admin')->user();
            $data = PurchaseOrderHeader::where('id', $request->input('id'))->first();
            if($data == null){
                Session::flash('error', 'Data tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $data->branch_id = $user->branch_id;
            $data->transaction_date = Carbon::parse($request->input('transaction_date'))->toDateTimeString();
            $data->supplier_id = $request->input('supplier_id');
            $data->warehouse_id = $request->input('warehouse_id');
            $data->top = Utilities::toFloat($request->input('top'));
            $data->subtotal = Utilities::toFloat($request->input('subtotal'));
            $data->disc1_percent = Utilities::toFloat($request->input('disc1_percent'));
            $data->disc1_value = Utilities::toFloat($request->input('disc1_value'));
            $data->disc2_percent = Utilities::toFloat($request->input('disc2_percent'));
            $data->disc2_value = Utilities::toFloat($request->input('disc2_value'));
            $data->ppn_percent = Utilities::toFloat($request->input('ppn_percent'));
            $data->ppn_value = Utilities::toFloat($request->input('ppn_value'));
            $data->ppn_type = $request->input('ppn_type');
            $data->grandtotal = Utilities::toFloat($request->input('grandtotal'));
            $data->description = $request->input('description') ?? "";
            $data->updated_by = $user->id;
            $data->save();

            $ct = 0;
            $oldData = PurchaseOrderDetail::where('header_id', $request->input('id'))->get();
            if(count($oldData) > 0){
                foreach($oldData as $oldDetaildata){
                    $oldDetaildata->delete();
                }
            }
            foreach ($itemIds as $itemId){
                $dataDetail = new PurchaseOrderDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->item_id = $itemId;
                $dataDetail->unit_id = $unitIds[$ct];
                $dataDetail->qty_pr = $qtyPrs[$ct] == null ? 0 : Utilities::toFloat($qtyPrs[$ct]);
                $dataDetail->qty = $qtys[$ct] == null ? 0 : Utilities::toFloat($qtys[$ct]);
                $dataDetail->price = $prices[$ct] == null ? 0 :  Utilities::toFloat($prices[$ct]);
                $dataDetail->total = $totals[$ct] == null ? 0 :  Utilities::toFloat($totals[$ct]);
                $dataDetail->disc1_percent = $disc1Percents[$ct] == null ? 0 :  Utilities::toFloat($disc1Percents[$ct]);
                $dataDetail->disc1_value = $disc1Values[$ct] == null ? 0 :  Utilities::toFloat($disc1Values[$ct]);
                $dataDetail->disc2_percent = $disc2Percents[$ct] == null ? 0 :  Utilities::toFloat($disc2Percents[$ct]);
                $dataDetail->disc2_value = $disc2Values[$ct] == null ? 0 :  Utilities::toFloat($disc2Values[$ct]);
                $dataDetail->subtotal = $subtotals[$ct] == null ? 0 :  Utilities::toFloat($subtotals[$ct]);
                $dataDetail->qty_gr = 0;
                $dataDetail->created_by = $user->id;
                $dataDetail->save();

                $ct++;
            }

            Session::flash('success', 'Sukses mengubah data Purchase Order!');
            return redirect()->route('admin.purchase_order.index');
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

        $dataDB = PurchaseOrderHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }

        $supplierId = 0;
        $supplierName = "";
        $warehouseId = 0;
        $warehouseName = "";
        $prDescription = "";

        $detailData = [];

        foreach ($dataDB->purchase_order_details as $dataDetail){

            $supplierId = $dataDB->supplier_id;
            $supplierName = $dataDB->supplier->name;
            $warehouseId = $dataDB->warehouse_id;
            $warehouseName = $dataDB->warehouse->name;
            $prDescription = $dataDB->purchase_request_header->description;

            $data = [
                "itemId"        => $dataDetail->item_id,
                "itemName"        => $dataDetail->item->name,
                "unitId"        => $dataDetail->unit_id,
                "unitName"        => $dataDetail->unit_material->name,
                "qtyPr"         => $dataDetail->qty_pr,
                "qty"           => $dataDetail->qty,
                "price"         => $dataDetail->price,
                "disc1"         => $dataDetail->disc1_percent,
                "disc1Value"    => $dataDetail->disc1_value,
                "disc2"         => $dataDetail->disc2_percent,
                "disc2Value"    => $dataDetail->disc2_value,
                "total"         => $dataDetail->subtotal,
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
            'supplierId'    => $supplierId,
            'supplierName'    => $supplierName,
            'warehouseId'    => $warehouseId,
            'warehouseName'    => $warehouseName,
            'prDescription'    => $prDescription,
            'createdBy' => $createdBy != null ? $createdBy->name : "-",
        ];
        $type = trim($request->type);
        if(!empty($type)){
            return view('admin.purchase_order.print')->with($data);
        }


        return view('admin.purchase_order.show')->with($data);
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

            $dataDB = PurchaseOrderHeader::where('id', $request->input('id'))->first();
            if($dataDB == null){
                Session::flash('error', 'Data tidak ditemukan!');
                return back();
            }
            $dataDB->status_id = 2;
            $dataDB->save();

            //check qty
            foreach ($dataDB->purchase_order_details as $detail){
                $prDetail = PurchaseRequestDetail::where('item_id', $detail->item_id)->where('header_id',$dataDB->reference_id)->first();
                if(!empty($prDetail)){
                    if($prDetail->qty_po > 0){
                        $prDetail->qty_po = $prDetail->qty_po - $detail->qty;
                        $prDetail->save();
                    }
                }
            }

            //check on all pr details and update PR
            $prData = PurchaseRequestHeader::where('id', $dataDB->reference_id)->first();
            $prData->is_po = 0;
            $prData->save();

            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json('BAD REQUEST!', 200);
        }
    }

    public function approve(Request $request){
        try{
            if(!$request->filled('password')){
                DB::rollBack();
                Session::flash('error', 'Passowrd wajib diisi!');
                return redirect()->route('admin.purchase_order.index');
            }

            //check password first
            $config = Configuration::where('key', 'spv_password')->first();
            if(!Hash::check($request->input('password'), $config->value)){
                DB::rollBack();
                Session::flash('error', 'Password Supervisor Salah!');
                return redirect()->route('admin.purchase_order.index');
            }

            $admin = Auth::guard('admin')->user();
            $ids = $request->input('ids');
            if(empty($ids)){
                Session::flash('error', 'Tidak Ada Data yang Dipilih');
                return redirect()->route('admin.purchase_order.index');
            }
            foreach ($ids as $id) {
                $dataDB = PurchaseOrderHeader::where('id', $id)->first();
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
                    foreach ($dataDB->purchase_order_details as $detail){
                        $prDetail = PurchaseRequestDetail::where('item_id', $detail->item_id)->where('header_id',$dataDB->reference_id)->first();
                        if(!empty($prDetail)){
                            $prDetail->qty_po = $prDetail->qty_po + $detail->qty;
                            $prDetail->save();
                        }

                        //check dan input harga terbaru barang
                        $itemDB = Item::where('id', $detail->item_id)->first();
                        if(!empty($itemDB)){
                            $itemDB->cost_price = $detail->price;
                            $itemDB->save();
                        }
                    }

                    //check on all pr details and update PR
                    $prData = PurchaseRequestHeader::where('id', $dataDB->reference_id)->first();
                    $donePrDtCount = PurchaseRequestDetail::where('header_id', $dataDB->reference_id)
                        ->whereRaw('purchase_request_details.qty = purchase_request_details.qty_po')
                        ->count();
                    $prDtCount = PurchaseRequestDetail::where('header_id', $dataDB->reference_id)->count();
                    if($donePrDtCount == $prDtCount){
                        $prData->is_po = 1;
                        $prData->save();
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
                }
            }

            if(empty($successString)){
                Session::flash('error', 'Gagal approve data Purchase Order '. $failedString);
            }
            else{
                Session::flash('success', 'Sukses approve data Purchase Order '. $successString);
            }
            return redirect()->route('admin.purchase_order.index');
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

        return view('admin.report.report_po_filter')->with($data);
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

            $headers = PurchaseOrderHeader::where('status_id', '!=' , 2)
                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->orderby('code')->get();

            $user = Auth::guard('admin')->user();
            $branch = Branch::where('id', $user->branch_id)->first();

            $data = [
                'result' => $headers,
                'type' => $type,
                'branch' => $branch,
                'start_date' => $startDate->locale('id_ID')->toDateTimeString(),
                'end_date' => $endDate->locale('id_ID')->toDateTimeString(),
            ];
            return view('admin.purchase_order.report')->with($data);
        }
        catch (\Exception $ex){
            Session::flash('error', $ex);
            return back();
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\InvoiceManualCustomDetail;
use App\Models\InvoiceManualCustomHeader;
use App\Models\Partner;
use App\Models\Warehouse;
use App\Transformer\InvoiceManualCustomTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class InvoiceManualCustomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function selectAr(Request $request){
        $user = Auth::guard('admin')->user();

        $term = trim($request->search);
        $employeeId = trim($request->employeeId);
        $partnerId = trim($request->partnerId);

        $searchDatas = DB::table('invoice_manual_custom_headers')
            ->select('id','code','description','grandtotal')
            ->where(function($q) use ($term){
                $q->Where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('description', 'LIKE', '%'. $term. '%');
            });

            if(!empty($employeeId) && empty($partnerId)){
                $searchDatas = $searchDatas->where('employee_id', $employeeId);
            }
            else{
                $searchDatas = $searchDatas->where('partner_id', $partnerId);
            }

            $searchDatas = $searchDatas->where('status_id', 13)
            ->where('branch_id', $user->branch_id)
            ->orderByDesc('id')
            ->get();

        $formatted_tags = [];

        foreach ($searchDatas as $searchData) {
//            $anyArDetail = ArDetail1::where('sales_invoice_id', $searchData->id)->first();

            $sumArDetail = DB::table('ar_detail1s')
                ->join('ar_headers', 'ar_headers.id', '=', 'ar_detail1s.header_id')
                ->join('ar_detail2s', 'ar_detail2s.header_id', '=', 'ar_detail1s.header_id')
                ->select(
                    'ar_headers.status_id as status_id',
                    'ar_detail1s.invoice_id as invoice_id',
                    'ar_detail2s.debit as debit',
                )
                ->where('ar_headers.status_id', 13)
                ->where('ar_detail1s.invoice_id', $searchData->id)
                ->sum('ar_detail2s.debit');

            if($sumArDetail != $searchData->grandtotal){
                $formatted_tags[] = ['id' => $searchData->id, 'text' => $searchData->code];
            }
        }

        return Response::json($formatted_tags);
    }
    public function getTotalforAr(Request $request){
        $dataId = trim($request->salesInv_id);
        $returnData = null;
        if(!empty($dataId)){
            $data = InvoiceManualCustomHeader::where('id',  $dataId)
                ->first();
            $amount = $data->grandtotal;
            $sumArDetail = DB::table('ar_detail1s')
                ->join('ar_headers', 'ar_headers.id', '=', 'ar_detail1s.header_id')
                ->join('ar_detail2s', 'ar_detail2s.header_id', '=', 'ar_detail1s.header_id')
                ->select(
                    'ar_headers.status_id as status_id',
                    'ar_detail1s.invoice_id as invoice_id',
                    'ar_detail2s.debit as debit',
                )
                ->where('ar_headers.status_id', 13)
                ->where('ar_detail1s.invoice_id', $dataId)
                ->sum('ar_detail2s.debit');

            //check if any approved ar


            $returnData = [
                'invId' => $data->id,
                'invCode' => $data->code,
                'amount' => $amount - $sumArDetail,
                'desc' => $data->description,
            ];
        }
        return Response::json($returnData);
    }
    public function select(Request $request){

        $term = trim($request->search);

        $searchDatas = DB::table('invoice_manual_custom_headers')
            ->select('id','code','description')
            ->where(function($q) use ($term){
                $q->Where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('description', 'LIKE', '%'. $term. '%');
            })
            ->where('status_id', 13)
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
        $datas = InvoiceManualCustomHeader::where('status_id', '!=', 2)
            ->where('branch_id', $user->branch_id)
            ->orderByDesc('id');

        return DataTables::of($datas)
            ->setTransformer(new InvoiceManualCustomTransformer)
            ->make(true);
    }

    public function index(){
        return view('admin.invoice_manual_custom.index');
    }

    public function create(){

        $user = Auth::guard('admin')->user();
        if($user->can('branch.scope_all') == 1){
            $branches = Branch::all();
        }
        else{
            $branches = Branch::find($user->branch_id);
        }

        $data = [
            'branches'      => $branches,
        ];
        return view('admin.invoice_manual_custom.create')->with($data);
    }

    public function store(Request $request){
        try{
//            dd($request);
            $itemNames = $request->input('itemNames');
            $qty = $request->input('qty');
            $prices = $request->input('prices');
            $totals = $request->input('totals');
            $disc1Values = $request->input('disc1_values');
            $subtotals = $request->input('subtotals');
            //dd($itemIds, $qty, $unitIds);

//            if(count($itemIds) > 0){
//                if(count($itemIds) != count($qty) && count($itemIds)  != count($unitIds) ){
//                    return back()->withErrors("Detail Item tidak lengkap")->withInput($request->all());
//                }
//            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);
            $data = new InvoiceManualCustomHeader();
            $data->code = AutoNumberGenerator::generateAutonumberTrx('INVM');
            $data->branch_id = $userData->branch_id;
//            $data->required_date = Carbon::parse($request->input('required_date'))->toDateTimeString();
            $data->customer_name = $request->input('customer_name') ?? "";
            $data->warehouse_id = $request->input('warehouse_id') ?? null;
            $data->description = $request->input('description') ?? "";
            $data->subtotal = Utilities::toFloat($request->input('subtotal'));
            $data->disc_value = Utilities::toFloat($request->input('disc1_value'));
            $data->ppn_percent = Utilities::toFloat($request->input('ppn_percent'));
            $data->ppn_value = Utilities::toFloat($request->input('ppn_value'));
            $data->grandtotal = Utilities::toFloat($request->input('grandtotal'));
            $data->status_id = 1;
            $data->created_by = $user->id;

            $data->employee_name = "";
            $data->partner_number = "";
            $data->partner_alias = "";
            $data->customer_type = "";
            if(!empty($request->input('warehouse_id'))){
                $data->warehouse_id = $request->input('warehouse_id');
                $data->warehouse_name = Warehouse::find($request->input('warehouse_id'))->name;
            }
            if($request->filled('employee')){
                $employeeId = $request->input('employee');
                $employeeDb = Employee::where('id', $employeeId)->first();
                $data->customer_type = "KARYAWAN";
                $data->employee_id = $employeeId;
                $data->employee_name = $employeeDb->name;
            }
            else{
                $partnerId = $request->input('partner');
                $partnerDb = Partner::where('id', $partnerId)->first();
                $data->customer_type = "TERAPIS";
                $data->partner_id = $partnerId;
                $data->partner_number = $partnerDb->number;
                $data->partner_alias = $partnerDb->alias;
            }

            $data->save();

//            AutoNumberGenerator::nextAutonumber('INVM');

            $ct = 0;
            foreach ($itemNames as $itemName){

                $dataDetail = new InvoiceManualCustomDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->item_name = $itemName;
                $dataDetail->qty = $qty[$ct]!= null ? Utilities::toFloat($qty[$ct]) : 0;
                $dataDetail->price = $prices[$ct]!= null ? Utilities::toFloat($prices[$ct]) : 0;
                $dataDetail->total = $totals[$ct]!= null ? Utilities::toFloat($totals[$ct]) : 0;
                $dataDetail->disc_value = $disc1Values[$ct]!= null ? Utilities::toFloat($disc1Values[$ct]) : 0;
                $dataDetail->ppn_percent = 0;
                $dataDetail->ppn_value = 0;
                $dataDetail->subtotal = $subtotals[$ct]!= null ? Utilities::toFloat($subtotals[$ct]) : 0;
                $dataDetail->unit_id = null;
                $dataDetail->qty_unit = 1;
                $dataDetail->created_by = $user->id;
                $dataDetail->save();

                $ct++;
            }

            Session::flash('success', 'Sukses membuat data Invoice Manual baru!');
            return redirect()->route('admin.invoice_manual.index');
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

        $dataDB = InvoiceManualCustomHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];

        foreach ($dataDB->invoice_manual_custom_details as $dataDetail){
            $vueData = [
                "itemName"    => $dataDetail->item_name,
                "qty"       => $dataDetail->qty,
                "price"       => $dataDetail->price,
                "disc1Value"       => $dataDetail->disc_value,
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

        return view('admin.invoice_manual_custom.edit')->with($data);
    }

    public function update(Request $request){
        try{

            $itemNames = $request->input('itemNames');
            $qty = $request->input('qty');
            $prices = $request->input('prices');
            $totals = $request->input('totals');
            $disc1Values = $request->input('disc1_values');
            $subtotals = $request->input('subtotals');

            if(count($itemNames) > 0){
                if(count($itemNames) != count($qty) && count($itemNames)  != count($prices) ){
                    return back()->withErrors("Detail Item tidak lengkap")->withInput($request->all());
                }
            }

            $user = Auth::guard('admin')->user();
            $data = InvoiceManualCustomHeader::where('id', $request->input('id'))->first();
            if($data == null){
                Session::flash('error', 'Data tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $data->branch_id = $user->branch_id;
            $data->customer_name = $request->input('customer_name') ?? "";
            $data->warehouse_id = $request->input('warehouse_id') ?? null;
            $data->description = $request->input('description') ?? "";
            $data->subtotal = Utilities::toFloat($request->input('subtotal'));
            $data->disc_value = Utilities::toFloat($request->input('disc1_value'));
            $data->ppn_percent = Utilities::toFloat($request->input('ppn_percent'));
            $data->ppn_value = Utilities::toFloat($request->input('ppn_value'));
            $data->grandtotal = Utilities::toFloat($request->input('grandtotal'));
            $data->updated_by = $user->id;

            $data->employee_name = "";
            $data->partner_number = "";
            $data->partner_alias = "";
            if(!empty($request->input('warehouse_id'))){
                $data->warehouse_id = $request->input('warehouse_id');
                $data->warehouse_name = Warehouse::find($request->input('warehouse_id'))->name;
            }
            if($request->filled('employee')){
                $employeeId = $request->input('employee');
                $employeeDb = Employee::where('id', $employeeId)->first();
                $data->employee_id = $employeeId;
                $data->employee_name = $employeeDb->name;
            }
            else{
                $partnerId = $request->input('partner');
                $partnerDb = Partner::where('id', $partnerId)->first();
                $data->partner_id = $partnerId;
                $data->partner_number = $partnerDb->number;
                $data->partner_alias = $partnerDb->alias;
            }


            $data->save();

            $ct = 0;
            $oldData = InvoiceManualCustomDetail::where('header_id', $request->input('id'))->get();
            if(count($oldData) > 0){
                foreach($oldData as $oldDetaildata){
                    $oldDetaildata->delete();
                }
            }
            foreach ($itemNames as $itemName){

                $dataDetail = new InvoiceManualCustomDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->item_name = $itemName;
                $dataDetail->qty = $qty[$ct]!= null ? Utilities::toFloat($qty[$ct]) : 0;
                $dataDetail->price = $prices[$ct]!= null ? Utilities::toFloat($prices[$ct]) : 0;
                $dataDetail->total = $totals[$ct]!= null ? Utilities::toFloat($totals[$ct]) : 0;
                $dataDetail->disc_value = $disc1Values[$ct]!= null ? Utilities::toFloat($disc1Values[$ct]) : 0;
                $dataDetail->ppn_percent = 0;
                $dataDetail->ppn_value = 0;
                $dataDetail->subtotal = $subtotals[$ct]!= null ? Utilities::toFloat($subtotals[$ct]) : 0;
                $dataDetail->unit_id = null;
                $dataDetail->qty_unit = 1;
                $dataDetail->created_by = $user->id;
                $dataDetail->save();

                $ct++;
            }

            Session::flash('success', 'Sukses mengubah data Invoice Manual!');
            return redirect()->route('admin.invoice_manual.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function show($id, Request $request){
        $user = Auth::guard('admin')->user();
        if($user->can('branch.scope_all') == 1){
            $branches = Branch::all();
        }
        else{
            $branches = Branch::find($user->branch_id);
        }

        $dataDB = InvoiceManualCustomHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];

        foreach ($dataDB->invoice_manual_custom_details as $dataDetail){
            $vueData = [
                "itemName"    => $dataDetail->item_name,
                "qty"       => $dataDetail->qty,
                "price"       => $dataDetail->price,
                "disc1Value"       => $dataDetail->disc_value,
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
        $type = trim($request->type);
        if(!empty($type)){
            return view('admin.invoice_manual_custom.print')->with($data);
        }

        return view('admin.invoice_manual_custom.show')->with($data);
    }

    public function delete(Request $request){
        try{
            $warehouse = InvoiceManualCustomHeader::where('id', $request->input('id'))->first();
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
//            dd($request);
            $ids = $request->input('ids');
            if(empty($ids)){
                Session::flash('error', 'Tidak Ada Data yang Dipilih');
                return redirect()->route('admin.invoice_manual.index');
            }
            foreach ($ids as $id) {
                $dataDB = InvoiceManualCustomHeader::where('id', $id)->first();
                if($dataDB == null){
                    Session::flash('error', 'Data tidak ditemukan!');
                    return back();
                }
                $dataDB->status_id = 13;
                $dataDB->save();

                //Update Stock
                // Utilities::updateStock($userData->branch_id,'min', $qty[$ct], $itemId,$data->warehouse_id,$data->code,'Invoice Manual Custome',);

            }

            Session::flash('success', 'Sukses approve data Invoice Manual!');
            return redirect()->route('admin.invoice_manual.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back();
        }
    }
}

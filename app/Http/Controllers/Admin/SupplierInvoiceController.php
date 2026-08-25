<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\ApDetail1;
use App\Models\Branch;
use App\Models\Configuration;
use App\Models\GoodReceiveHeader;
use App\Models\PurchaseOrderHeader;
use App\Models\SupplierInvoiceDetail1;
use App\Models\SupplierInvoiceDetail2;
use App\Models\SupplierInvoiceHeader;
use App\Transformer\SupplierInvoiceTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class SupplierInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function select(Request $request){
        $user = Auth::guard('admin')->user();

        $term = trim($request->search);

        $searchDatas = DB::table('supplier_invoice_headers')
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
    public function selectAp(Request $request){
        $user = Auth::guard('admin')->user();

        $term = trim($request->search);
        $supplierId = trim($request->supplierId);

        $searchDatas = DB::table('supplier_invoice_headers')
            ->select('id','code','description')
            ->where(function($q) use ($term){
                $q->Where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('description', 'LIKE', '%'. $term. '%');
            })
            ->where('supplier_id', $supplierId)
            ->where('status_id', 13)
            ->where('branch_id', $user->branch_id)
            ->orderByDesc('id')
            ->get();

        $formatted_tags = [];

        foreach ($searchDatas as $searchData) {
            $anyArDetail = ApDetail1::leftJoin('ap_headers', 'ap_detail1s.header_id', '=', 'ap_headers.id')
            ->where('ap_detail1s.supplier_invoice_id', $searchData->id)
            ->where('ap_headers.status_id', '!=', 2)
            ->first();

            if(empty($anyArDetail)){
                $formatted_tags[] = ['id' => $searchData->id, 'text' => $searchData->code];
            }
        }

        return Response::json($formatted_tags);
    }
    public function getTotalforAp(Request $request){
        $grId = trim($request->suppInv_id);
        $returnData = null;
        if(!empty($grId)){
            $data = SupplierInvoiceHeader::where('id',  $grId)
                ->first();
            $amount = SupplierInvoiceDetail1::where('header_id', $grId)->sum('total');
            // $amount = $data->grandtotal;
            $returnData = [
                'suppInvId' => $data->id,
                'suppInvCode' => $data->code,
                'amount' => $amount,
                'desc' => $data->description,
            ];
        }
        return Response::json($returnData);
    }

    public function getIndex(Request $request){
        $user = Auth::guard('admin')->user();
        // $datas = SupplierInvoiceHeader::with(['supplier', 'status'])
        //     ->where('supplier_invoice_headers.status_id', '!=', 2)
        //     ->where('supplier_invoice_headers.branch_id', $user->branch_id);
            $datas = SupplierInvoiceHeader::select('supplier_invoice_headers.id', 'supplier_invoice_headers.code', 'supplier_invoice_headers.date',
            'supplier_invoice_headers.supplier_id', 'suppliers.name as supplier_name', 'supplier_invoice_headers.description',
            'supplier_invoice_headers.status_id', 'statuses.description as status', 'supplier_invoice_headers.created_at')
            ->leftJoin('suppliers', 'supplier_invoice_headers.supplier_id', '=', 'suppliers.id')
            ->leftJoin('statuses', 'supplier_invoice_headers.status_id', '=', 'statuses.id')
            // ->where('supplier_invoice_headers.status_id', '!=', 2)
            ->where('supplier_invoice_headers.branch_id', $user->branch_id);

        return DataTables::of($datas)
            ->setTransformer(new SupplierInvoiceTransformer)
            ->make(true);
    }

    public function index(){
        return view('admin.supplier_invoice.index');
    }

    public function create(Request $request){
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

        return view('admin.supplier_invoice.create')->with($data);
    }

    public function store(Request $request){
        try{
//            dd($request);
            if($request->input('type') == 'GR') {
                $grIds = $request->input('grIds');
                $grCodes = $request->input('grCodes');
                $total = $request->input('total');
                $noInvs = $request->input('noInvs');
                $descs = $request->input('descs');

                if (count($grIds) > 0) {
                    if (count($grIds) != count($total) && count($grIds) != count($noInvs)) {
                        return back()->withErrors("Detail tidak lengkap")->withInput($request->all());
                    }
                }
            }
            else{
                $coaIds = $request->input('coaIds');
                $coaCodes = $request->input('coaCodes');
                $total = $request->input('total');
                $descs = $request->input('descs');

                if (count($coaIds) > 0) {
                    if (count($coaIds) != count($total) && count($coaIds) != count($descs)) {
                        return back()->withErrors("Detail tidak lengkap")->withInput($request->all());
                    }
                }
            }

            $user = Auth::guard('admin')->user();
            $data = new SupplierInvoiceHeader();
            $data->code = AutoNumberGenerator::generateAutonumberTrx('SI');
            $data->branch_id = $user->branch_id;
            $data->supplier_id = $request->input('supplier_id');
            $data->date = Carbon::parse($request->input('date'))->toDateTimeString();
            $data->top = Utilities::toFloat($request->input('top'));
            $data->type = $request->input('type');
            $data->description = $request->input('description') ?? "";
            $data->grandtotal = Utilities::toFloat($request->input('grandtotal'));
            $data->status_id = 1;
            $data->created_by = $user->id;
            $data->save();

//            AutoNumberGenerator::nextAutonumber('SI');

            $ct = 0;
            if($request->input('type') == 'GR'){
                foreach ($grIds as $itemId){
                    $dataDetail = new SupplierInvoiceDetail1();
                    $dataDetail->header_code = $data->code;
                    $dataDetail->header_id = $data->id;
                    $dataDetail->gr_id = $itemId;
                    $dataDetail->gr_code = $grCodes[$ct] ?? "";
                    $dataDetail->total = $total[$ct]!= null ? Utilities::toFloat($total[$ct]) : 1;
                    $dataDetail->no_invoice = $noInvs[$ct] ?? "";
                    $dataDetail->description = $descs[$ct] ?? "";
                    $dataDetail->save();

                    $ct++;
                }
            }
            else{
                $coaIds = $request->input('coaIds');
                $coaCodes = $request->input('coaCodes');
                $total = $request->input('total');
                $descs = $request->input('descs');

                foreach ($coaIds as $itemId){
                    $dataDetail = new SupplierInvoiceDetail2();
                    $dataDetail->header_code = $data->code;
                    $dataDetail->header_id = $data->id;
                    $dataDetail->coa_id = $itemId;
                    $dataDetail->coa_code = $coaCodes[$ct] ?? "";
                    $dataDetail->total = $total[$ct]!= null ? Utilities::toFloat($total[$ct]) : 1;
                    $dataDetail->description = $descs[$ct] ?? "";
                    $dataDetail->save();

                    $ct++;
                }
            }

            Session::flash('success', 'Sukses membuat data Supplier Invoice baru!');
            return redirect()->route('admin.supplier_invoice.index');
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

        $dataDB = SupplierInvoiceHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];
        $detailData2 = [];
        if($dataDB->type == "GR"){
            foreach ($dataDB->supplier_invoice_detail1s as $dataDetail){
                $grDB = GoodReceiveHeader::where('id', $dataDetail->gr_id)->first();
                $vueData = [
                    "grId"    => $dataDetail->gr_id,
                    "poCode"    => empty($grDB) ? "-" : $grDB->po_code,
                    "grCode"    => $dataDetail->gr_code,
                    "qty"       => $dataDetail->qty,
                    "noInv"     => $dataDetail->no_invoice,
                    "total"     => $dataDetail->total,
                    "desc"      => $dataDetail->description,
                ];
                array_push($detailData, $vueData);
            }
        }
        else{
            foreach ($dataDB->supplier_invoice_detail2s as $dataDetail){
                $vueData = [
                    "coaId"     => $dataDetail->coa_id,
                    "coaCode"   => $dataDetail->coa_code,
                    "total"     => $dataDetail->total,
                    "desc"      => $dataDetail->description,
                ];
                array_push($detailData2, $vueData);
            }
        }

        $vueData = [
            'itemList' => $detailData,
            'itemList2' => $detailData2,
        ];

        $data = [
            'branches'  => $branches,
            'data'      => $dataDB,
            'vueData'      => $vueData,
        ];

        return view('admin.supplier_invoice.edit')->with($data);
    }

    public function update(Request $request){
        try{
            if($request->input('type') == 'GR') {
                $grIds = $request->input('grIds');
                $grCodes = $request->input('grCodes');
                $total = $request->input('total');
                $noInvs = $request->input('noInvs');
                $descs = $request->input('descs');

                if (count($grIds) > 0) {
                    if (count($grIds) != count($total) && count($grIds) != count($noInvs)) {
                        return back()->withErrors("Detail tidak lengkap")->withInput($request->all());
                    }
                }
            }
            else{
                $coaIds = $request->input('coaIds');
                $coaCodes = $request->input('coaCodes');
                $total = $request->input('total');
                $descs = $request->input('descs');

                if (count($coaIds) > 0) {
                    if (count($coaIds) != count($total) && count($coaIds) != count($descs)) {
                        return back()->withErrors("Detail tidak lengkap")->withInput($request->all());
                    }
                }
            }

            $user = Auth::guard('admin')->user();
            $data = SupplierInvoiceHeader::where('id', $request->input('id'))->first();
            if($data == null){
                Session::flash('error', 'Data tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $data->branch_id = $user->branch_id;
            $data->date = Carbon::parse($request->input('date'))->toDateTimeString();
            $data->supplier_id = $request->input('supplier_id');
            $data->top = Utilities::toFloat($request->input('top'));
            $data->type = $request->input('type');
            $data->description = $request->input('description') ?? "";
            $data->grandtotal = Utilities::toFloat($request->input('grandtotal'));
            $data->updated_by = $user->id;
            $data->save();

            $oldData = SupplierInvoiceDetail1::where('header_id', $request->input('id'))->get();
            if(count($oldData) > 0){
                foreach($oldData as $oldDetaildata){
                    $oldDetaildata->delete();
                }
            }
            $oldData2 = SupplierInvoiceDetail2::where('header_id', $request->input('id'))->get();
            if(count($oldData2) > 0){
                foreach($oldData2 as $oldDetaildata){
                    $oldDetaildata->delete();
                }
            }
            $ct = 0;
            if($request->input('type') == 'GR'){
                foreach ($grIds as $itemId){
                    $dataDetail = new SupplierInvoiceDetail1();
                    $dataDetail->header_code = $data->code;
                    $dataDetail->header_id = $data->id;
                    $dataDetail->gr_id = $itemId;
                    $dataDetail->gr_code = $grCodes[$ct] ?? "";
                    $dataDetail->total = $total[$ct] != null ? Utilities::toFloat($total[$ct]) : 1;
                    $dataDetail->no_invoice = $noInvs[$ct] ?? "";
                    $dataDetail->description = $descs[$ct] ?? "";
                    $dataDetail->save();

                    $ct++;
                }
            }
            else{
                $coaIds = $request->input('coaIds');
                $coaCodes = $request->input('coaCodes');
                $total = $request->input('total');
                $descs = $request->input('descs');

                foreach ($coaIds as $itemId){
                    $dataDetail = new SupplierInvoiceDetail2();
                    $dataDetail->header_code = $data->code;
                    $dataDetail->header_id = $data->id;
                    $dataDetail->coa_id = $itemId;
                    $dataDetail->coa_code = $coaCodes[$ct] ?? "";
                    $dataDetail->total = $total[$ct]!= null ? Utilities::toFloat($total[$ct]) : 1;
                    $dataDetail->description = $descs[$ct] ?? "";
                    $dataDetail->save();

                    $ct++;
                }
            }

            Session::flash('success', 'Sukses mengubah data Supplier Invoice!');
            return redirect()->route('admin.supplier_invoice.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function show($id, Request $request){
        $user = Auth::guard('admin')->user();
        $branches = Branch::find($user->branch_id);

        $dataDB = SupplierInvoiceHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];
        $detailData2 = [];
        $ppnPercent = 0;
        $grandtotal = 0;
        if($dataDB->type == "GR"){
            foreach ($dataDB->supplier_invoice_detail1s as $dataDetail) {
                $grDB    = GoodReceiveHeader::where('id', $dataDetail->gr_id)->first();
                $poDB = PurchaseOrderHeader::where('id', $grDB->po_id)->first();
                $ppnPercent = $poDB->ppn_percent;
                $vueData = [
                    "grId"   => $dataDetail->gr_id,
                    "poCode" => empty($grDB) ? "-" : $grDB->po_code,
                    "grCode" => $dataDetail->gr_code,
                    "qty"    => $dataDetail->qty,
                    "noInv"  => $dataDetail->no_invoice,
                    "total"  => $dataDetail->total,
                    "desc"   => $dataDetail->description,
                ];
                array_push($detailData, $vueData);
            }

        }
        else{
            foreach ($dataDB->supplier_invoice_detail2s as $dataDetail){
                $vueData = [
                    "coaId"     => $dataDetail->coa_id,
                    "coaCode"   => $dataDetail->coa_code,
                    "total"     => $dataDetail->total,
                    "desc"      => $dataDetail->description,
                ];
                array_push($detailData2, $vueData);
            }
        }

        $vueData = [
            'itemList' => $detailData,
            'itemList2' => $detailData2,
        ];

        $createdBy = AdminUser::where('id', $dataDB->created_by)->first();
        $data = [
            'branches'  => $branches,
            'data'      => $dataDB,
            'vueData'      => $vueData,
            'itemList'      => $detailData,
            'itemList2'      => $detailData2,
            'createdBy' => $createdBy != null ? $createdBy->name : "-",
            'ppn_percent' => $ppnPercent,
            'ppn_value' => (double)(($ppnPercent / 100) * $grandtotal),
            'grandtotal' => $dataDB->grandtotal + $grandtotal,
        ];
        $type = trim($request->type);
        if(!empty($type)){
            return view('admin.supplier_invoice.print')->with($data);
        }


        return view('admin.supplier_invoice.show')->with($data);
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

            $warehouse = SupplierInvoiceHeader::where('id', $request->input('id'))->first();
            if($warehouse == null){
                return Response::json('Data tidak ditemukan!', 200);
            }
            $details = SupplierInvoiceDetail1::where('header_id', $request->input('id'))->get();
            foreach($details as $detail){
                $dataGr = GoodReceiveHeader::where('id', $detail->gr_id)->first();
                $dataGr->is_close = 0;
                $dataGr->save();
            }

            $warehouse->status_id = 2;
            $warehouse->save();


            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json('BAD REQUEST!', 200);
        }
    }

    public function approve(Request $request){
        try{
            if(!$request->filled('password')){
                Session::flash('error', 'Password wajib diisi!');
                return redirect()->route('admin.supplier_invoice.index');
            }

            //check password first
            $config = Configuration::where('key', 'spv_password')->first();
            if(!Hash::check($request->input('password'), $config->value)){
                Session::flash('error', 'Password Supervisor Salah!');
                return redirect()->route('admin.supplier_invoice.index');
            }
//            dd($request);
            $admin = Auth::guard('admin')->user();
            $ids = $request->input('ids');
            if(empty($ids)){
                Session::flash('error', 'Tidak Ada Data yang Dipilih');
                return redirect()->route('admin.supplier_invoice.index');
            }
            foreach ($ids as $id) {
                $dataDB = SupplierInvoiceHeader::where('id', $id)->first();
                if($dataDB == null){
                    Session::flash('error', 'Data tidak ditemukan!');
                    return back();
                }
                $dataDB->is_approve = 1;
                $dataDB->status_id = 13;
                $dataDB->updated_by = $admin->id;
                $dataDB->save();

                foreach ($dataDB->supplier_invoice_detail1s as $dataDetail){
                    $dataGR = GoodReceiveHeader::where('id', $dataDetail->gr_id)->first();
                    if(!empty($dataGR)){
                        $dataGR->is_close = 1;
                        $dataGR->save();
                    }
                }
            }

            Session::flash('success', 'Sukses approve data Supplier Invoice!');
            return redirect()->route('admin.supplier_invoice.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back();
        }
    }
}

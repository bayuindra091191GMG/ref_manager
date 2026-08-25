<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\Branch;
use App\Models\Configuration;
use App\Models\InternalRequestHeader;
use App\Models\ItemUnit;
use App\Models\PurchaseRequestDetail;
use App\Models\PurchaseRequestHeader;
use App\Transformer\PurchaseRequestTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class PurchaseRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function select(Request $request){
        $user = Auth::guard('admin')->user();

        $term = trim($request->search);

        $searchDatas = DB::table('purchase_request_headers')
            ->select('id','code','description')
            ->where('branch_id', $user->branch_id)
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
        $datas = PurchaseRequestHeader::with(['status', 'supplier', 'warehouse'])
            // ->where('status_id', '!=', 2)
            ->where('branch_id', $user->branch_id)
            ->orderByDesc('id');

        return DataTables::of($datas)
            ->setTransformer(new PurchaseRequestTransformer)
            ->make(true);
    }

    public function index(){
        return view('admin.purchase_request.index');
    }

    public function create(Request $request){
        $user = Auth::guard('admin')->user();
        if($user->can('branch.scope_all') == 1){
            $branches = Branch::all();
        }
        else{
            $branches = Branch::find($user->branch_id);
        }

        $referenceId = trim($request->reference_id);
        $isInternal = 0;
        $referenceCode = "";
        $detailData = [];
        if(!empty($referenceId)){
            $internalData = InternalRequestHeader::where('id', $referenceId)->first();

            $isInternal = 1;
            $referenceCode = $internalData->code;
            foreach ($internalData->internal_request_details as $dataDetail){
                $data = [
                    "itemId"    => $dataDetail->item_id,
                    "qty"       => $dataDetail->qty,
                    "unitId"       => $dataDetail->unit_id,
                ];
                array_push($detailData, $data);
            }

        }

        $vueData = [
            'itemList' => $detailData
        ];

        $data = [
            'branches'      => $branches,
            'isInternal'    => $isInternal,
            'referenceId'   => $referenceId,
            'referenceCode' => $referenceCode,
            'vueData'       => $vueData,
        ];

        return view('admin.purchase_request.create')->with($data);
    }

    public function store(Request $request){
        try{
            $itemIds  = $request->input('itemIds', []);
            $qty      = $request->input('qty', []);
            $unitIds  = $request->input('unitIds', []);

            if (count($itemIds) !== count($qty) || count($itemIds) !== count($unitIds)) {
                return back()->withErrors("Detail Item tidak lengkap")->withInput();
            }


            if(!$request->filled('warehouse_id')){
                return back()->withErrors("Gudang belum di pilih")->withInput($request->all());
            }
            // if(!$request->filled('supplier_id')){
            //     return back()->withErrors("SUppplier belum di pilih")->withInput($request->all());
            // }
            DB::beginTransaction();
            $user = Auth::guard('admin')->user();
            $data = new PurchaseRequestHeader();
            $data->code = AutoNumberGenerator::generateAutonumberTrx('PR');
            $data->branch_id = $user->branch_id;
            $data->required_date = Carbon::parse($request->input('required_date'))->toDateTimeString();
            $data->supplier_id = $request->input('supplier_id');
            $data->warehouse_id = $request->input('warehouse_id');
            $data->description = $request->input('description') ?? "";
            $data->status_id = 1;
            $data->is_po = 0;
            $data->created_by = $user->id;
            if(!empty($request->input('reference_id'))){
                $data->reference_id = $request->input('reference_id');
                $data->reference_code = InternalRequestHeader::find($request->input('reference_id'))->code;
            }
            $data->save();

//            AutoNumberGenerator::nextAutonumber('PR');

            $ct = 0;
            foreach ($itemIds as $itemId){
                $itemUnit = ItemUnit::where('item_id', $itemId)->where('unit_id', $unitIds[$ct])->first();
                $dataDetail = new PurchaseRequestDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->item_id = $itemId;
                $dataDetail->qty = $qty[$ct] == null ? 1 : Utilities::toFloat($qty[$ct]);
                $dataDetail->unit_id = $unitIds[$ct];
                $dataDetail->qty_unit = $itemUnit != null ? $itemUnit->qty : 1;
                $dataDetail->qty_po = 0;
                $dataDetail->created_by = $user->id;
                $dataDetail->save();

                $ct++;
            }

            DB::commit();
            Session::flash('success', 'Sukses membuat data purchase request baru!');
            return redirect()->route('admin.purchase_request.index');
        }
        catch (\Exception $ex){
            DB::rollBack();
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

        $dataDB = PurchaseRequestHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];

        foreach ($dataDB->purchase_request_details as $dataDetail){
            $vueData = [
                "id"        => $dataDetail->id,
                "itemId"    => $dataDetail->item_id,
                "qty"       => $dataDetail->qty,
                "unitId"       => $dataDetail->unit_id,
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

        return view('admin.purchase_request.edit')->with($data);
    }

    public function update(Request $request){
        try{

            $itemIds  = $request->input('itemIds', []);
            $qty      = $request->input('qty', []);
            $unitIds  = $request->input('unitIds', []);

            if (count($itemIds) !== count($qty) || count($itemIds) !== count($unitIds)) {
                return back()->withErrors("Detail Item tidak lengkap")->withInput();
            }

            if(!$request->filled('warehouse_id')){
                return back()->withErrors("Gudang belum di pilih")->withInput($request->all());
            }

            DB::beginTransaction();
            $user = Auth::guard('admin')->user();
            $data = PurchaseRequestHeader::where('id', $request->input('id'))->first();
            if($data == null){
                Session::flash('error', 'Data tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $data->branch_id = $user->branch_id;
            $data->required_date = Carbon::parse($request->input('required_date'))->toDateTimeString();
            $data->supplier_id = $request->input('supplier_id');
            $data->warehouse_id = $request->input('warehouse_id');
            $data->description = $request->input('description') ?? "";
            $data->updated_by = $user->id;
            if(!empty($request->input('reference_id'))){
                $data->reference_id = $request->input('reference_id');
                $data->reference_code = InternalRequestHeader::find($request->input('reference_id'))->code;
            }
            $data->save();

            $ct = 0;
            $oldData = PurchaseRequestDetail::where('header_id', $request->input('id'))->get();
            if(count($oldData) > 0){
                foreach($oldData as $oldDetaildata){
                    $oldDetaildata->delete();
                }
            }
            foreach ($itemIds as $itemId){
                $itemUnit = ItemUnit::where('item_id', $itemId)->where('unit_id', $unitIds[$ct])->first();
                $dataDetail = new PurchaseRequestDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->item_id = $itemId;
                $dataDetail->qty = $qty[$ct] == null ? 1 : Utilities::toFloat($qty[$ct]);
                $dataDetail->unit_id = $unitIds[$ct];
                $dataDetail->qty_unit = $itemUnit != null ? $itemUnit->qty : 1;
                $dataDetail->qty_po = 0;
                $dataDetail->created_by = $user->id;
                $dataDetail->save();

                $ct++;
            }

            DB::commit();
            Session::flash('success', 'Sukses mengubah data Purchase Request!');
            return redirect()->route('admin.purchase_request.index');
        }
        catch (\Exception $ex){
            DB::rollBack();
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function show($id, Request $request){
        $user = Auth::guard('admin')->user();
        $branches = Branch::find($user->branch_id);

        $dataDB = PurchaseRequestHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];

        foreach ($dataDB->purchase_request_details as $dataDetail){
            $vueData = [
                "itemId"    => $dataDetail->item_id,
                "itemName"        => $dataDetail->item->name,
                "qty"       => $dataDetail->qty,
                "unitName"        => $dataDetail->unit_material->name,
            ];
            array_push($detailData, $vueData);
        }

        $vueData = [
            'itemList' => $detailData
        ];

        $createdBy = AdminUser::where('id', $dataDB->created_by)->first();
        $data = [
            'branches'  => $branches,
            'data'      => $dataDB,
            'vueData'      => $vueData,
            'itemList'       => $detailData,
            'createdBy' => $createdBy != null ? $createdBy->name : "-",
        ];
        $type = trim($request->type);
        if(!empty($type)){
            return view('admin.purchase_request.print')->with($data);
        }

        return view('admin.purchase_request.show')->with($data);
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

            $warehouse = PurchaseRequestHeader::where('id', $request->input('id'))->first();
            if($warehouse == null){
                return Response::json('Data tidak ditemukan!', 200);
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
                return redirect()->route('admin.purchase_request.index');
            }

            //check password first
            $config = Configuration::where('key', 'spv_password')->first();
            if(!Hash::check($request->input('password'), $config->value)){
                Session::flash('error', 'Password Supervisor Salah!');
                return redirect()->route('admin.purchase_request.index');
            }

            $admin = Auth::guard('admin')->user();
//            dd($request);
            $ids = $request->input('ids');
            if(empty($ids)){
                Session::flash('error', 'Tidak Ada Data yang Dipilih');
                return redirect()->route('admin.purchase_request.index');
            }
            foreach ($ids as $id) {
                $dataDB = PurchaseRequestHeader::where('id', $id)->first();
                if($dataDB == null){
                    Session::flash('error', 'Data tidak ditemukan!');
                    return back();
                }
                $dataDB->is_approve = 1;
                $dataDB->status_id = 13;
                $dataDB->updated_by = $admin->id;
                $dataDB->save();
            }

            Session::flash('success', 'Sukses approve data Purchase Request!');
            return redirect()->route('admin.purchase_request.index');
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

        return view('admin.report.report_pr_filter')->with($data);
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

            $headers = PurchaseRequestHeader::where('status_id', '!=' , 2)
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
            return view('admin.purchase_request.report')->with($data);
        }
        catch (\Exception $ex){
            Session::flash('error', $ex);
            return back();
        }
    }
}

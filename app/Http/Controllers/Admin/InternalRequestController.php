<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Models\Branch;
use App\Models\InternalRequestDetail;
use App\Models\InternalRequestHeader;
use App\Models\ItemUnit;
use App\Transformer\InternalRequestTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class InternalRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function select(Request $request){
        $admin = Auth::guard('admin')->user();
        $term = trim($request->search);

        $searchDatas = DB::table('internal_request_headers')
            ->select('id','code','description')
            ->where('branch_id', '=', $admin->branch_id)
            ->where(function($q) use ($term){
                $q->Where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('description', 'LIKE', '%'. $term. '%');
            })
            ->where('status_id','=',13)
            ->where('is_pr','=',0)
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
        $datas = InternalRequestHeader::where('status_id', '!=', 2)
            ->where('branch_id', $user->branch_id)
            ->orderByDesc('id');

        return DataTables::of($datas)
            ->setTransformer(new InternalRequestTransformer)
            ->make(true);
    }

    public function index(){
        return view('admin.internal_request.index');
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

        return view('admin.internal_request.create')->with($data);
    }

    public function store(Request $request){
        try{
//            dd($request);
            $itemIds = $request->input('itemIds');
            $qty = $request->input('qty');
            $unitIds = $request->input('unitIds');

            if(count($itemIds) > 0){
                if(count($itemIds) != count($qty) && count($itemIds)  != count($unitIds) ){
                    return back()->withErrors("Detail Item tidak lengkap")->withInput($request->all());
                }
            }
            if(!$request->filled('warehouse_id')){
                return back()->withErrors("Harus pilih Gudang")->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $data = new InternalRequestHeader();
            $data->code = AutoNumberGenerator::generateAutonumberTrx('IR');
            $data->branch_id = $user->branch_id;
//            $data->required_date = Carbon::parse($request->input('required_date'))->toDateTimeString();
//            $data->supplier_id = $request->input('supplier_id');
            $data->warehouse_id = $request->input('warehouse_id');
            $data->description = $request->input('description') ?? "";
            $data->status_id = 1;
            $data->is_pr = 0;
            $data->created_by = $user->id;
            $data->save();

//            AutoNumberGenerator::nextAutonumber('IR');

            $ct = 0;
            foreach ($itemIds as $itemId){
                $itemUnit = ItemUnit::where('item_id', $itemId)->where('unit_id', $unitIds[$ct])->first();
                $dataDetail = new InternalRequestDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->item_id = $itemId;
                $dataDetail->qty = $qty[$ct] ?? 1;
                $dataDetail->unit_id = $unitIds[$ct];
                $dataDetail->qty_unit = $itemUnit != null ? $itemUnit->qty : 1;
                $dataDetail->created_by = $user->id;
                $dataDetail->save();

                $ct++;
            }

            Session::flash('success', 'Sukses membuat data transfer barang baru!');
            return redirect()->route('admin.internal_request.index');
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

        $dataDB = InternalRequestHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];

        foreach ($dataDB->internal_request_details as $dataDetail){
            $vueData = [
                "id"    => $dataDetail->id,
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

        return view('admin.internal_request.edit')->with($data);
    }

    public function update(Request $request){
        try{

            $itemIds = $request->input('itemIds');
            $qty = $request->input('qty');
            $unitIds = $request->input('unitIds');

            if(count($itemIds) > 0){
                if(count($itemIds) != count($qty) && count($itemIds)  != count($unitIds) ){
                    return back()->withErrors("Detail Item tidak lengkap")->withInput($request->all());
                }
            }
            if(!$request->filled('warehouse_id')){
                return back()->withErrors("Harus pilih Gudang")->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $data = InternalRequestHeader::where('id', $request->input('id'))->first();
            if($data == null){
                Session::flash('error', 'Data tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $data->branch_id = $user->branch_id;
//            $data->required_date = Carbon::parse($request->input('required_date'))->toDateTimeString();
//            $data->supplier_id = $request->input('supplier_id');
            $data->warehouse_id = $request->input('warehouse_id');
            $data->description = $request->input('description') ?? "";
            $data->updated_by = $user->id;
            $data->save();

            $ct = 0;
            $oldData = InternalRequestDetail::where('header_id', $request->input('id'))->get();
            if(count($oldData) > 0){
                foreach($oldData as $oldDetaildata){
                    $oldDetaildata->delete();
                }
            }
            foreach ($itemIds as $itemId){
                $itemUnit = ItemUnit::where('item_id', $itemId)->where('unit_id', $unitIds[$ct])->first();
                $dataDetail = new InternalRequestDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->item_id = $itemId;
                $dataDetail->qty = $qty[$ct] ?? 1;
                $dataDetail->unit_id = $unitIds[$ct];
                $dataDetail->qty_unit = $itemUnit != null ? $itemUnit->qty : 1;
                $dataDetail->created_by = $user->id;
                $dataDetail->save();

                $ct++;
            }

            Session::flash('success', 'Sukses mengubah data Internal Request!');
            return redirect()->route('admin.internal_request.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function show($id, Request $request){
        $branches = Branch::all();
        $dataDB = InternalRequestHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];

        foreach ($dataDB->internal_request_details as $dataDetail){
            $vueData = [
                "itemId"    => $dataDetail->item_id,
                "itemName"        => $dataDetail->item->name,
                "qty"       => $dataDetail->qty,
                "unitId"       => $dataDetail->unit_id,
                "unitName"       => $dataDetail->unit_material->name,
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
            'itemList'       => $detailData,
        ];
        $type = trim($request->type);
        if(!empty($type)){
            return view('admin.internal_request.print')->with($data);
        }

        return view('admin.internal_request.show')->with($data);
    }

    public function delete(Request $request){
        try{
            $warehouse = InternalRequestHeader::where('id', $request->input('id'))->first();
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
            $admin = Auth::guard('admin')->user();
            if(empty($ids)){
                Session::flash('error', 'Tidak Ada Data yang Dipilih');
                return redirect()->route('admin.internal_request.index');
            }
            $admin = Auth::guard('admin')->user();
            foreach ($ids as $id) {
                $dataDB = InternalRequestHeader::where('id', $id)->first();
                if($dataDB == null){
                    Session::flash('error', 'Data tidak ditemukan!');
                    return back();
                }
                $dataDB->status_id = 13;
                $dataDB->updated_by = $admin->id;
                $dataDB->save();

//                foreach ($dataDB->internal_request_details as $detail) {
//                    // Stock out from origin warehouse
//                    Utilities::updateStock($admin->branch_id,
//                        'sub',
//                        $detail->qty,
//                        $detail->item_id,
//                        $dataDB->warehouse_id,
//                        $dataDB->code,
//                        'Internal Request '.$dataDB->code);
//                }
            }

            Session::flash('success', 'Sukses approve data Internal Request!');
            return redirect()->route('admin.internal_request.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back();
        }
    }
}

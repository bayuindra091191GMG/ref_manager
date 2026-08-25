<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\Branch;
use App\Models\InternalRequestDetail;
use App\Models\InvoiceManualDetail;
use App\Models\InvoiceManualHeader;
use App\Models\ItemUnit;
use App\Transformer\InvoiceManualTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class InvoiceManualController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function select(Request $request){

        $term = trim($request->search);

        $searchDatas = DB::table('invoice_manual_headers')
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
        $datas = InvoiceManualHeader::where('status_id', '!=', 2)
            ->orderByDesc('id');

        return DataTables::of($datas)
            ->setTransformer(new InvoiceManualTransformer)
            ->make(true);
    }

    public function index(){
        return view('admin.invoice_manual.index');
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

        return view('admin.invoice_manual.create')->with($data);
    }

    public function store(Request $request){
        try{
//            dd($request);
            $itemIds = $request->input('itemIds');
            $qty = $request->input('qty');
            $unitIds = $request->input('unitIds');
            //dd($itemIds, $qty, $unitIds);

//            if(count($itemIds) > 0){
//                if(count($itemIds) != count($qty) && count($itemIds)  != count($unitIds) ){
//                    return back()->withErrors("Detail Item tidak lengkap")->withInput($request->all());
//                }
//            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);
            $data = new InvoiceManualHeader();
            $data->code = AutoNumberGenerator::generateAutonumberTrx('INVM');
            $data->branch_id = $userData->branch_id;
//            $data->required_date = Carbon::parse($request->input('required_date'))->toDateTimeString();
            $data->customer_id = $request->input('customer_id') ?? null;
            $data->warehouse_id = $request->input('warehouse_id') ?? null;
            $data->description = $request->input('description') ?? "";
            $data->status_id = 1;
            $data->created_by = $user->id;
            $data->save();

//            AutoNumberGenerator::nextAutonumber('INVM');

            $ct = 0;
            foreach ($itemIds as $itemId){
                //$itemUnit = ItemUnit::where('item_id', $itemId)->where('unit_id', $unitIds[$ct])->first();
                $dataDetail = new InvoiceManualDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->item_id = $itemId;
                $dataDetail->qty = $qty[$ct] ?? 1;
                $dataDetail->unit_id = null;
//                $dataDetail->qty_unit = $itemUnit != null ? $itemUnit->qty : 1;
                $dataDetail->qty_unit = 1;
                $dataDetail->created_by = $user->id;
                $dataDetail->save();

                //Update Stock
                Utilities::updateStock($userData->branch_id,'min', $qty[$ct], $itemId,$data->warehouse_id,$data->code,'Invoice Manual',);

                $ct++;
            }

            Session::flash('success', 'Sukses membuat data transfer barang baru!');
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

        $dataDB = InvoiceManualHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];

        foreach ($dataDB->invoice_manual_details as $dataDetail){
            $vueData = [
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

        return view('admin.invoice_manual.edit')->with($data);
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

            $user = Auth::guard('admin')->user();
            $data = InvoiceManualHeader::where('id', $request->input('id'))->first();
            if($data == null){
                Session::flash('error', 'Data tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $data->branch_id = $user->branch_id;
//            $data->required_date = Carbon::parse($request->input('required_date'))->toDateTimeString();
//            $data->supplier_id = $request->input('supplier_id');
//            $data->warehouse_id = $request->input('warehouse_id');
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
            return redirect()->route('admin.invoice_manual.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function show($id, Request $request){
        $branches = Branch::all();
        $dataDB = InvoiceManualHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];

        foreach ($dataDB->invoice_manual_details as $dataDetail){
            $vueData = [
                "itemId"    => $dataDetail->item_id,
                "itemName"        => $dataDetail->item->name,
                "qty"       => $dataDetail->qty,
                "unitId"       => $dataDetail->unit_id,
//                "unitName"       => $dataDetail->unit_material->name,
                "unitName"       => 'PCS',
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
            return view('admin.invoice_manual.print')->with($data);
        }

        return view('admin.invoice_manual.show')->with($data);
    }

    public function delete(Request $request){
        try{
            $warehouse = InvoiceManualHeader::where('id', $request->input('id'))->first();
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
                $dataDB = InvoiceManualHeader::where('id', $id)->first();
                if($dataDB == null){
                    Session::flash('error', 'Data tidak ditemukan!');
                    return back();
                }
                $dataDB->status_id = 13;
                $dataDB->save();
            }

            Session::flash('success', 'Sukses approve data Internal Request!');
            return redirect()->route('admin.invoice_manual.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back();
        }
    }
}

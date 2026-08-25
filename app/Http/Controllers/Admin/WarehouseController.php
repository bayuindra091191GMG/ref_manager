<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Models\Branch;
use App\Models\Status;
use App\Models\Warehouse;
use App\Transformer\WarehouseTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.warehouse.index');
    }

    public function getIndex(Request $request){
        $user = Auth::guard('admin')->user();
        $warehouses = Warehouse::with('branch')
            ->where('branch_id', $user->branch_id)
            ->where('id', '>', 0);

        return DataTables::of($warehouses)
            ->setTransformer(new WarehouseTransformer)
            ->make(true);
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

        return view('admin.warehouse.create')->with($data);
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'name'    => 'required',
            ],[
                'name.required'         => 'Nama Gudang wajib diisi!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $warehouse = new Warehouse();
            $warehouse->code = AutoNumberGenerator::generateAutonumberMasterData('WH');
            $warehouse->branch_id = $user->branch_id;
            $warehouse->name = $request->input('name');
            $warehouse->description = $request->input('description');
            $warehouse->status_id = 1;
            $warehouse->created_by = $user->id;
            $warehouse->save();

//            AutoNumberGenerator::nextAutonumber('WH');

            Session::flash('success', 'Sukses membuat data gudang baru!');
            return redirect()->route('admin.warehouse.index');
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
        $warehouse = Warehouse::where('id', $id)->first();
        $statuses = Status::where('id', 1)->orWhere('id', 2)->get();
        if($warehouse == null){
            Session::flash('error', 'Warehouse tidak ditemukan!');
            return back();
        }

        $data = [
            'warehouse'     => $warehouse,
            'branches'      => $branches,
            'statuses'      => $statuses,
        ];

        return view('admin.warehouse.edit')->with($data);
    }

    public function update(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'name'    => 'required',
            ],[
                'name.required'       => 'Nama wajib diisi!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $warehouse = Warehouse::where('id', $request->input('id'))->first();
            if($warehouse == null){
                Session::flash('error', 'Gudang tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $warehouse->branch_id = $user->branch_id;
            $warehouse->name = $request->input('name');
            $warehouse->description = $request->input('description');
            $warehouse->status_id = $request->input('status_id');
            $warehouse->updated_by = $user->id;
            $warehouse->save();

            Session::flash('success', 'Sukses mengubah data gudang!');
            return redirect()->route('admin.warehouse.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
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

    public function select(Request $request){
        $term = trim($request->search);

        $user = Auth::guard('admin')->user();
        if($user->can('branch.scope_all') == 1 && empty($user->branch_id)){
            $searchDatas = DB::table('warehouses')
                ->select('id','code','name')
                ->where(function($q) use ($term){
                    $q->Where('code', 'LIKE', '%'. $term. '%')
                        ->orWhere('name', 'LIKE', '%'. $term. '%');
                })
                ->orderBy('id')
                ->get();
        }
        else{
            $searchDatas = DB::table('warehouses')
                ->select('id','code','name')
                ->where(function($q) use ($term){
                    $q->Where('code', 'LIKE', '%'. $term. '%')
                        ->orWhere('name', 'LIKE', '%'. $term. '%');
                })
                ->where('branch_id', $user->branch_id)
                ->orderBy('id')
                ->get();
        }

        $formatted_tags = [];

        foreach ($searchDatas as $searchData) {
            $formatted_tags[] = ['id' => $searchData->id, 'text' => $searchData->name];
        }

        return Response::json($formatted_tags);
    }
}

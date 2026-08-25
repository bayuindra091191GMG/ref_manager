<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Status;
use App\Models\UnitMaterial;
use App\Transformer\UnitMaterialTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class UnitMaterialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.uom.index');
    }

    public function getIndex(Request $request){
        $admin = Auth::guard('admin')->user();
//        $uoms = UnitMaterial::with(['branch', 'status'])
//            ->where('branch_id', '=', $admin->branch_id);
        $uoms = UnitMaterial::with(['branch', 'status'])
            ->where('unit_materials.id', '>', 0);

        return DataTables::of($uoms)
            ->setTransformer(new UnitMaterialTransformer)
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

        return view('admin.uom.create')->with($data);
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'name'    => 'required',
            ],[
                'name.required'         => 'Nama Unit Material wajib diisi!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $uom = new UnitMaterial();
            $uom->branch_id = $user->branch_id;
            $uom->name = $request->input('name');
            $uom->status_id = 1;
            $uom->save();

            Session::flash('success', 'Sukses membuat data gudang baru!');
            return redirect()->route('admin.uom.index');
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
        $uom = UnitMaterial::where('id', $id)->first();
        $statuses = Status::where('id', 1)->orWhere('id', 2)->get();
        if($uom == null){
            Session::flash('error', 'Unit Material tidak ditemukan!');
            return back();
        }

        $data = [
            'uom'           => $uom,
            'branches'      => $branches,
            'statuses'      => $statuses,
        ];

        return view('admin.uom.edit')->with($data);
    }

    public function update(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'name'    => 'required',
            ],[
                'name.required'         => 'Nama Unit Material wajib diisi!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $uom = UnitMaterial::where('id', $request->input('id'))->first();
            if($uom == null){
                Session::flash('error', 'Unit Material tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $uom->branch_id = $user->branch_id;
            $uom->name = $request->input('name');
            $uom->save();

            Session::flash('success', 'Sukses mengubah data Unit Material!');
            return redirect()->route('admin.uom.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function delete(Request $request){
        try{
            $uom = UnitMaterial::where('id', $request->input('id'))->first();
            if($uom == null){
                Session::flash('error', 'Unit Material tidak ditemukan!');
                return back();
            }
            $uom->status_id = 2;
            $uom->save();

            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json('error', 200);
        }
    }

    public function select(Request $request){

        $term = trim($request->search);

        $searchDatas = DB::table('unit_materials')
            ->select('id','name')
            ->where(function($q) use ($term){
                $q->Where('name', 'LIKE', '%'. $term. '%');
            })
            ->orderBy('id')
            ->get();

        $formatted_tags = [];

        foreach ($searchDatas as $searchData) {
            $formatted_tags[] = ['id' => $searchData->id, 'text' => $searchData->name];
        }

        return Response::json($formatted_tags);
    }
}

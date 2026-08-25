<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Models\Branch;
use App\Models\Status;
use App\Models\Supplier;
use App\Transformer\SupplierTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.supplier.index');
    }

    public function getIndex(Request $request){
        $user = Auth::guard('admin')->user();
        $suppliers = Supplier::with('branch')
            ->where('branch_id', $user->branch_id)
            ->where('id', '>', 0);

        return DataTables::of($suppliers)
            ->setTransformer(new SupplierTransformer)
            ->make(true);
    }

    public function create(){
        $branches = Branch::all();

        $data = [
            'branches'      => $branches,
        ];

        return view('admin.supplier.create')->with($data);
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'name'    => 'required',
//                'email' => 'required',
//                'phone' => 'required',
            ],[
                'name.required'         => 'Nama Supplier wajib diisi!',
//                'email.required'         => 'Email Supplier wajib diisi!',
//                'phone.required'        => 'No Telepon wajib dipilih!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $supplier = new Supplier();
            $supplier->code = AutoNumberGenerator::generateAutonumberMasterData('SUP');
            $supplier->branch_id = $user->branch_id;
            $supplier->name = $request->input('name');
            $supplier->description = $request->input('description');
            $supplier->phone = $request->input('phone') ?? '';
            $supplier->email = $request->input('email') ?? '';
            $supplier->address1 = $request->input('address1') ?? "";
            $supplier->address2 = $request->input('address2') ?? "";
            $supplier->status_id = 1;
            $supplier->created_by = $user->id;
            $supplier->save();

//            AutoNumberGenerator::nextAutonumber('WH');

            Session::flash('success', 'Sukses membuat data supplier baru!');
            return redirect()->route('admin.supplier.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function edit($id){
        $branches = Branch::all();
        $supplier = Supplier::where('id', $id)->first();
        $statuses = Status::where('id', 1)->orWhere('id', 2)->get();
        if($supplier == null){
            Session::flash('error', 'Supplier tidak ditemukan!');
            return back();
        }

        $data = [
            'supplier'     => $supplier,
            'branches'      => $branches,
            'statuses'      => $statuses,
        ];

        return view('admin.supplier.edit')->with($data);
    }

    public function update(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'name'    => 'required',
//                'email' => 'required',
//                'phone' => 'required',
            ],[
                'name.required'         => 'Nama Supplier wajib diisi!',
//                'email.required'         => 'Email Supplier wajib diisi!',
//                'phone.required'        => 'No Telepon wajib dipilih!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $supplier = Supplier::where('id', $request->input('id'))->first();
            if($supplier == null){
                Session::flash('error', 'Gudang tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $supplier->branch_id = $user->branch_id;
            $supplier->name = $request->input('name');
            $supplier->description = $request->input('description');
            $supplier->phone = $request->input('phone') ?? '';
            $supplier->email = $request->input('email') ?? '';
            $supplier->address1 = $request->input('address1') ?? "";
            $supplier->address2 = $request->input('address2') ?? "";
            $supplier->status_id = $request->input('status_id');
            $supplier->updated_by = $user->id;
            $supplier->save();

            Session::flash('success', 'Sukses mengubah data supplier!');
            return redirect()->route('admin.supplier.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function delete(Request $request){
        try{
            $supplier = Supplier::where('id', $request->input('id'))->first();
            if($supplier == null){
                Session::flash('error', 'Gudang tidak ditemukan!');
                return back();
            }
            $supplier->status_id = 2;
            $supplier->save();

            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json('error', 200);
        }
    }

    public function select(Request $request){

        $term = trim($request->search);

        $user = Auth::guard('admin')->user();
        if($user->can('branch.scope_all') == 1){
            $searchDatas = DB::table('suppliers')
                ->select('id','code','name', 'branch_id')
                ->where(function($q) use ($term){
                    $q->Where('code', 'LIKE', '%'. $term. '%')
                        ->orWhere('name', 'LIKE', '%'. $term. '%');
                })
                ->orderBy('id')
                ->get();
        }
        else{
            $searchDatas = DB::table('suppliers')
                ->select('id','code','name', 'branch_id')
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

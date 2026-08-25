<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Models\Branch;
use App\Models\ItemCategory;
use App\Transformer\ItemCategoryTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;


class ItemCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function getIndex(Request $request){
        $admin = Auth::guard('admin')->user();
        $categories = ItemCategory::where('status_id', 1)
            ->where('branch_id', '=', $admin->branch_id)
            ->get();
        return DataTables::of($categories)
            ->setTransformer(new ItemCategoryTransformer)
            ->addIndexColumn()
            ->make(true);
    }

    public function select(Request $request){
        $term = trim($request->search);

        $admin = Auth::guard('admin')->user();
        $searchDatas = DB::table('item_categories')
            ->select('id','code','name')
            ->where('branch_id', '=', $admin->branch_id)
            ->where('status_id', '=', 1)
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            })
            ->orderBy('code')
            ->get();

        $formatted_tags = [];

        foreach ($searchDatas as $searchData) {
            $formatted_tags[] = ['id' => $searchData->id, 'text' => $searchData->code. " - " .$searchData->name];
        }

        return Response::json($formatted_tags);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.item_category.index');
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();
        if($user->can('branch.scope_all') == 1){
            $branches = Branch::all();
        }
        else{
            $branches = Branch::find($user->branch_id);
        }

        $data = [
            'branches'     => $branches,
        ];

        return view('admin.item_category.create')->with($data);
    }

    public function store(Request $request)
    {
//        dd($request);
        $validator = Validator::make($request->all(), [
            'name'              => 'required|max:100',
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

        //Create Admin
        $user = Auth::guard('admin')->user();

        $data = new ItemCategory();
        $data->branch_id = $user->branch_id;
        $data->code =  AutoNumberGenerator::generateAutonumberMasterData('IC');
        $data->name = $request->input('name');
        $data->desc = $request->input('description') ?? "";
        $data->coa_stock = $request->input('coa_stock');
        $data->coa_cost = $request->input('coa_cost');
        $data->status_id = 1;

        $data->save();
//        AutoNumberGenerator::nextAutonumber('IC');

        Session::flash('success', 'Sukses membuat Item Kategori baru');
        return redirect()->route('admin.item_category.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $coa = ItemCategory::find($id);
        $user = Auth::guard('admin')->user();
        if($user->can('branch.scope_all') == 1){
            $branches = Branch::all();
        }
        else{
            $branches = Branch::find($user->branch_id);
        }

        $data = [
            'data'      => $coa,
            'branches'  => $branches,
        ];

        return view('admin.item_category.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|max:100',
            'description'
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

        //Edit Admin
        $id = $request->input('id');
        $user = Auth::guard('admin')->user();

        $data = ItemCategory::where('id', $id)->first();
        $data->branch_id = $user->branch_id;
        $data->name = $request->input('name');
        $data->desc = $request->input('description') ?? "";
        $data->coa_stock = $request->input('coa_stock');
        $data->coa_cost = $request->input('coa_cost');
        $data->save();

        Session::flash('success', 'Sukses mengubah Item Kategori!');
        return redirect()->route('admin.item_category.index');
    }

    public function delete(Request $request)
    {
        //
        try {
            $coaId = $request->input('id');
            $coa = ItemCategory::find($coaId);

            if(empty($coa)){
                return Response::json(array('errors' => 'INVALID'));
            }
            $coa->status_id = 2;
            $coa->save();

        // $adminUser->delete();

            Session::flash('success', 'Sukses menghapus Item Kategori ' . $coa->name);
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            Log::error('Admin/CoaController - destroy - error EX: '. $ex);
            return Response::json(array('errors' => 'INVALID'));
        }
    }
}

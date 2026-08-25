<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Coa;
use App\Models\CoaType;
use App\Transformer\CoaTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;


class CoaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function getIndex(Request $request){
        $user = Auth::guard('admin')->user();
        $coas = Coa::where('status_id', 1)
            ->where('branch_id', $user->branch_id);

        if($request->get('type')){
            $coas->where('type_id', $request->get('type'));
        }

        return DataTables::of($coas)
            ->setTransformer(new CoaTransformer)
            ->addIndexColumn()
            ->make(true);
    }

    public function select(Request $request){
        $user = Auth::guard('admin')->user();

        $term = trim($request->search);

        $searchDatas = DB::table('coas')
            ->select('id','code','name')
            ->where('status_id', 1)
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            })
            ->where('branch_id', $user->branch_id)
            ->orderBy('code')
            ->get();

        $formatted_tags = [];

        foreach ($searchDatas as $searchData) {
            $formatted_tags[] = ['id' => $searchData->id, 'text' => $searchData->code. " - " .$searchData->name];
        }

        return Response::json($formatted_tags);
    }

    public function selectParent(Request $request){
        $user = Auth::guard('admin')->user();

        $term = trim($request->search);

        $searchDatas = DB::table('coas')
            ->select('id','code','name')
            // ->where('is_header', 1)
            ->where('status_id', 1)
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            })
            ->where('branch_id', $user->branch_id)
            ->orderBy('code')
            ->get();

            $formatted_tags = [];

            foreach ($searchDatas as $searchData) {
                $formatted_tags[] = ['id' => $searchData->id, 'text' => $searchData->code. " - " .$searchData->name];
            }

            return Response::json($formatted_tags);
    }

    public function selectBankCash(Request $request){
        $user = Auth::guard('admin')->user();

        $term = trim($request->search);
        $coaKas = Coa::where('branch_id', $user->branch_id)
            ->where(function($q){
                $q->where('name', 'KAS')
                    ->orWhere('name', 'Kas')
                    ->orWhere('name', 'kas');
            })
            ->first();

        $searchDatas = DB::table('coas')
            ->select('id','code','name')
            ->where('status_id', 1)
            ->where('parent_id', $coaKas->id)
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            })
            ->where('branch_id', $user->branch_id)
            ->orderBy('code')
            ->get();

            $formatted_tags = [];

            foreach ($searchDatas as $searchData) {
                $formatted_tags[] = ['id' => $searchData->id, 'text' => $searchData->code. " - " .$searchData->name];
            }

            return Response::json($formatted_tags);
    }

    public function selectPiutang(Request $request){
        $user = Auth::guard('admin')->user();

        $term = trim($request->search);
        $coaKas = Coa::where('branch_id', $user->branch_id)
            ->where(function($q){
                $q->where('name', 'Piutang')
                    ->orWhere('name', 'Piutang');
            })
            ->first();

        $searchDatas = DB::table('coas')
            ->select('id','code','name')
            ->where('status_id', 1)
            ->where('parent_id', $coaKas->id)
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            })
            ->where('branch_id', $user->branch_id)
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
        $types = CoaType::where('status_id', 1)->get();

        return view('admin.coa.index', compact('types'));
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
        $coaTypes = CoaType::all();

        $data = [
            'coaTypes'     => $coaTypes,
        ];

        return view('admin.coa.create')->with($data);
    }

    public function store(Request $request)
    {
//        dd($request);
        $validator = Validator::make($request->all(), [
            'code'              => 'required|max:50',
            'name'              => 'required|max:100',
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());
        $existCoa = Coa::where('code', $request->input('code'))->first();
        if(!empty($existCoa)){
            return back()->withErrors("Code COA sudah ada.")->withInput($request->all());
        }

        //Create Admin
        $user = Auth::guard('admin')->user();

        $data = new Coa();
        $data->branch_id = $user->branch_id;
        $data->code = $request->input('code');
        $data->name = $request->input('name');
        $data->name = $request->input('name');
        $data->level = 1;
        $data->is_header = 0;
        $data->description = $request->input('description') ?? "";
        $data->status_id = 1;
        $data->cashflow = $request->has('cashflow') ? 1 : 0;
        $data->profit_loss = $request->has('profit_loss') ? 1 : 0;
        $data->balance_sheet = $request->has('balance_sheet') ? 1 : 0;
        $data->trial_balance = $request->has('trial_balance') ? 1 : 0;

        if($request->input('parent') != null){
            //case if parent
            $data->parent_id = $request->input('parent');

            $parent = Coa::find($request->input('parent'));

            $data->parent_code = $parent->code;
            $data->level = $parent->level + 1;
            // $data->is_header = 0;
        }
        else{
            $data->level = 1;
            $data->parent_id = 0;
        }

        if($request->input('type') != null){
            //case if parent
            $data->type_id = $request->input('type');

            $type = CoaType::find($request->input('type'));

            $data->type = $type->code;
        }

        $data->save();

        Session::flash('success', 'Sukses membuat COA baru');
        return redirect()->route('admin.coa.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $coa = Coa::find($id);
        $admin = Auth::guard('admin')->user();

        $coaTypes = CoaType::all();
        $data = [
            'coa'     => $coa,
            'coaTypes'     => $coaTypes,
        ];

        return view('admin.coa.edit')->with($data);
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

        $data = Coa::where('id', $id)->first();
        $data->code = $request->input('code');
        $data->name = $request->input('name');
        $data->name = $request->input('name');
        $data->level = 1;
        $data->description = $request->input('description') ?? "";
        $data->status_id = 1;
        $data->updated_at = Carbon::now('Asia/Jakarta');
        $data->updated_by = $user->id;
        $data->cashflow = $request->has('cashflow') ? 1 : 0;
        $data->profit_loss = $request->has('profit_loss') ? 1 : 0;
        $data->balance_sheet = $request->has('balance_sheet') ? 1 : 0;
        $data->trial_balance = $request->has('trial_balance') ? 1 : 0;

        if($request->input('parent') != null){
            //case if parent
            $data->parent_id = $request->input('parent');

            $parent = Coa::find($request->input('parent'));

            $data->parent_code = $parent->code;
            $data->level = $parent->level + 1;
            // $data->is_header = 0;
        }
        else{
            $data->level = 1;
            $data->parent_id = 0;
        }

        if($request->input('type') != null){
            //case if parent
            $data->type_id = $request->input('type');

            $type = CoaType::find($request->input('type'));

            $data->type = $type->code;
        }
        $data->save();

        Session::flash('success', 'Success Updating COA!');
        return redirect()->route('admin.coa.index');
    }

    public function delete(Request $request)
    {
        //
        try {
            $coaId = $request->input('id');
            $coa = Coa::find($coaId);

            if(empty($coa)){
                return Response::json(array('errors' => 'INVALID'));
            }
            $coa->status_id = 2;
            $coa->save();

        // $adminUser->delete();

            Session::flash('success', 'Sukses menghapus COA ' . $coa->name);
            return Response::json('Sukses', 200);
        }
        catch(\Exception $ex){
            Log::error('Admin/CoaController - destroy - error EX: '. $ex);
            return Response::json('error', 200);
        }
    }
}

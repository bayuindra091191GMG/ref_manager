<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeePosition;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class EmployeePositionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request){
        try {
            $admin = Auth::guard('admin')->user();

            $filterBranchId = $admin->branch_id;;
//            if(!$admin->is_super){
//                $filterBranchId = $admin->branch_id;
//            }
//            else{
//                // Get branch id filter
//                if($request->branch_id != null){
//                    $filterBranchId = intval($request->branch_id);
//                }
//            }

            $branches = DB::table('branches')
                ->orderBy('name')
                ->get();

            $data = [
//                'isSuperAdmin' => $admin->is_super,
                'isSuperAdmin' => false,
                'filterBranchId' => $filterBranchId,
                'branches' => $branches
            ];

            return view('admin.employee_position.index')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/EmployeeController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndex(Request $request)
    {
        $employees = EmployeePosition::query();
//        $employees = DB::table('employee_positions')
//            ->join('branches', 'employee_positions.branch_id', '=', 'branches.id')
//            ->select('employee_positions.id as id',
//                'employee_positions.code as code',
//                'employee_positions.name as name',
//                'employee_positions.created_at as created_at',
//                'branches.name as branch_name');

        $admin = Auth::guard('admin')->user();

        // Filter branch id
        if(!$admin->can('branch.scope_all')){
            $filterBranchId = $admin->branch_id;
            $employees = $employees->where('branch_id', '=', $filterBranchId);
        }
        else{
            if($request->filled('filter_branch_id')){
                $filterBranchId = $request->input('filter_branch_id');
                if($filterBranchId > 0){
                    $employees = $employees->where('branch_id', '=', $filterBranchId);
                }
            }
        }

        return DataTables::of($employees)
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->toIso8601String();
            })
            ->addColumn('action', function ($row) {
                $buttons = '';
                $buttons .= "<a class='btn btn-xs btn-primary' href='". route('admin.position.edit', $row->id). "'><i class='fa fa-pencil-alt'></i> Show</a>";;

                return $buttons;
            })
            ->make();
//        return DataTables::of($employees)
//            ->setTransformer(new EmployeePositionTransformer())
//            ->make(true);
    }

    /**
     * Function to show employee position create form
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|string
     */
    public function create(){
        try {
            $admin = Auth::guard('admin')->user();

//            $adminBranch = null;
//            if(!$admin->is_super){
//                $adminBranch = DB::table('branches')
//                    ->where('id', '=', $admin->branch_id)
//                    ->first();
//            }

            $branches = DB::table('branches')
                ->orderBy('name')
                ->get();

            $data = [
//                'isSuperAdmin' => $admin->is_super,
                'isSuperAdmin' => false,
//                'adminBranch' => $adminBranch,
                'adminBranchId' => $admin->branch_id,
                'branches' => $branches
            ];

            return view('admin.employee_position.create')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/EmployeePositionController - create '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    /**
     * Function to save newly created employee position to database
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:200',
                'code' => 'required|max:100'
            ],[
                'name.required' => 'Nama wajib diisi!',
                'name.max' => 'Nama maksimal 200 karakter!',
                'code.required' => 'Kode Karyawan sudah terdaftar!',
                'code.max' => 'Kode Karyawan maksimal 100 karakter!'
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $branchId = intval($request->input('branch'));

            // Validate unique code for selected branch
            $code = strtoupper(trim($request->input('code')));
            if(DB::table('employee_positions')
                ->where('branch_id', '=', $branchId)
                ->where('code', '=', $code)
                ->exists()){
                Session::flash('error', 'Kode Jabatan sudah terpakai!');
                return redirect()->back()->withInput($request->all());
            }

            $admin = Auth::guard('admin')->user();

            $newPosition = new EmployeePosition();
            $newPosition->branch_id = $branchId;
            $newPosition->code = $code;
            $newPosition->name = trim($request->input('name'));
            $newPosition->created_by = $admin->id;
            $newPosition->updated_by = $admin->id;
            $newPosition->save();

            Session::flash('success', 'Berhasil tambah Jabatan Karyawan baru!');
            return redirect()->route('admin.position.index');
        }
        catch (\Exception $ex){

            Log::channel('master_data')->error('Admin/EmployeePositionController - store '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }

    public function edit($id){
        try {
            $admin = Auth::guard('admin')->user();

            $position = EmployeePosition::find($id);
            if(empty($position)){
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back();
            }

            $data = [
                'position' => $position,
                'adminBranchId' => $admin->branch_id,
            ];

            return view('admin.employee_position.edit')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/EmployeePositionController - edit '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    /**
     * Function to save edited employee position to database
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id){
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:200',
                'code' => 'required|max:100'
            ],[
                'name.required' => 'Nama wajib diisi!',
                'name.max' => 'Nama maksimal 200 karakter!',
                'code.required' => 'Kode Karyawan sudah terdaftar!',
                'code.max' => 'Kode Karyawan maksimal 100 karakter!'
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $branchId = intval($request->input('branch'));

            // Validate unique code for selected branch
            $code = strtoupper(trim($request->input('code')));
            if(DB::table('employee_positions')
                ->where('id', '!=', $id)
                ->where('branch_id', '=', $branchId)
                ->where('code', '=', $code)
                ->exists()){
                Session::flash('error', 'Kode Jabatan sudah terpakai!');
                return redirect()->back()->withInput($request->all());
            }

            $admin = Auth::guard('admin')->user();

            $editedPosition = EmployeePosition::find($id);
            if(empty($editedPosition)){
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back()->withInput($request->all());
            }

            $editedPosition->code = $code;
            $editedPosition->name = trim($request->input('name'));
            $editedPosition->updated_by = $admin->id;
            $editedPosition->save();

            Session::flash('success', 'Berhasil ubah data Jabatan Karyawan!');
            return redirect()->route('admin.position.index');
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/EmployeePositionController - update '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }

    public function getPositions(Request $request){
        try {
            if($request->branch_id == null){
                return response()->json(collect([
                    'code' => 400,
                    'error' => 'BAD REQUEST'
                ]), 200);
            }

            $positions = DB::table('employee_positions')
                ->where('branch_id', '=', $request->branch_id)
                ->orderBy('name')
                ->get();

            $positionListArr = [];
            foreach ($positions as $position){
                $positionListArr[] = [
                    'id' => $position->id,
                    'text' => $position->name
                ];
            }

            return response()->json(collect([
                'code' => 200,
                'error' => 'none',
                'response' => [
                    'positions' => $positionListArr
                ]
            ]), 200);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/EmployeePositionController - getPositions '. $ex);

            return response()->json(collect([
                'code' => 500,
                'error' => $ex->getMessage()
            ]), 500);
        }
    }

    public function select(Request $request){
        $positions = EmployeePosition::all();

        $data = [];
        foreach ($positions as $position){
            $data[] = [
                'id' => $position->id,
                'text' => $position->name
            ];
        }

        return response($data);
    }
}

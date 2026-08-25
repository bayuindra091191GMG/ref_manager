<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function select(Request $request){

        $term = trim($request->search);

        $searchDatas = DB::table('departments')
            ->select('id','code','name')
            ->where(function($q) use ($term){
                $q->Where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            })
            ->orderByDesc('id')
            ->get();

        $formatted_tags = [];

        foreach ($searchDatas as $searchData) {
            $formatted_tags[] = ['id' => $searchData->id, 'text' => $searchData->code. " - ". $searchData->name];
        }

        return Response::json($formatted_tags);
    }

    public function index(Request $request){
        try {
            $admin = Auth::guard('admin')->user();

            $filterBranchId = $admin->branch_id;
//            if(!$admin->is_super){
//                $filterBranchId = $admin->branch_id;
//            }
//            else{
//                // Get branch id filter
//                if($request->branch_id != null){
//                    $filterBranchId = intval($request->branch_id);
//                }
//            }

            $departments = Department::with(['branch']);
            if($filterBranchId > 0){
                $departments = $departments->where('branch_id', '=', $filterBranchId);
            }

            $departments = $departments->get();

            //dd($departments);

            $branches = DB::table('branches')
                ->orderBy('name')
                ->get();

            $data = [
                'isSuperAdmin' => $admin->can('branch.scope_all'),
                'filterBranchId' => $filterBranchId,
                'departments' => $departments,
                'branches' => $branches
            ];

            return view('admin.department.index')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/DepartmentController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    /**
     * Function to show department create form
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
                'isSuperAdmin' => $admin->can('branch.scope_all'),
//                'adminBranch' => $adminBranch,
                'adminBranchId' => $admin->branch_id,
                'branches' => $branches
            ];

            return view('admin.department.create')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/EmployeeController - create '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    /**
     * Function to save newly created department to database
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:200',
                'code' => 'max:100'
            ],[
                'name.required' => 'Nama Departemen wajib diisi!',
                'name.max' => 'Nama Departemen maksimal 200 karakter!',
                'code.max' => 'Kode Departemen maksimal 100 karakter!'
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $branchId = intval($request->input('branch'));

            // Validate unique code for selected branch
            $code = null;
            if($request->filled('code')){
                $code = strtoupper(trim($request->input('code')));
                if(DB::table('departments')
                    ->where('branch_id', '=', $branchId)
                    ->where('code', '=', $code)
                    ->exists()){
                    Session::flash('error', 'Kode Departemen sudah terpakai!');
                    return redirect()->back()->withInput($request->all());
                }
            }

            $admin = Auth::guard('admin')->user();

            $newDepartment = new Department();
            $newDepartment->branch_id = $branchId;
            $newDepartment->code = $code;
            $newDepartment->name = trim($request->input('name'));
            $newDepartment->created_by = $admin->id;
            $newDepartment->updated_by = $admin->id;
            $newDepartment->save();

            Session::flash('success', 'Berhasil tambah departemen baru!');
            return redirect()->route('admin.department.index');
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/DepartmentController - store '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }

    /**
     * Function to show department edit form
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|string
     */
    public function edit($id){
        try {
            $admin = Auth::guard('admin')->user();

            $department = Department::where('id', '=', $id)
                ->first();

            if(empty($department)){
                Session::flash('error', 'BAD REQUEST');
                return redirect()->back();
            }

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
                'department' => $department,
                'isSuperAdmin' => $admin->can('branch.scope_all'),
//                'adminBranch' => $adminBranch,
                'adminBranchId' => $admin->branch_id,
                'branches' => $branches
            ];

            return view('admin.department.edit')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/DepartmentController - edit '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    /**
     * Function to save newly updated department to database
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:200',
                'code' => 'max:100'
            ],[
                'name.required' => 'Nama Departemen wajib diisi!',
                'name.max' => 'Nama Departemen maksimal 200 karakter!',
                'code.max' => 'Kode Departemen maksimal 100 karakter!'
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $branchId = intval($request->input('branch'));

            // Validate unique code for selected branch
            $code = null;
            if($request->filled('code')){
                $code = strtoupper(trim($request->input('code')));
                if(DB::table('departments')
                    ->where('branch_id', '=', $branchId)
                    ->where('code', '=', $code)
                    ->where('id', '!=', $id)
                    ->exists()){
                    Session::flash('error', 'Kode Departemen sudah terpakai!');
                    return redirect()->back()->withInput($request->all());
                }
            }

            $admin = Auth::guard('admin')->user();

            $updatedDepartment = Department::where('id', '=', $id)->first();
            if(empty($updatedDepartment)){
                Session::flash('error', 'Berhasil ubah data departemen!');
                return redirect()->back()->withInput($request->all());
            }

            $updatedDepartment->branch_id = $branchId;
            $updatedDepartment->code = $code;
            $updatedDepartment->name = trim($request->input('name'));
            $updatedDepartment->created_by = $admin->id;
            $updatedDepartment->updated_by = $admin->id;
            $updatedDepartment->save();

            Session::flash('success', 'Berhasil ubah data departemen!');
            return redirect()->route('admin.department.index');
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/DepartmentController - update '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }
}

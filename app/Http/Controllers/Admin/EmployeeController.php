<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Models\EmployeePosition;
use App\Models\PkwtHistory;
use App\Transformer\EmployeeTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
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

            // Filter status
            $filterStatusId = -1;
            if($request->status_id != null){
                $filterStatusId = intval($request->status_id);
            }

            // Filter position
            $filterPositionId = -1;
            if($request->position_id != null){
                $filterPositionId = intval($request->position_id);
            }

            $positions = DB::table('employee_positions')
                ->where('branch_id', $filterBranchId)
                ->orderBy('name')
                ->get();

            $branches = DB::table('branches')
                ->orderBy('name')
                ->get();

            $data = [
//                'isSuperAdmin' => $admin->is_super,
                'isSuperAdmin' => false,
                'filterBranchId' => $filterBranchId,
                'filterStatusId' => $filterStatusId,
                'filterPositionId' => $filterPositionId,
                'positions' => $positions,
                'branches' => $branches
            ];

            return view('admin.employee.index')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/EmployeeController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndex(Request $request){
        $employees = DB::table('employees')
            ->join('branches', 'employees.branch_id', '=', 'branches.id')
            ->join('statuses', 'employees.status_id', '=', 'statuses.id')
            ->leftJoin('employee_positions', 'employees.position_id', '=', 'employee_positions.id')
            ->select('employees.id as id',
                'employees.code as code',
                'employees.number as number',
                'employees.name as name',
                'employees.phone as phone',
                'employees.address as address',
                'employees.created_by as created_by',
                'employees.created_at as created_at',
                'employees.updated_at as updated_at',
                'employees.updated_by as updated_by',
                'employees.attend_machine_id as attend_machine_id',
                'employee_positions.name as position_name',
                'branches.name as branch_name',
                'statuses.display as status_display');

//        $admin = Auth::guard('admin')->user();

        // Filter branch id
        if($request->filled('filter_branch_id')){
            $filterBranchId = $request->input('filter_branch_id');
            if($filterBranchId > 0){
                $employees->where('employees.branch_id', '=', $filterBranchId);
            }
        }

        // Filter Contract
        if($request->get('contract')){
            $employees->join('employee_contracts', 'employees.id', '=', 'employee_contracts.employee_id')
                ->whereRaw('DATE_ADD(employee_contracts.end_date, INTERVAL -1 MONTH) <= CURDATE() AND CURDATE() <= employee_contracts.end_date');
        }

        // Filter status id
        if($request->filled('filter_status_id')){
            $filterStatusId = $request->input('filter_status_id');
            if($filterStatusId > 0){
                $employees->where('employees.status_id', '=', $filterStatusId);
            }
        }

        // Filter position id
        if($request->filled('filter_position_id')){
            $filterPositionId = $request->input('filter_position_id');
            if($filterPositionId > 0){
                $employees->where('employees.position_id', '=', $filterPositionId);
            }
        }

        return DataTables::of($employees)
            ->setTransformer(new EmployeeTransformer())
            ->make(true);
    }

    public function show($id){
        try {

            $employee = Employee::with(['status', 'employee_position', 'branch'])
                ->where('id', '=', $id)
                ->first();

            if(empty($employee)){
                Session::flash('error', 'BAD REQUEST');
                return redirect()->back();
            }

            $departmentName = '';
            if(!empty($employee->department_id)){
                $departmentName = DB::table('departments')
                    ->where('id', '=', $employee->department_id)
                    ->value('name');
            }

            $data = [
                'employee' => $employee,
                'departmentName' => $departmentName
            ];

            return view('admin.employee.show')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/EmployeeController - index '. $ex);
            Session::flash('error', $ex);
            return redirect()->back();
        }
    }

    /**
     * Function to show employee create form
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

//            $positions = null;
//            if(!empty($adminBranch)){
//                $positions = DB::table('employee_positions')
//                    ->where('branch_id', '=', $adminBranch->id)
//                    ->orderBy('name')
//                    ->get();
//            }

            $positions = DB::table('employee_positions')
                ->where('branch_id', '=', $admin->branch_id)
                ->orderBy('name')
                ->get();

            $departments = DB::table('departments')
                ->where('branch_id', '=', $admin->branch_id)
                ->orderBy('name')
                ->get();

            $data = [
//                'isSuperAdmin' => $admin->is_super,
                'isSuperAdmin' => false,
//                'adminBranch' => $adminBranch,
                'adminBranchId' => $admin->id,
                'branches' => $branches,
                'departments' => $departments,
                'positions' => $positions,
            ];

            return view('admin.employee.create')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/EmployeeController - create '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    /**
     * Function to save newly created employee to database
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:200',
                'code' => 'required|max:100',
//                'phone' => 'required|max:50',
            ],[
                'name.required' => 'Nama wajib diisi!',
                'name.max' => 'Nama maksimal 200 karakter!',
                'code.required' => 'Kode Karyawan sudah terdaftar!',
                'code.max' => 'Kode Karyawan maksimal 100 karakter!',
//                'phone.required' => 'Nomor Telp wajib diisi!',
//                'phone.max' => 'Nomor Telp maksimal 50 karakter!',
            ]);

            if ($validator->fails()) return redirect()
                ->back()
                ->withErrors($validator->errors())
                ->withInput($request->all());

            $admin = Auth::guard('admin')->user();

            // Validate unique code for selected branch
            $code = strtoupper(trim($request->input('code')));
            if(DB::table('employees')
                ->where('branch_id', '=', $admin->branch_id)
                ->where('code', '=', $code)
                ->exists()){
                Session::flash('error', 'Kode Karyawan sudah terpakai!');
                return redirect()->back()->withInput($request->all());
            }

            $position = intval($request->input('position'));
            if($position < 1){
                Session::flash('error', 'Mohon pilih Posisi!');
                return redirect()->back()->withInput($request->all());
            }

            // $departmentId = intval($request->input('department'));
            // if($departmentId === 0){
            //     Session::flash('error', 'Mohon pilih Departemen!');
            //     return redirect()->back()->withInput($request->all());
            // }

            $newEmployee = new Employee();
            $newEmployee->branch_id = $admin->branch_id;
            $newEmployee->status_id = $request->input('status');
            $newEmployee->code = $code;
            $newEmployee->name = trim($request->input('name'));
            $newEmployee->phone = $request->filled('phone') ? trim($request->input('phone')) : null;
            $newEmployee->id_card_no = $request->filled('identity_no') ? trim($request->input('identity_no')) : null;
            $newEmployee->position_id = $position;
            // $newEmployee->department_id = $departmentId;
            $newEmployee->address = $request->filled('address') ? trim($request->input('address')) : null;
            $newEmployee->basic_salary_amount = Utilities::toFloat($request->input('basic_salary_amount'));
            $newEmployee->attend_machine_id = $request->filled('attend_machine_id') ? trim($request->input('attend_machine_id')) : null;
            $newEmployee->created_by = $admin->id;
            $newEmployee->updated_by = $admin->id;
            $newEmployee->save();
            // dd($request->all());
            $newEmployee->photo_path = 'default_person_photo.png';
            $newEmployee->save();

            Session::flash('success', 'Berhasil tambah karyawan baru!');
            return redirect()->route('admin.employee.show', ['id' => $newEmployee->id]);
        }
        catch (\Exception $ex){
            dd($ex);
            Log::channel('master_data')->error('Admin/EmployeeController - store '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }

    /**
     * Function to show employee edit form
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|string
     */
    public function edit($id){
        try {
            $admin = Auth::guard('admin')->user();

            $employee = Employee::with(['status', 'employee_position', 'branch'])
                ->where('id', '=', $id)
                ->first();

            if(empty($employee)){
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

            $positions = DB::table('employee_positions')
                ->where('branch_id', '=', $employee->branch_id)
                ->orderBy('name')
                ->get();

            $departments = DB::table('departments')
                ->where('branch_id', '=', $admin->branch_id)
                ->orderBy('name')
                ->get();

            $data = [
                'employee' => $employee,
//                'isSuperAdmin' => $admin->is_super,
                'isSuperAdmin' => false,
//                'adminBranch' => $adminBranch,
                'adminBranchId' => $admin->id,
                'branches' => $branches,
                'positions' => $positions,
                'departments' => $departments
            ];

            return view('admin.employee.edit')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/EmployeeController - edit '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    /**
     * Function to update employee to database
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:200',
                'code' => 'required|max:100',
//                'phone' => 'required|max:50',
            ],[
                'name.required' => 'Nama wajib diisi!',
                'name.max' => 'Nama maksimal 200 karakter!',
                'code.required' => 'Kode Karyawan sudah terdaftar!',
                'code.max' => 'Kode Karyawan maksimal 100 karakter!',
//                'phone.required' => 'Nomor Telp wajib diisi!',
//                'phone.max' => 'Nomor Telp maksimal 50 karakter!',
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $admin = Auth::guard('admin')->user();

            // Validate unique code for selected branch
            $code = strtoupper(trim($request->input('code')));
            if(DB::table('employees')
                ->where('id', '!=', $id)
                ->where('branch_id', '=', $admin->branch_id)
                ->where('code', '=', $code)
                ->exists()){
                Session::flash('error', 'Kode Karyawan sudah terpakai!');
                return redirect()->back()->withInput($request->all());
            }

            // Validate position
            $positionId = intval($request->input('position'));
            if($positionId < 1){
                Session::flash('error', 'Mohon pilih Jabatan!');
                return redirect()->back()->withInput($request->all());
            }

            $departmentId = intval($request->input('department'));
            if($departmentId === 0){
                Session::flash('error', 'Mohon pilih Departemen!');
                return redirect()->back()->withInput($request->all());
            }


            $updatedEmployee = Employee::where('id', '=', $id)->first();

            if(empty($updatedEmployee)){
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back()->withInput($request->all());
            }

            $updatedEmployee->branch_id = $admin->branch_id;
            $updatedEmployee->code = $code;
            $updatedEmployee->name = trim($request->input('name'));
            $updatedEmployee->phone = $request->filled('phone') ? trim($request->input('phone')) : null;
            $updatedEmployee->id_card_no = $request->filled('identity_no') ? trim($request->input('identity_no')) : null;
            $updatedEmployee->position_id = $positionId;
            $updatedEmployee->department_id = $departmentId;
            $updatedEmployee->address = $request->filled('address') ? trim($request->input('address')) : null;
            $updatedEmployee->basic_salary_amount = Utilities::toFloat($request->input('basic_salary_amount'));
            $updatedEmployee->attend_machine_id = $request->filled('attend_machine_id') ? trim($request->input('attend_machine_id')) : null;
            $updatedEmployee->save();


            Session::flash('success', 'Berhasil ubah data Karyawan');
            return redirect()->route('admin.employee.show', ['id' => $id]);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/EmployeeController - update '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }

    public function select(Request $request){
        $term = trim($request->search);

        $user = Auth::guard('admin')->user();

        $employees = DB::table('employees')
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            });
        $employees = $employees->where('branch_id', $user->branch_id);

        if($request->get('position_id') != null){
            $employees->where('position_id', $request->get('position_id'));
        }

        // Check is_empty_admin parameter
        if($request->is_empty_admin != null){
            $isEmptyAdmin = intval($request->is_empty_admin);
            if($isEmptyAdmin > 0){
                $employees = $employees->whereNull('admin_user_id');
            }
        }

        $employees = $employees->orderBy('code')
            ->get();

        $formatted_tags = [];

        foreach ($employees as $employee) {
            $formatted_tags[] = ['id' => $employee->id, 'text' => $employee->code. " - ". $employee->name];
        }

        return Response::json($formatted_tags);
    }

    /**
     * Function to update employee status
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request){
        try {
            if(!$request->filled('updated_status_id') || !$request->filled('updated_employee_id')){
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back()->withInput($request->all());
            }

            $updatedEmployeeId = intval($request->input('updated_employee_id'));
            $employee = Employee::where('id', '=', $updatedEmployeeId)
                ->first();

            if(empty($employee)){
                Session::flash('error', 'BAD REQUEST');
                return redirect()->back();
            }

            $updatedStatusId = intval($request->input('updated_status_id'));
            if($updatedStatusId < 1 || $updatedStatusId > 2){
                Session::flash('error', 'BAD REQUEST');
                return redirect()->back();
            }

            // Update status
            $employee->status_id = $updatedStatusId;
            $employee->save();

            Session::flash('success', 'Berhasil ubah status Karyawan!');
            return redirect()->route('admin.employee.show', ['id' => $updatedEmployeeId]);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/EmployeeController - updateStatus '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }

    public function getGros(Request $request){
        try{
            $skip = 0;
            $take = 10;
            if($request->exists('skip')){
                $skip = $request->query('skip');
            }

            if($request->exists('take')){
                $take = $request->query('take');
            }

            $arr = [];
            //position id should be based on branch
            $user = Auth::guard('admin')->user();

            $userData = AdminUser::where('id', $user->id)->first();
            $position = EmployeePosition::where('branch_id', $userData->branch_id)->where('code', 'GRO')->first();

            $employees = Employee::with(['branch', 'employee_position', 'status'])->where('position_id', $position->id)->where('status_id', '!=', 2);
            if($request->exists('search') && $request->query('search') != ''){
                $employees = $employees->where('name', 'like', '%' . $request->query('search') . '%');
            }
            $employees = $employees->skip($skip)->take($take)->get();
            $count = DB::table('employees')->where('position_id', $position->id)->count();
            $pages = [];
            $tmpPageCount = ceil($count / 10);
            for($i=1; $i<=$tmpPageCount; $i++){
                $pages[] = $i;
            }

            $transformer = new EmployeeTransformer();
            foreach ($employees as $employee){
                $arr[] = $transformer->transform($employee);
            }

            $data = [
                'gros'      => $arr,
                'count'     => $count,
                'pages'     => $pages,
            ];

            return Response::json($data, 200);
        }
        catch (\Exception $ex){
            return Response::json('failed' . $ex, 500);
        }
    }




    // CONTRACT

    public function createContract($id){
        return view('admin.employee.contract.create', compact('id'));
    }

    public function storeContract(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'code' => 'required|max:255',
            'education' => 'required|max:200',
            'address' => 'required|max:200',
            'phone_number' => 'required|max:45',
            'ktp' => 'required',
            'pkwt' => 'required'
        ],[
            'name.required' => 'Nama Karyawan wajib diisi!',
            'name.max' => 'Nama Karyawan maksimal 255 karakter!',
            'code.required' => 'Nomor Kontrak wajib diisi!',
            'code.max' => 'Nomor Kontrak maksimal 255 karakter!',
            'education.required' => 'Pendidikan wajib diisi!',
            'education.max' => 'Pendidikan maksimal 200 karakter!',
            'address.required' => 'Alamat wajib diisi!',
            'address.max' => 'Alamat maksimal 200 karakter!',
            'phone_number.required' => 'No Telp wajib diisi!',
            'phone_number.max' => 'No Telp maksimal 45 karakter!',
            'ktp.required' => 'Foto KTP wajib diisi!',
            'pkwt.required' => 'Dokumen PKWT wajib diisi!',
        ]);

        if ($validator->fails()) return redirect()
            ->back()
            ->withErrors($validator->errors())
            ->withInput($request->all());

        try {
            $user = Auth::guard('admin')->user();

            $contract = EmployeeContract::create([
                'branch_id' => $user->branch_id,
                'employee_id' => $request->input('employee_id'),
                'duration' => $request->input('duration'),
                'start_date' => Carbon::parse($request->input('start_date'))->toDateTimeString(),
                'end_date' => Carbon::parse($request->input('end_date'))->toDateTimeString(),
                'code' => trim($request->input('code')),
                'name' => trim($request->input('name')),
                'marital_status' => trim($request->input('marital_status')),
                'gender' => trim($request->input('gender')),
                'education' => trim($request->input('education')),
                'address' => trim($request->input('address')),
                'phone_number' => trim($request->input('phone_number')),
                'salary' => $request->input('salary') ?? 0,
                'commission' => Utilities::toFloat($request->input('commission'))/10,
            ]);

            $ktp = $request->file('ktp');
            $pkwt = $request->file('pkwt');

            $ktpName = 'ktp_' . $contract->id . '_' . date('d-m-Y_H-i') . '.' . $ktp->getClientOriginalExtension();
            $pkwtName = 'pkwt_' . $contract->id . '_' . date('d-m-Y_H-i') . '.' . $pkwt->getClientOriginalExtension();

            $ktp->storeAs('', $ktpName, 'employee_ktp');
            $pkwt->storeAs('', $pkwtName, 'employee_pkwt');

            $contract->update([
                'ktp_image_path' => $ktpName,
                'pkwt_image_path' => $pkwtName
            ]);

            Session::flash('success', 'Berhasil membuat kontrak karyawan!');
            return redirect()->route('admin.employee.show', ['id' => $request->input('employee_id')]);
        } catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/EmployeeController - storeContract '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }

    public function editContract($id){
        $contract = EmployeeContract::where('employee_id', $id)->first();

        return view('admin.employee.contract.edit', compact('contract'));
    }

    public function updateContract(Request $request){
        $validator = Validator::make($request->all(), [
            'code' => 'required|max:255',
        ],[
            'code.required' => 'Nomor Kontrak wajib diisi!',
            'code.max' => 'Nomor Kontrak maksimal 255 karakter!',
        ]);

        if ($validator->fails()) return redirect()
            ->back()
            ->withErrors($validator->errors())
            ->withInput($request->all());

        try {
            $contract = EmployeeContract::find($request->input('id'));

            $contract->update([
                'duration' => $request->input('duration'),
                'start_date' => Carbon::parse($request->input('start_date'))->toDateTimeString(),
                'end_date' => Carbon::parse($request->input('end_date'))->toDateTimeString(),
                'code' => trim($request->input('code')),
                'name' => trim($request->input('name')),
                'marital_status' => trim($request->input('marital_status')),
                'gender' => trim($request->input('gender')),
                'education' => trim($request->input('education')),
                'address' => trim($request->input('address')),
                'phone_number' => trim($request->input('phone_number')),
                'commission' => Utilities::toFloat($request->input('commission'))/10,
                'salary' => $request->input('salary') ?? 0,
            ]);

            if($request->hasFile('pkwt')){
                $pkwt = $request->file('pkwt');
                $pkwtName = 'pkwt_' . $contract->id . '_' . date('d-m-Y_H-i') . '.' . $pkwt->getClientOriginalExtension();
                $pkwt->storeAs('', $pkwtName, 'employee_pkwt');

                PkwtHistory::create([
                    'type' => 'employee',
                    'reference_id' => $contract->employee_id,
                    'filename' => $contract->pkwt_image_path
                ]);

                $contract->update([
                    'pkwt_image_path' => $pkwtName
                ]);
            }

            if($request->hasFile('ktp')){
                $ktp = $request->file('ktp');
                $ktpName = 'ktp_' . $contract->id . '_' . date('d-m-Y_H-i') . '.' . $ktp->getClientOriginalExtension();
                $ktp->storeAs('', $ktpName, 'employee_ktp');

                $contract->update([
                    'ktp_image_path' => $ktpName,
                ]);
            }

            Session::flash('success', 'Berhasil mengubah kontrak karyawan!');
            return redirect()->route('admin.employee.show', ['id' => $contract->employee_id]);
        } catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/EmployeeController - storeContract '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }

    public function deleteContract(Request $request){
        $contract = EmployeeContract::find($request->input('id'));

        try {
            $employee_id = $contract->employee_id;
            $contract->delete();

            Session::flash('success', 'Berhasil menghapus kontrak karyawan!');
            return redirect()->route('admin.employee.show', ['id' => $employee_id]);
        } catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/EmployeeController - storeContract '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }

    }

    public function pkwtHistory($id){
        $pkwts = PkwtHistory::where('type', 'employee')
            ->where('reference_id', $id)
            ->get();

        return view('admin.employee.contract.history', compact('id', 'pkwts'));
    }
}

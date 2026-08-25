<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Employee;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request){
        try {
            $admin = Auth::guard('admin')->user();
            $filterBranchId = $admin->branch_id;

            $adminUsers = AdminUser::query()
                ->where('status_id', 1)
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'superadmin')
                        ->where('guard_name', 'admin');
                });

            if($filterBranchId > 0){
                $adminUsers = $adminUsers->where('branch_id', '=', $filterBranchId);
            }

            $adminUsers = $adminUsers->get();

            $data = [
                'filterBranchId' => $filterBranchId,
                'adminUsers' => $adminUsers,
                'isDeveloper' => $admin->hasRole('superadmin')
            ];

            return view('admin.admin_user.index')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/AdminUserController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    /**
     * Function to show admin create form
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|string
     */
    public function create(){
        try {
            $roles = Role::query()
                ->where('guard_name', 'admin')
                ->orderBy('name')
                ->get();

            return view('admin.admin_user.create', compact('roles'));
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/AdminUserController - create '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    /**
     * Function to save newly created admin to database
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|max:100',
                'new_password' => 'required|max:20',
                'retry_password' => 'required|max:20'
            ],[
                'username.required' => 'Username wajib diisi!',
                'username.max' => 'Username maksimal 100 karakter!',
                'new_password.required' => 'Kata Sandi wajib diisi!',
                'new_password.max' => 'Kata Sandi maksimal 20 karakter!',
                'retry_password.required' => 'Ulangi Kata Sandi wajib diisi!',
                'retry_password.max' => 'Ulangi Kata Sandi maksimal 20 karakter!'
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $admin = Auth::guard('admin')->user();

            // Validate unique username for selected branch
            $username = trim($request->input('username'));
            if(DB::table('admin_users')
                ->where('branch_id', '=', $admin->branch_id)
                ->where('username', '=', $username)
                ->exists()){
                Session::flash('error', 'Username sudah terpakai!');
                return redirect()->back()->withInput($request->all());
            }

            $newPassword = trim($request->input('new_password'));
            $retryPassword = trim($request->input('retry_password'));
            if($newPassword !== $retryPassword){
                Session::flash('error', 'Kata Sandi tidak cocok!');
                return redirect()->back()->withInput($request->all());
            }

            $newAdmin = new AdminUser();
            $newAdmin->branch_id = $admin->branch_id;
            $newAdmin->username = $username;
            $newAdmin->name = trim($request->input('name'));
            $newAdmin->password = Hash::make($newPassword);
            $newAdmin->save();
            $role = Role::query()
                ->where('guard_name', 'admin')
                ->where('name', $request->input('role', 'staff'))
                ->firstOrFail();
            $newAdmin->syncRoles([$role]);

            if($request->filled('employee')){
                $employee = Employee::find($request->input('employee'));
                $employee->admin_user_id = $newAdmin->id;
                $employee->save();
            }

            Session::flash('success', 'Berhasil tambah admin baru!');
            return redirect()->route('admin.admin_user.index');
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/AdminUserController - store '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }

    public function edit($id){
        try {
            $admin = AdminUser::find($id);
            $linkedEmployee = DB::table('employees')
                ->where('admin_user_id', '=', $id)
                ->first();

            $data = [
                'admin' => $admin,
                'linkedEmployee' => $linkedEmployee,
                'roles' => Role::query()->where('guard_name', 'admin')->orderBy('name')->get(),
                'currentRole' => $admin->roles->first()?->name ?? 'staff',
            ];

            return view('admin.admin_user.edit')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/AdminUserController - edit '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|max:100',
            ],[
                'username.required' => 'Username wajib diisi!',
                'username.max' => 'Username maksimal 100 karakter!'
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $admin = Auth::guard('admin')->user();

            // Validate unique username for selected branch
            $username = trim($request->input('username'));
            if(DB::table('admin_users')
                ->where('branch_id', '=', $admin->branch_id)
                ->where('username', '=', $username)
                ->where('id', '!=', $id)
                ->exists()){
                Session::flash('error', 'Username sudah terpakai!');
                return redirect()->back()->withInput($request->all());
            }

            $updatedAdmin = AdminUser::find($id);
            if($request->filled('new_password') && $request->filled('retry_password')){
                $newPassword = trim($request->input('new_password'));
                $retryPassword = trim($request->input('retry_password'));
                if($newPassword !== $retryPassword){
                    Session::flash('error', 'Kata Sandi tidak cocok!');
                    return redirect()->back()->withInput($request->all());
                }

                $updatedAdmin->password = Hash::make($newPassword);
            }

            $updatedAdmin->username = $username;
            $updatedAdmin->name = trim($request->input('name'));
            $updatedAdmin->save();
            $roleName = $request->input('role', $updatedAdmin->roles->first()?->name ?? 'staff');
            $role = Role::query()
                ->where('guard_name', 'admin')
                ->where('name', $roleName)
                ->firstOrFail();

            if ((int) $id === (int) $admin->id && $role->name !== 'superadmin') {
                throw new \RuntimeException('Superadmin cannot remove its own superadmin role.');
            }

            $updatedAdmin->syncRoles([$role]);

            if($request->filled('employee')){
                $employee = Employee::find($request->input('employee'));

                // Reset admin user
                DB::table('employees')
                    ->where('admin_user_id', '=', $id)
                    ->update([
                        'admin_user_id' => null
                    ]);

                $employee->admin_user_id = $id;
                $employee->save();
            }

            Session::flash('success', 'Berhasil ubah admin!');
            return redirect()->route('admin.admin_user.index');
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/AdminUserController - update '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }

    public function showChangePasswordForm(){
        return view('admin.admin_user.change_password');
    }

    public function submitChangePassword(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'old_password' => 'required|max:16',
                'new_password' => 'required|max:16'
            ],[
                'old_password.required' => 'Kata Sandi Lama wajib diisi!',
                'old_password.max' => 'Kata Sandi Lama maksimal 16 karakter!',
                'new_password.required' => 'Kata Sandi Baru wajib diisi!',
                'new_password.max' => 'Kata Sandi Baru maksimal 16 karakter!',
            ]);

            if ($validator->fails()) return redirect()
                ->back()
                ->withErrors($validator->errors())
                ->withInput($request->all());

            $admin = Auth::guard('admin')->user();

            $oldPassword = trim($request->input('old_password'));
            if(!Hash::check($oldPassword, $admin->password)){
                Session::flash('error', 'Kata Sandi Lama salah!');
                return redirect()->back()->withInput($request->all());
            }

            $newPassword = trim($request->input('new_password'));
            $retryPassword = trim($request->input('retry_password'));
            if($newPassword !== $retryPassword){
                Session::flash('error', 'Kata Sandi Baru tidak cocok!');
                return redirect()->back()->withInput($request->all());
            }

            DB::table('admin_users')
                ->where('id', '=', $admin->id)
                ->update([
                    'password' => Hash::make($newPassword)
                ]);

            Session::flash('success', 'Berhasil ganti Kata Sandi!');
            return redirect()->route('admin.my.change_password');
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/AdminUserController - submitChangePassword '. $ex);
            Session::flash('error', $ex);
            return redirect()->back();
        }
    }

    public function select(Request $request){
        $users = AdminUser::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'superadmin')
                    ->where('guard_name', 'admin');
            })
            ->where('name', 'LIKE', '%'. $request->get('search'). '%')
            ->get();

        $data = [];
        foreach ($users as $user){
            $data[] = [
                'id' => $user->id,
                'text' => $user->name
            ];
        }

        return response($data);
    }
}

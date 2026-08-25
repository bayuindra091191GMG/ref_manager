<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthorizationPosition;
use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AuthorizationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Function to show list of authorized modules of each position
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|string
     */
    public function index(Request $request){
        try {
//            $branchId = 1;

            $admin = Auth::guard('admin')->user();
//            if($admin->is_super){
//                if($request->branch_id != null){
//                    $branchId = intval($request->branch_id);
//                    if(!DB::table('branches')
//                        ->where('id', '=', $branchId)
//                        ->exists()){
//                        $branchId = 1;
//                    }
//                }
//            }
//            else{
//                $branchId = $admin->branch_id;
//            }

            $positions = DB::table('employee_positions')
                ->where('branch_id', '=', $admin->branch_id)
                ->orderBy('name')
                ->get();

            $authCollects = collect();
            $idx = 1;
            foreach ($positions as $position){
                $authPositionModules = DB::table('authorization_positions')
                    ->join('authorization_modules', 'authorization_positions.auth_module_id', '=', 'authorization_modules.id')
                    ->select('authorization_positions.auth_module_id as auth_module_id',
                        'authorization_modules.action as action',
                        'authorization_modules.menu_level_1_name as menu_level_1_name',
                        'authorization_modules.menu_level_2_name as menu_level_2_name',
                        'authorization_modules.menu_level_3_name as menu_level_3_name')
                    ->where('authorization_positions.position_id', '=', $position->id)
                    ->where('authorization_positions.branch_id', '=', $admin->branch_id)
                    ->orderBy('authorization_modules.menu_level_1_name')
                    ->orderBy('authorization_modules.menu_level_2_name')
                    ->orderBy('authorization_modules.menu_level_3_name')
                    ->orderBy('authorization_modules.action')
                    ->get();

                $authModuleNameCollects = collect();
                $authPositionModuleIds = '';
                if(count($authPositionModules) > 0){
                    $i = 0;
                    foreach ($authPositionModules as $authPositionModule){
                        if($i === 0){
                            $authPositionModuleIds .= $authPositionModule->auth_module_id;
                        }
                        else{
                            $authPositionModuleIds .= ','. $authPositionModule->auth_module_id;
                        }

                        if(!empty($authPositionModule->menu_level_1_name)){
                            $authModuleName = $authPositionModule->menu_level_1_name. ' - '. $authPositionModule->action;
                            error_log($authModuleName);

                            if(!empty($authPositionModule->menu_level_2_name)){
                                $authModuleName = $authPositionModule->menu_level_1_name. ' - '. $authPositionModule->menu_level_2_name. ' - '. $authPositionModule->action;

                                if(!empty($authPositionModule->menu_level_3_name)){
                                    $authModuleName = $authPositionModule->menu_level_1_name. ' - '. $authPositionModule->menu_level_2_name. ' - '. $authPositionModule->menu_level_3_name. ' - '. $authPositionModule->action;
                                }
                            }
                        }
                        else{
                            $authModuleName = $authPositionModule->action;
                        }

                        $authModuleNameCollects->push($authModuleName);
                        $i++;
                    }
                }

                $authCollects->push(collect([
                    'index' => $idx,
                    'positionId' => $position->id,
                    'name' => $position->name,
                    'modules' => $authModuleNameCollects,
                    'moduleIds' => $authPositionModuleIds
                ]));

                $idx++;
            }

            // Get available auth modules
            $authModules = DB::table('authorization_modules')
                ->where('authorization_modules.branch_id', '=', $admin->branch_id)
                ->orderBy('authorization_modules.menu_level_1_name')
                ->orderBy('authorization_modules.menu_level_2_name')
                ->orderBy('authorization_modules.menu_level_3_name')
                ->orderBy('authorization_modules.action')
                ->get();

            $authModuleArr = [];
            foreach ($authModules as $authModule){
                if(!empty($authModule->menu_level_1_name)){
                    $moduleItem = $authModule->menu_level_1_name. ' - '. $authModule->action;

                    if(!empty($authModule->menu_level_2_name)){
                        $moduleItem = $authModule->menu_level_1_name. ' - '. $authModule->menu_level_2_name. ' - '. $authModule->action;

                        if(!empty($authModule->menu_level_3_name)){
                            $moduleItem = $authModule->menu_level_1_name. ' - '. $authModule->menu_level_2_name. ' - '. $authModule->menu_level_3_name. ' - '. $authModule->action;
                        }
                    }
                }
                else{
                    $moduleItem = $authModule->action;
                }

                $authModuleArr[] = [
                    'value' => $authModule->id,
                    'text' => $moduleItem
                ];
            }

            $branches = DB::table('branches')
                ->orderBy('name')
                ->get();

            $data = [
                'isSuperAdmin' => $admin->can('branch.scope_all'),
                'branches' => $branches,
                'branchId' => $admin->branch_id,
                'authCollects' => $authCollects,
                'authModules' => $authModuleArr
            ];

            return view('admin.authorization.setting')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('setting')->error('Admin/AuthorizationController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    /**
     * Function to update authorized modules of edited employee position
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request){
        try {
            if(!$request->filled('edited_branch_id')){
                return redirect()->back()->withErrors('BAD REQUEST!', 'default')->withInput($request->all());
            }

            if(!$request->filled('edited_auth_modules')){
                return redirect()->back()->withErrors('Mohon pilih modul authorisasi!', 'default')->withInput($request->all());
            }

            if(!$request->filled('edited_position_id')){
                return redirect()->back()->withErrors('BAD REQUEST', 'default')->withInput($request->all());
            }

            $branchId = intval($request->input('edited_branch_id'));
            $editedPositionId = $request->input('edited_position_id');
            $inputModules = $request->input('edited_auth_modules');

            // Check for new authorized modules
            foreach ($inputModules as $inputModule){
                if(!DB::table('authorization_positions')
                    ->where('position_id', '=', $editedPositionId)
                    ->where('auth_module_id', '=', $inputModule)
                    ->where('branch_id', '=', $branchId)
                    ->exists()){

                    AuthorizationPosition::create([
                        'position_id' => $editedPositionId,
                        'auth_module_id' => $inputModule,
                        'branch_id' => $branchId
                    ]);

                    Log::channel('auth')->info('Admin/AuthorizationController - AUTH MODULE CREATED position_id:'. $editedPositionId. ' module_id: '. $inputModule);
                }
            }

            // Check for deleted authorized modules
            $authorizedPositions = DB::table('authorization_positions')
                ->where('position_id', '=', $editedPositionId)
                ->where('branch_id', '=', $branchId)
                ->get();

            foreach ($authorizedPositions as $authorizedPosition){
                if(!in_array($authorizedPosition->auth_module_id, $inputModules)){
                    if($authorizedPosition->auth_module_id !== 0){
                        // Delete module
                        DB::table('authorization_positions')
                            ->where('position_id', '=', $editedPositionId)
                            ->where('auth_module_id', '=', $authorizedPosition->auth_module_id)
                            ->where('branch_id', '=', $branchId)
                            ->delete();

                        Log::channel('setting')->info('Admin/AuthorizationController - AUTH MODULE DELETED position_id:'. $editedPositionId. ' module_id: '. $authorizedPosition->auth_module_id);
                    }
                }
            }

            Session::flash('message', 'Berhasil ubah ijin akses modul!');
            return redirect()->route('admin.auth.module.index');
        }
        catch (\Exception $ex){
            Log::channel('setting')->error('Admin/AuthorizationController - index '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back();
        }
    }

    /**
     * Function to update authorized modules of edited employee position
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePassword(){

        return view('admin.authorization.change_password');
    }

    /**
     * Function to update authorized modules of edited employee position
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request){
        try {
            if(!$request->filled('oldPassword')){
                return redirect()->back()->withErrors('BAD REQUEST!', 'default')->withInput($request->all());
            }

            if(!$request->filled('newPassword')){
                return redirect()->back()->withErrors('BAD REQUEST', 'default')->withInput($request->all());
            }

            if(!$request->filled('confirmPassword')){
                return redirect()->back()->withErrors('BAD REQUEST', 'default')->withInput($request->all());
            }

            if($request->input('newPassword') != $request->input('confirmPassword')){
                return redirect()->back()->withErrors('PASSWORD BARU TIDAK COCOK', 'default')->withInput($request->all());
            }

            $admin = Auth::guard('admin')->user();

            $oldPassword = $request->input('oldPassword');
            $branchId = $admin->branch_id;

            $branchPassword = Configuration::where('branch_id', $branchId)
                ->where('key', 'cancel_password')
                ->where('value', $oldPassword)
                ->first();
            if(empty($branchPassword)){
                return redirect()->back()->withErrors('PASSWORD SEBELUMNYA TIDAK BENAR', 'default')->withInput($request->all());
            }

            $branchPassword->value = $request->input('newPassword');
            $branchPassword->save();

            Session::flash('success', 'Berhasil ubah password manager!');
            return redirect()->route('admin.manager_password.index');
        }
        catch (\Exception $ex){
            Log::channel('setting')->error('Admin/AuthorizationController - updatePassword '. $ex);
            dd($ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back();
        }
    }
}

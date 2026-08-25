<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeePosition;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class MenuAccessController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $data = Menu::orderBy('sequence')->get();

        $menus = [];
        foreach ($data as $d){
            $actData = explode(',', $d->actions);
            $actions = [];
            foreach ($actData as $action){
                $actions[] = [
                    'code' => $d->code.'.'.$action,
                    'name' => str_replace('_', ' ', ucfirst($action)),
                    'checked' => false,
                ];
            }

            $menus[] = [
                'id' => $d->code,
                'name' => $d->name,
                'action' => $actions
            ];
        }

        return view('admin.menu_access.index', compact('menus'));
    }

    public function getPermission(Request $request){
        try {
            $position = EmployeePosition::find($request->get('id'));

            $data = Menu::orderBy('sequence')->orderBy('category')->get();

            $menus = [];
            foreach ($data as $d){
                $actData = explode(',', $d->actions);
                $actions = [];
                foreach ($actData as $action){
                    $actions[] = [
                        'code' => $d->code.'.'.$action,
                        'name' => str_replace('_', ' ', ucfirst($action)),
                        'checked' => $position->hasPermissionTo($d->code.'.'.$action, 'admin'),
                    ];
                }

                $menus[] = [
                    'id' => $d->code,
                    'name' => $d->name,
                    'category' => $d->category,
                    'action' => $actions
                ];
            }

            return response([
                'success' => true,
                'menus' => $menus,
            ]);
        } catch (\Exception $ex){
            Log::channel('menu_access')->error($ex);

            return response([
                'success' => false,
            ]);
        }
    }

    public function setPermission(Request $request){
        try {
            $position = EmployeePosition::find($request->input('position_id'));

            $position->syncPermissions($request->input('permission'));

            Session::flash('success', 'Sukses mengatur akses menu jabatan!');
            return \redirect()->back();
        } catch (\Exception $ex){
            Log::channel('menu_access')->error($ex);

            return \redirect()->back()->withErrors([
                'Terjadi kesalahan di dalam server!'
            ]);
        }
    }
}

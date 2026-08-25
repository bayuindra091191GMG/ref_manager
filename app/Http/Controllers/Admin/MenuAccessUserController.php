<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;

class MenuAccessUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        return view('admin.menu_access_user.index', [
            'menus' => $this->permissionMenus(),
        ]);
    }

    public function getPermission(Request $request)
    {
        try {
            $user = AdminUser::find($request->integer('id'));

            if (!$user) {
                return response([
                    'success' => false,
                    'message' => 'User tidak ditemukan.',
                ], 404);
            }

            $menus = $this->permissionMenus($user);

            return response([
                'success' => true,
                'menus' => $menus,
                'position_menus' => $menus,
            ]);
        } catch (\Throwable $exception) {
            Log::channel('menu_access')->error($exception);

            return response([
                'success' => false,
            ], 500);
        }
    }

    public function setPermission(Request $request)
    {
        try {
            $user = AdminUser::findOrFail((int) $request->input('user_id'));
            $requested = array_values(array_filter(array_map(
                'strval',
                (array) $request->input('permission', [])
            )));

            $permissions = Permission::query()
                ->where('guard_name', 'admin')
                ->whereIn('name', $requested)
                ->pluck('name')
                ->all();

            $user->syncPermissions($permissions);

            Session::flash('success', 'Sukses mengatur permission user!');

            return redirect()->back();
        } catch (\Throwable $exception) {
            Log::channel('menu_access')->error($exception);

            return redirect()->back()->withErrors([
                'Terjadi kesalahan di dalam server!',
            ]);
        }
    }

    private function permissionMenus(?AdminUser $user = null): array
    {
        $categories = [
            'transaction' => 'pos',
            'booking' => 'pos',
            'monitor' => 'pos',
            'voucher' => 'pos',
            'voucher_order' => 'pos',
            'prewedding' => 'pos',
            'branch' => 'master',
            'room' => 'master',
            'banner' => 'master',
            'partner' => 'master',
            'treatment' => 'master',
            'position' => 'master',
            'employee' => 'master',
            'supplier' => 'master',
            'member' => 'master',
            'item' => 'barang',
            'item_category' => 'barang',
            'unit_material' => 'barang',
            'warehouse' => 'barang',
            'inventori' => 'barang',
            'move_in' => 'barang',
            'move_out' => 'barang',
            'stock_card' => 'barang',
            'item_transfer' => 'barang',
            'purchase_request' => 'purchase',
            'purchase_order' => 'purchase',
            'good_receive' => 'purchase',
            'supplier_invoice' => 'purchase',
            'ap' => 'finance',
            'ar' => 'finance',
            'coa' => 'finance',
            'bank_cash_in' => 'finance',
            'bank_cash_out' => 'finance',
            'journal_manual' => 'general_ledger',
            'general_ledger' => 'general_ledger',
            'reporting' => 'report',
            'purchasing' => 'report',
        ];

        $menus = [];

        foreach (Permission::query()->where('guard_name', 'admin')->orderBy('name')->get() as $permission) {
            [$module, $action] = array_pad(explode('.', $permission->name, 2), 2, '');

            if (!isset($menus[$module])) {
                $menus[$module] = [
                    'id' => $module,
                    'name' => ucwords(str_replace('_', ' ', $module)),
                    'category' => $categories[$module] ?? 'master',
                    'action' => [],
                ];
            }

            $menus[$module]['action'][] = [
                'code' => $permission->name,
                'name' => ucwords(str_replace('_', ' ', $action)),
                'checked' => $user?->hasPermissionTo($permission->name) ?? false,
            ];
        }

        return array_values($menus);
    }
}

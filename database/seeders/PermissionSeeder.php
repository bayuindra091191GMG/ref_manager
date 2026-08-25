<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionGroups = [
            'advance_payment' => ['delete', 'edit', 'show'],
            'advance_receive' => ['delete', 'edit', 'show'],
            'ap' => ['add', 'approve', 'delete', 'edit', 'list', 'show'],
            'ar' => ['delete', 'edit', 'show'],
            'attendance' => ['import', 'list'],
            'bank_cash_in' => ['add', 'approve', 'delete', 'edit', 'list', 'show'],
            'bank_cash_out' => ['add', 'approve', 'delete', 'edit', 'list', 'show'],
            'banner' => ['add', 'edit', 'list', 'show'],
            'booking' => ['add', 'approve', 'assign', 'cancel', 'edit', 'finalize', 'list', 'show'],
            'branch' => ['list', 'scope_all'],
            'coa' => ['add', 'delete', 'edit', 'list'],
            'delivery_order' => ['delete', 'edit', 'show'],
            'employee' => ['add', 'delete', 'edit', 'list', 'show'],
            'finance' => ['ap_aging', 'ap_card', 'ap_invoice_list', 'ar_aging', 'ar_card'],
            'general_ledger' => ['balance_sheet', 'cashflow', 'ledger', 'profit_and_loss', 'profit_loss', 'trial_balance'],
            'good_receive' => ['add', 'approve', 'delete', 'edit', 'list', 'show'],
            'inventori' => ['list'],
            'item' => ['add', 'delete', 'edit', 'list'],
            'item_category' => ['add', 'delete', 'edit', 'list', 'show'],
            'item_transfer' => ['add', 'approve', 'delete', 'edit', 'list', 'show'],
            'admin_user' => ['manage'],
            'journal_manual' => ['add', 'approve', 'delete', 'edit', 'list', 'show', 'unapprove'],
            'member' => ['list'],
            'master_banner' => ['add'],
            'monitor' => ['list'],
            'move_in' => ['add', 'approve', 'delete', 'edit', 'list', 'show'],
            'move_out' => ['add', 'approve', 'delete', 'edit', 'list', 'show'],
            'partner' => ['add', 'delete', 'edit', 'list', 'show'],
            'position' => ['add', 'delete', 'edit', 'list', 'show'],
            'prewedding' => ['add', 'approve', 'cancel', 'edit', 'list', 'process', 'show'],
            'purchase_order' => ['add', 'approve', 'delete', 'edit', 'list', 'show'],
            'purchase_request' => ['add', 'approve', 'delete', 'edit', 'list', 'show'],
            'purchasing' => ['good_receive', 'purchase_order', 'purchase_request'],
            'reporting' => ['daily_recap', 'laba_kotor', 'mtdytd', 'terapis', 'trx_commision', 'trx_payment', 'trx_report', 'voucher'],
            'room' => ['add', 'delete', 'edit', 'list'],
            'role' => ['manage'],
            'salary' => ['delete', 'edit', 'show'],
            'sales_invoice' => ['delete', 'edit', 'show'],
            'sales_order' => ['delete', 'edit', 'show'],
            'stock_card' => ['list'],
            'supplier' => ['add', 'delete', 'edit', 'list', 'show'],
            'supplier_invoice' => ['add', 'approve', 'delete', 'edit', 'list', 'show'],
            'transaction' => ['list', 'update_payment', 'void'],
            'transaction_utility' => ['use'],
            'treatment' => ['add', 'delete', 'edit', 'list', 'show'],
            'trx_preview' => ['show'],
            'unit_material' => ['add', 'delete', 'edit', 'list', 'show'],
            'user' => ['permission.manage'],
            'voucher' => ['list'],
            'voucher_order' => ['approve', 'cancel', 'list', 'show'],
            'warehouse' => ['add', 'delete', 'edit', 'list', 'show'],
        ];

        $permissionNames = [];
        foreach ($permissionGroups as $module => $actions) {
            foreach ($actions as $action) {
                $permissionNames[] = $module.'.'.$action;
            }
        }

        $permissions = collect($permissionNames)
            ->unique()
            ->map(fn (string $name) => Permission::findOrCreate($name, 'admin'));

        $superadmin = Role::findOrCreate('superadmin', 'admin');
        $superadmin->syncPermissions($permissions);

        Role::findOrCreate('staff', 'admin');

        $admin = AdminUser::query()
            ->whereRaw('LOWER(username) = ?', ['superadmin'])
            ->where('name', 'Superadmin')
            ->firstOrFail();

        $admin->syncPermissions([]);
        $admin->syncRoles([$superadmin]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Branch;
use App\Models\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $status = Status::findOrNew(1);
        $status->forceFill([
            'id' => 1,
            'description' => 'active',
            'display' => 'Aktif',
        ])->save();

        $branch = Branch::findOrNew(1);
        $branch->forceFill([
            'id' => 1,
            'code' => 'REF-001',
            'name' => 'Reflexology Manager',
            'status_id' => 1,
        ])->save();

        $admin = AdminUser::firstOrNew(['username' => 'superadmin']);
        $admin->forceFill([
            'name' => 'Superadmin',
            'password' => Hash::make('superadmin'),
            'branch_id' => 1,
            'status_id' => 1,
            'is_super' => 1,
            'is_developer' => 1,
        ])->save();
    }
}

<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SuperAdminLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();

        foreach ([
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
            'roles',
            'permissions',
            'admin_users',
            'branches',
            'statuses',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('statuses', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('description')->nullable();
            $table->string('display')->nullable();
        });

        Schema::create('branches', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->text('address')->nullable();
            $table->string('item_unit_mode')->nullable();
            $table->text('image_filename')->nullable();
            $table->string('email_from')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->string('phone')->nullable();
            $table->text('extra_text')->nullable();
            $table->string('voucher_code')->nullable();
            $table->integer('is_oura')->nullable()->default(0);
            $table->decimal('latitude', 8, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->integer('home_service_fee')->nullable()->default(0);
            $table->integer('status_id')->nullable()->default(1);
        });

        Schema::create('admin_users', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->integer('branch_id')->nullable();
            $table->string('username')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token')->nullable();
            $table->boolean('is_super')->nullable()->default(0);
            $table->boolean('is_developer')->nullable()->default(0);
            $table->boolean('is_bod')->nullable()->default(0);
            $table->boolean('is_finance')->nullable()->default(0);
            $table->integer('status_id')->nullable()->default(1);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });

        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        Schema::enableForeignKeyConstraints();

        $this->seed(SuperAdminSeeder::class);
        $this->seed(PermissionSeeder::class);
    }

    public function test_superadmin_is_seeded_with_every_permission(): void
    {
        $admin = AdminUser::where('username', 'superadmin')->firstOrFail();

        $this->assertTrue(Hash::check('superadmin', $admin->password));
        $this->assertTrue($admin->hasRole('superadmin'));
        $this->assertTrue($admin->is_super);
        $this->assertTrue($admin->is_developer);
        $this->assertTrue($admin->can('branch.scope_all'));
        $this->assertTrue($admin->can('customer.delete'));
        $this->assertTrue($admin->can('inventory_in.show'));
        $this->assertSame(Permission::count(), $admin->getAllPermissions()->count());
    }

    public function test_superadmin_can_log_in(): void
    {
        $response = $this->post('/login/submit', [
            'branch_id' => 1,
            'username' => 'superadmin',
            'password' => 'superadmin',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated('admin');
    }

    public function test_superadmin_cannot_log_in_with_wrong_password(): void
    {
        $this->post('/login/submit', [
            'branch_id' => 1,
            'username' => 'superadmin',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest('admin');
    }
}

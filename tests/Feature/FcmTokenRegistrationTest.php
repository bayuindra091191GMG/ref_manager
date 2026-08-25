<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\UserController;
use App\Models\FcmToken;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

class FcmTokenRegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('oauth_access_tokens');
        Schema::dropIfExists('oauth_refresh_tokens');
        Schema::dropIfExists('oauth_personal_access_clients');
        Schema::dropIfExists('oauth_clients');
        Schema::dropIfExists('fcm_tokens');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->integer('status_id')->nullable();
            $table->integer('is_password_set')->nullable();
            $table->date('birthday_date')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('fcm_tokens', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('token')->nullable();
            $table->timestamps();
        });
        Schema::create('oauth_clients', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('owner_type')->nullable();
            $table->string('name');
            $table->string('secret')->nullable();
            $table->string('provider')->nullable();
            $table->text('redirect_uris');
            $table->text('grant_types');
            $table->boolean('revoked')->default(false);
            $table->timestamps();
        });
        Schema::create('oauth_access_tokens', function (Blueprint $table): void {
            $table->string('id', 100)->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('client_id');
            $table->string('name')->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_refresh_tokens', function (Blueprint $table): void {
            $table->string('id', 100)->primary();
            $table->string('access_token_id', 100)->index();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        DB::table('oauth_clients')->insert([
            'name' => 'Test Personal Access Client',
            'secret' => 'test-secret',
            'provider' => 'users',
            'redirect_uris' => json_encode([]),
            'grant_types' => json_encode(['personal_access']),
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::enableForeignKeyConstraints();
    }

    public function test_authenticated_user_can_register_fcm_token(): void
    {
        $user = User::query()->create([
            'name' => 'Customer',
            'phone' => '08123456789',
            'email' => 'customer@example.test',
        ]);

        $request = Request::create('/api/user/fcm/register', 'POST', [
            'token' => 'fcm-token-123',
        ]);
        $request->setUserResolver(fn () => $user);

        $response = (new UserController())->saveFcmToken($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Token FCM berhasil disimpan!', $response->getData(true)['message']);

        $this->assertDatabaseHas('fcm_tokens', [
            'user_id' => $user->id,
            'token' => 'fcm-token-123',
        ]);
    }

    public function test_registering_same_fcm_token_does_not_duplicate_row(): void
    {
        $user = User::query()->create([
            'name' => 'Customer',
            'phone' => '08123456789',
            'email' => 'customer@example.test',
        ]);

        FcmToken::query()->create([
            'user_id' => $user->id,
            'token' => 'fcm-token-123',
        ]);

        $request = Request::create('/api/user/fcm/register', 'POST', [
            'token' => 'fcm-token-123',
        ]);
        $request->setUserResolver(fn () => $user);

        $response = (new UserController())->saveFcmToken($request);

        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame(
            1,
            FcmToken::query()
                ->where('user_id', $user->id)
                ->where('token', 'fcm-token-123')
                ->count()
        );
    }

    public function test_login_without_fcm_token_does_not_create_empty_token(): void
    {
        User::query()->create([
            'name' => 'Customer',
            'phone' => '08123456789',
            'email' => 'customer@example.test',
            'password' => Hash::make('password123'),
            'status_id' => 1,
            'is_password_set' => 1,
        ]);

        $request = Request::create('/api/user/login', 'POST', [
            'phone' => '08123456789',
            'password' => 'password123',
        ]);

        try {
            (new LoginController())->submit($request);
        } catch (LogicException $exception) {
            $this->assertSame('Invalid key supplied', $exception->getMessage());
        }

        $this->assertDatabaseCount('fcm_tokens', 0);
    }
}

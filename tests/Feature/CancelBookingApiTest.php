<?php

namespace Tests\Feature;

use App\Models\BookingHeader;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class CancelBookingApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('fcm_tokens');
        Schema::dropIfExists('booking_headers');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_headers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('status_id')->nullable();
            $table->string('payment_proof')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->boolean('is_settled')->nullable();
            $table->timestamp('payment_at')->nullable();
            $table->string('pg_status')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
        });
        Schema::create('fcm_tokens', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('token')->nullable();
            $table->timestamps();
        });

        Schema::create('user_notifications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('message_id')->nullable();
            $table->string('notif_type')->nullable();
            $table->text('model')->nullable();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->integer('status_id')->nullable();
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    public function test_customer_can_cancel_unpaid_booking_without_payment_proof(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-13 10:15:00', 'Asia/Jakarta'));

        $user = User::query()->create([
            'name' => 'Customer',
            'phone' => '08123456789',
            'email' => 'customer@example.test',
            'password' => 'secret',
        ]);

        $booking = BookingHeader::forceCreate([
            'user_id' => $user->id,
            'status_id' => 3,
            'payment_proof' => null,
            'transaction_id' => null,
            'is_settled' => false,
            'payment_at' => Carbon::parse('2026-06-13 09:15:00', 'Asia/Jakarta'),
            'pg_status' => 'SUCCESS',
        ]);

        config(['auth.defaults.guard' => 'user-api']);
        Auth::shouldUse('user-api');
        $this->be($user, 'user-api');

        $response = $this->postJson('/api/booking/cancel', [
            'booking_id' => $booking->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Booking berhasil dibatalkan!')
            ->assertJsonPath('response.booking_id', $booking->id)
            ->assertJsonPath('response.status_id', 10);

        $booking->refresh();

        $this->assertSame(10, (int) $booking->status_id);
        $this->assertNotNull($booking->canceled_at);
    }

    public function test_customer_cannot_cancel_booking_with_payment_proof(): void
    {
        $user = User::query()->create([
            'name' => 'Customer',
            'phone' => '08123456789',
            'email' => 'customer@example.test',
            'password' => 'secret',
        ]);

        $booking = BookingHeader::forceCreate([
            'user_id' => $user->id,
            'status_id' => 3,
            'payment_proof' => 'proof.png',
            'transaction_id' => null,
            'is_settled' => false,
        ]);

        config(['auth.defaults.guard' => 'user-api']);
        Auth::shouldUse('user-api');
        $this->be($user, 'user-api');
        $response = $this->postJson('/api/booking/cancel', [
            'booking_id' => $booking->id,
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Booking tidak bisa dibatalkan');

        $booking->refresh();

        $this->assertSame(3, (int) $booking->status_id);
        $this->assertNull($booking->canceled_at);
    }
}

<?php

namespace Tests\Feature;

use App\Jobs\SendVoucherCancelledNotification;
use App\Models\OrderVoucherHeader;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CancelVoucherOrderApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('order_voucher_headers');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        Schema::create('order_voucher_headers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->integer('status_id')->nullable();
            $table->string('payment_proof')->nullable();
            $table->timestamp('payment_due_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function test_customer_can_cancel_unpaid_voucher_without_payment_proof(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-19 10:15:00', 'Asia/Jakarta'));
        Bus::fake([SendVoucherCancelledNotification::class]);

        $user = User::query()->create([
            'name' => 'Customer',
            'phone' => '08123456789',
            'email' => 'customer@example.test',
            'password' => 'secret',
        ]);

        $order = OrderVoucherHeader::query()->forceCreate([
            'created_by' => $user->id,
            'status_id' => 3,
            'payment_proof' => null,
            'payment_due_at' => Carbon::parse('2026-06-19 10:45:00', 'Asia/Jakarta'),
        ]);

        $this->be($user);

        $response = $this->postJson('/api/voucher/order/cancel', [
            'voucher_order_id' => $order->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Voucher berhasil dibatalkan!')
            ->assertJsonPath('response.voucher_order_id', $order->id)
            ->assertJsonPath('response.status_id', 10);

        $order->refresh();

        $this->assertSame(10, (int) $order->status_id);
        $this->assertNotNull($order->canceled_at);
        Bus::assertNotDispatched(SendVoucherCancelledNotification::class);
    }

    public function test_customer_cannot_cancel_voucher_with_payment_proof(): void
    {
        $user = User::query()->create([
            'name' => 'Customer',
            'phone' => '08123456789',
            'email' => 'customer@example.test',
            'password' => 'secret',
        ]);

        $order = OrderVoucherHeader::query()->forceCreate([
            'created_by' => $user->id,
            'status_id' => 3,
            'payment_proof' => 'proof.png',
            'payment_due_at' => Carbon::parse('2026-06-19 10:45:00', 'Asia/Jakarta'),
        ]);

        $this->be($user);

        $response = $this->postJson('/api/voucher/order/cancel', [
            'voucher_order_id' => $order->id,
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Voucher tidak bisa dibatalkan');

        $order->refresh();

        $this->assertSame(3, (int) $order->status_id);
        $this->assertNull($order->canceled_at);
    }
}

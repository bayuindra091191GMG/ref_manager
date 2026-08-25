<?php

namespace Tests\Feature;

use App\Jobs\CancelExpiredVoucherPayments;
use App\Jobs\SendVoucherCancelledNotification;
use App\Models\OrderVoucherHeader;
use App\Models\OrderVoucherPayment;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon as LaravelCarbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CancelExpiredVoucherPaymentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::disconnect();
        DB::reconnect();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('order_voucher_payments');
        Schema::dropIfExists('order_voucher_headers');

        Schema::create('order_voucher_headers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('code')->nullable();
            $table->integer('status_id')->nullable();
            $table->string('payment_proof')->nullable();
            $table->timestamp('payment_due_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->string('pg_status')->nullable();
            $table->timestamps();
        });

        Schema::create('order_voucher_payments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('header_id')->nullable();
            $table->string('pg_status')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        LaravelCarbon::setTestNow();

        parent::tearDown();
    }

    public function test_cancels_expired_unpaid_voucher_orders_without_touching_pg_status(): void
    {
        $now = Carbon::parse('2026-06-19 10:00:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);
        LaravelCarbon::setTestNow($now);
        Bus::fake([SendVoucherCancelledNotification::class]);

        $expiredOrder = OrderVoucherHeader::query()->forceCreate([
            'created_by' => 1,
            'code' => 'VCR/6/0001',
            'status_id' => 3,
            'payment_proof' => null,
            'payment_due_at' => $now->copy()->subMinute()->toDateTimeString(),
            'pg_status' => 'MANUAL_PENDING',
        ]);
        OrderVoucherPayment::query()->create([
            'header_id' => $expiredOrder->id,
            'pg_status' => 'MANUAL_PENDING',
        ]);

        $futureOrder = OrderVoucherHeader::query()->forceCreate([
            'created_by' => 1,
            'code' => 'VCR/6/0002',
            'status_id' => 3,
            'payment_proof' => null,
            'payment_due_at' => $now->copy()->addMinute()->toDateTimeString(),
            'pg_status' => 'MANUAL_PENDING',
        ]);

        $proofOrder = OrderVoucherHeader::query()->forceCreate([
            'created_by' => 1,
            'code' => 'VCR/6/0003',
            'status_id' => 3,
            'payment_proof' => 'proof.jpg',
            'payment_due_at' => $now->copy()->subMinute()->toDateTimeString(),
            'pg_status' => 'MANUAL_PENDING',
        ]);

        (new CancelExpiredVoucherPayments())->handle();

        $expiredOrder->refresh();
        $futureOrder->refresh();
        $proofOrder->refresh();

        $this->assertSame(10, (int) $expiredOrder->status_id);
        $this->assertNotNull($expiredOrder->canceled_at);
        $this->assertSame('MANUAL_PENDING', $expiredOrder->pg_status);
        $this->assertDatabaseHas('order_voucher_payments', [
            'header_id' => $expiredOrder->id,
            'pg_status' => 'MANUAL_PENDING',
        ]);

        $this->assertSame(3, (int) $futureOrder->status_id);
        $this->assertNull($futureOrder->canceled_at);
        $this->assertSame(3, (int) $proofOrder->status_id);
        $this->assertNull($proofOrder->canceled_at);
        Bus::assertDispatched(SendVoucherCancelledNotification::class, 1);
    }
}

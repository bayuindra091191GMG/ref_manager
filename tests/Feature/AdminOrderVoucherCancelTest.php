<?php

namespace Tests\Feature;

use App\Jobs\SendVoucherCancelledNotification;
use App\Http\Controllers\Admin\OrderVoucherController;
use App\Models\OrderVoucherHeader;
use App\Models\OrderVoucherPayment;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon as LaravelCarbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminOrderVoucherCancelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('order_voucher_payments');
        Schema::dropIfExists('order_voucher_headers');

        Schema::create('order_voucher_headers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('code')->nullable();
            $table->integer('status_id')->nullable();
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('order_voucher_payments');
        Schema::dropIfExists('order_voucher_headers');
        Schema::enableForeignKeyConstraints();

        Carbon::setTestNow();
        LaravelCarbon::setTestNow();

        parent::tearDown();
    }

    public function test_admin_cancel_does_not_touch_pg_status(): void
    {
        $now = Carbon::parse('2026-06-19 10:00:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);
        LaravelCarbon::setTestNow($now);
        Bus::fake([SendVoucherCancelledNotification::class]);

        $order = OrderVoucherHeader::query()->forceCreate([
            'created_by' => 1,
            'code' => 'VCR/2026/06/0001',
            'status_id' => 3,
            'pg_status' => 'MANUAL_PENDING',
        ]);
        OrderVoucherPayment::query()->create([
            'header_id' => $order->id,
            'pg_status' => 'MANUAL_PENDING',
        ]);

        $request = Request::create('/admin/voucher_order/cancel', 'POST', [
            'cancelled_order_voucher_id' => $order->id,
        ]);

        $response = (new OrderVoucherController())->cancel($request);

        $this->assertTrue($response->isRedirect());
        $this->assertDatabaseHas('order_voucher_headers', [
            'id' => $order->id,
            'status_id' => 10,
            'pg_status' => 'MANUAL_PENDING',
        ]);
        $this->assertDatabaseHas('order_voucher_payments', [
            'header_id' => $order->id,
            'pg_status' => 'MANUAL_PENDING',
        ]);

        $order->refresh();
        $this->assertNotNull($order->canceled_at);
        Bus::assertDispatched(SendVoucherCancelledNotification::class, 1);
    }
}

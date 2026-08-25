<?php

namespace Tests\Feature;

use App\Jobs\SendUnpaidVoucherPaymentReminder;
use App\Models\FcmToken;
use App\Models\OrderVoucherHeader;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon as LaravelCarbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UnpaidVoucherPaymentReminderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('fcm_tokens');
        Schema::dropIfExists('order_voucher_headers');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('order_voucher_headers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('code')->nullable();
            $table->integer('status_id')->nullable();
            $table->string('payment_proof')->nullable();
            $table->timestamp('payment_due_at')->nullable();
            $table->timestamp('payment_reminded_at')->nullable();
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

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        LaravelCarbon::setTestNow();

        parent::tearDown();
    }

    public function test_sends_payment_reminder_for_unpaid_voucher_due_within_fifteen_minutes(): void
    {
        $now = Carbon::parse('2026-06-16 10:00:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);
        LaravelCarbon::setTestNow($now);

        $user = User::query()->create([
            'name' => 'Customer',
            'phone' => '08123456789',
            'email' => 'customer@example.test',
        ]);

        FcmToken::query()->create(['user_id' => $user->id, 'token' => 'token-valid']);
        FcmToken::query()->create(['user_id' => $user->id, 'token' => null]);
        FcmToken::query()->create(['user_id' => $user->id, 'token' => '']);

        $order = OrderVoucherHeader::forceCreate([
            'created_by' => $user->id,
            'code' => 'VCR/2026/06/00001',
            'status_id' => 3,
            'payment_proof' => null,
            'payment_due_at' => $now->copy()->addMinutes(15)->toDateTimeString(),
            'payment_reminded_at' => null,
        ]);

        $job = new class extends SendUnpaidVoucherPaymentReminder {
            public array $sent = [];

            protected function sendPushNotification(string $token, array $notification, array $data): void
            {
                $this->sent[] = compact('token', 'notification', 'data');
            }
        };

        $job->handle();

        $order->refresh();

        $this->assertNotNull($order->payment_reminded_at);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'message_id' => 'voucher-payment-reminder-'.$order->id,
            'notif_type' => 'voucher_payment_due_reminder',
            'title' => 'Batas Pembayaran Voucher Hampir Berakhir',
            'status_id' => 1,
        ]);

        $notification = $this->getConnection()
            ->table('user_notifications')
            ->where('message_id', 'voucher-payment-reminder-'.$order->id)
            ->first();
        $model = json_decode($notification->model, true);

        $this->assertStringContainsString('VCR/2026/06/00001', $notification->body);
        $this->assertSame($order->id, $model['id']);
        $this->assertSame($order->id, $model['voucher_order_id']);
        $this->assertSame('VCR/2026/06/00001', $model['order_no']);
        $this->assertSame('voucher_payment_due_reminder', $model['event']);
        $this->assertCount(1, $job->sent);
        $this->assertSame('token-valid', $job->sent[0]['token']);
        $this->assertSame('voucher_payment_due_reminder', $job->sent[0]['data']['event']);
    }

    public function test_does_not_send_duplicate_reminder_after_voucher_is_marked_reminded(): void
    {
        $now = Carbon::parse('2026-06-16 10:00:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);
        LaravelCarbon::setTestNow($now);

        $user = User::query()->create(['name' => 'Customer']);
        FcmToken::query()->create(['user_id' => $user->id, 'token' => 'token-valid']);

        OrderVoucherHeader::forceCreate([
            'created_by' => $user->id,
            'code' => 'VCR/2026/06/00002',
            'status_id' => 3,
            'payment_proof' => null,
            'payment_due_at' => $now->copy()->addMinutes(15)->toDateTimeString(),
            'payment_reminded_at' => $now->copy()->subMinute()->toDateTimeString(),
        ]);

        $job = new class extends SendUnpaidVoucherPaymentReminder {
            public array $sent = [];

            protected function sendPushNotification(string $token, array $notification, array $data): void
            {
                $this->sent[] = compact('token', 'notification', 'data');
            }
        };

        $job->handle();

        $this->assertDatabaseCount('user_notifications', 0);
        $this->assertCount(0, $job->sent);
    }
}

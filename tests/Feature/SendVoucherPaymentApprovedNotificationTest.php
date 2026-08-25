<?php

namespace Tests\Feature;

use App\Jobs\SendVoucherPaymentApprovedNotification;
use App\Models\FcmToken;
use App\Models\OrderVoucherHeader;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SendVoucherPaymentApprovedNotificationTest extends TestCase
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
            $table->timestamp('payment_approved_at')->nullable();
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

    public function test_saves_and_sends_payment_approved_voucher_notification_once(): void
    {
        $user = User::query()->create(['name' => 'Customer']);
        FcmToken::query()->create(['user_id' => $user->id, 'token' => 'token-valid']);
        FcmToken::query()->create(['user_id' => $user->id, 'token' => null]);
        FcmToken::query()->create(['user_id' => $user->id, 'token' => '']);

        $order = OrderVoucherHeader::forceCreate([
            'created_by' => $user->id,
            'code' => 'VCR/2026/06/0001',
            'status_id' => 20,
            'payment_approved_at' => '2026-06-19 10:00:00',
        ]);

        $job = new class($order) extends SendVoucherPaymentApprovedNotification {
            public array $sent = [];

            protected function sendPushNotification(string $token, array $notification, array $data): void
            {
                $this->sent[] = compact('token', 'notification', 'data');
            }
        };

        $job->handle();
        $job->handle();

        $this->assertDatabaseCount('user_notifications', 1);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'message_id' => 'voucher-payment-approved-'.$order->id,
            'notif_type' => 'voucher_payment_approved',
            'title' => 'Pembayaran Voucher Disetujui',
            'status_id' => 1,
        ]);

        $notification = $this->getConnection()
            ->table('user_notifications')
            ->where('message_id', 'voucher-payment-approved-'.$order->id)
            ->first();
        $model = json_decode($notification->model, true);

        $this->assertStringContainsString('VCR/2026/06/0001', $notification->body);
        $this->assertSame($order->id, $model['id']);
        $this->assertSame($order->id, $model['voucher_order_id']);
        $this->assertSame('VCR/2026/06/0001', $model['order_no']);
        $this->assertSame('voucher', $model['type']);
        $this->assertSame('voucher_payment_approved', $model['event']);
        $this->assertCount(1, $job->sent);
        $this->assertSame(['token-valid'], array_column($job->sent, 'token'));
        $this->assertSame('voucher_payment_approved', $job->sent[0]['data']['event']);
    }
}

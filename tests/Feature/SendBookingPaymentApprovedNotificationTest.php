<?php

namespace Tests\Feature;

use App\Jobs\SendBookingPaymentApprovedNotification;
use App\Models\BookingHeader;
use App\Models\FcmToken;
use App\Models\PreweddingHeader;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SendBookingPaymentApprovedNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('fcm_tokens');
        Schema::dropIfExists('prewedding_headers');
        Schema::dropIfExists('booking_headers');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_headers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type')->nullable();
            $table->string('code')->nullable();
            $table->integer('status_id')->nullable();
            $table->timestamps();
        });

        Schema::create('prewedding_headers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->string('code')->nullable();
            $table->integer('status_id')->nullable();
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

    public function test_saves_and_sends_payment_approved_booking_notification_once(): void
    {
        $user = User::query()->create(['name' => 'Customer']);
        FcmToken::query()->create(['user_id' => $user->id, 'token' => 'token-valid']);
        FcmToken::query()->create(['user_id' => $user->id, 'token' => null]);
        FcmToken::query()->create(['user_id' => $user->id, 'token' => '']);

        $booking = BookingHeader::forceCreate([
            'user_id' => $user->id,
            'type' => 'booking',
            'code' => 'BOOK/6/0001',
            'status_id' => 9,
        ]);

        $job = new class($booking) extends SendBookingPaymentApprovedNotification {
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
            'message_id' => 'booking-payment-approved-booking-'.$booking->id,
            'notif_type' => 'booking_payment_approved',
            'title' => 'Pembayaran Booking Disetujui',
            'status_id' => 1,
        ]);

        $notification = $this->getConnection()
            ->table('user_notifications')
            ->where('message_id', 'booking-payment-approved-booking-'.$booking->id)
            ->first();
        $model = json_decode($notification->model, true);

        $this->assertStringContainsString('BOOK/6/0001', $notification->body);
        $this->assertSame($booking->id, $model['id']);
        $this->assertSame($booking->id, $model['booking_id']);
        $this->assertSame('BOOK/6/0001', $model['order_no']);
        $this->assertSame('booking', $model['type']);
        $this->assertSame('booking_payment_approved', $model['event']);
        $this->assertCount(1, $job->sent);
        $this->assertSame('token-valid', $job->sent[0]['token']);
        $this->assertSame('booking_payment_approved', $job->sent[0]['data']['event']);
    }

    public function test_saves_and_sends_payment_approved_pranikah_notification(): void
    {
        $user = User::query()->create(['name' => 'Customer']);
        FcmToken::query()->create(['user_id' => $user->id, 'token' => 'token-pranikah']);

        $booking = PreweddingHeader::forceCreate([
            'member_id' => $user->id,
            'code' => 'PW0001',
            'status_id' => 13,
        ]);

        $job = new class($booking) extends SendBookingPaymentApprovedNotification {
            public array $sent = [];

            protected function sendPushNotification(string $token, array $notification, array $data): void
            {
                $this->sent[] = compact('token', 'notification', 'data');
            }
        };

        $job->handle();

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'message_id' => 'booking-payment-approved-pranikah-'.$booking->id,
            'notif_type' => 'booking_payment_approved',
            'title' => 'Pembayaran Booking Disetujui',
            'status_id' => 1,
        ]);

        $notification = $this->getConnection()
            ->table('user_notifications')
            ->where('message_id', 'booking-payment-approved-pranikah-'.$booking->id)
            ->first();
        $model = json_decode($notification->model, true);

        $this->assertStringContainsString('PW0001', $notification->body);
        $this->assertSame($booking->id, $model['id']);
        $this->assertSame($booking->id, $model['booking_id']);
        $this->assertSame('PW0001', $model['order_no']);
        $this->assertSame('pranikah', $model['type']);
        $this->assertSame('booking_payment_approved', $model['event']);
        $this->assertCount(1, $job->sent);
        $this->assertSame('token-pranikah', $job->sent[0]['token']);
    }
}

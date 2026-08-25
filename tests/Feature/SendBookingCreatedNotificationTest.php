<?php

namespace Tests\Feature;

use App\Jobs\SendBookingCreatedNotification;
use App\Models\BookingHeader;
use App\Models\FcmToken;
use App\Models\PreweddingHeader;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SendBookingCreatedNotificationTest extends TestCase
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
            $table->date('booking_date')->nullable();
            $table->string('booking_time')->nullable();
            $table->timestamp('payment_due_at')->nullable();
            $table->timestamps();
        });

        Schema::create('prewedding_headers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->string('code')->nullable();
            $table->timestamps();
        });

        Schema::create('fcm_tokens', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('token');
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

    public function test_saves_and_sends_notification_for_created_booking(): void
    {
        $user = User::query()->create([
            'name' => 'Customer',
            'phone' => '08123456789',
            'email' => 'customer@example.test',
        ]);

        FcmToken::query()->create(['user_id' => $user->id, 'token' => 'token-a']);
        FcmToken::query()->create(['user_id' => $user->id, 'token' => 'token-b']);

        $booking = BookingHeader::forceCreate([
            'user_id' => $user->id,
            'type' => 'booking',
            'code' => 'BOOK/6/0001',
            'booking_date' => '2026-06-16',
            'booking_time' => '10:00 - 11:00',
            'payment_due_at' => '2026-06-15 11:00:00',
        ]);

        $job = new class($booking) extends SendBookingCreatedNotification {
            public array $sent = [];

            protected function sendPushNotification(string $token, array $notification, array $data): void
            {
                $this->sent[] = compact('token', 'notification', 'data');
            }
        };

        $job->handle();

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'notif_type' => 'booking_created',
            'title' => 'Booking Berhasil Dibuat',
            'status_id' => 1,
        ]);

        $notification = $this->getConnection()
            ->table('user_notifications')
            ->where('message_id', 'booking-created-booking-'.$booking->id)
            ->first();

        $this->assertStringContainsString('BOOK/6/0001', $notification->body);
        $this->assertStringContainsString('Silakan unggah bukti pembayaran', $notification->body);

        $model = json_decode($notification->model, true);

        $this->assertSame($booking->id, $model['id']);
        $this->assertSame('BOOK/6/0001', $model['order_no']);
        $this->assertSame('booking', $model['type']);
        $this->assertSame('booking_created', $model['event']);
        $this->assertSame(2, count($job->sent));
        $this->assertSame(['token-a', 'token-b'], array_column($job->sent, 'token'));
        $this->assertSame('booking_created', $job->sent[0]['data']['event']);
    }

    public function test_saves_and_sends_notification_for_created_pranikah_booking(): void
    {
        $user = User::query()->create([
            'name' => 'Customer',
            'phone' => '08123456789',
            'email' => 'customer@example.test',
        ]);

        FcmToken::query()->create(['user_id' => $user->id, 'token' => 'token-pranikah']);

        $booking = PreweddingHeader::forceCreate([
            'member_id' => $user->id,
            'code' => 'PW0001',
        ]);

        $job = new class($booking, 'pranikah') extends SendBookingCreatedNotification {
            public array $sent = [];

            protected function sendPushNotification(string $token, array $notification, array $data): void
            {
                $this->sent[] = compact('token', 'notification', 'data');
            }
        };

        $job->handle();

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'notif_type' => 'booking_created',
            'title' => 'Booking Berhasil Dibuat',
            'status_id' => 1,
        ]);

        $notification = $this->getConnection()
            ->table('user_notifications')
            ->where('message_id', 'booking-created-pranikah-'.$booking->id)
            ->first();
        $model = json_decode($notification->model, true);

        $this->assertStringContainsString('PW0001', $notification->body);
        $this->assertSame($booking->id, $model['id']);
        $this->assertSame('PW0001', $model['order_no']);
        $this->assertSame('pranikah', $model['type']);
        $this->assertSame('booking_created', $model['event']);
        $this->assertSame('token-pranikah', $job->sent[0]['token']);
    }
}

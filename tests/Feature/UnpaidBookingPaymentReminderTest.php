<?php

namespace Tests\Feature;

use App\Jobs\SendUnpaidBookingPaymentReminder;
use App\Models\BookingHeader;
use App\Models\FcmToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon as LaravelCarbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UnpaidBookingPaymentReminderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('fcm_tokens');
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
            $table->string('payment_proof')->nullable();
            $table->boolean('is_settled')->nullable();
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

    public function test_sends_payment_reminder_for_unpaid_booking_due_within_fifteen_minutes(): void
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

        $booking = BookingHeader::forceCreate([
            'user_id' => $user->id,
            'type' => 'booking',
            'code' => 'BOOK/6/0001',
            'status_id' => 3,
            'payment_proof' => null,
            'is_settled' => true,
            'payment_due_at' => $now->copy()->addMinutes(15)->toDateTimeString(),
            'payment_reminded_at' => null,
        ]);

        $job = new class extends SendUnpaidBookingPaymentReminder {
            public array $sent = [];

            protected function sendPushNotification(string $token, array $notification, array $data): void
            {
                $this->sent[] = compact('token', 'notification', 'data');
            }
        };

        $job->handle();

        $booking->refresh();

        $this->assertNotNull($booking->payment_reminded_at);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'message_id' => 'booking-payment-reminder-'.$booking->id,
            'notif_type' => 'booking_payment_due_reminder',
            'title' => 'Batas Pembayaran Booking Hampir Berakhir',
            'status_id' => 1,
        ]);

        $notification = $this->getConnection()
            ->table('user_notifications')
            ->where('message_id', 'booking-payment-reminder-'.$booking->id)
            ->first();
        $model = json_decode($notification->model, true);

        $this->assertStringContainsString('BOOK/6/0001', $notification->body);
        $this->assertSame($booking->id, $model['id']);
        $this->assertSame('BOOK/6/0001', $model['order_no']);
        $this->assertSame('booking_payment_due_reminder', $model['event']);
        $this->assertCount(1, $job->sent);
        $this->assertSame('token-valid', $job->sent[0]['token']);
        $this->assertSame('booking_payment_due_reminder', $job->sent[0]['data']['event']);
    }

    public function test_does_not_send_duplicate_reminder_after_booking_is_marked_reminded(): void
    {
        $now = Carbon::parse('2026-06-16 10:00:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);
        LaravelCarbon::setTestNow($now);

        $user = User::query()->create(['name' => 'Customer']);
        FcmToken::query()->create(['user_id' => $user->id, 'token' => 'token-valid']);

        BookingHeader::forceCreate([
            'user_id' => $user->id,
            'type' => 'booking',
            'code' => 'BOOK/6/0002',
            'status_id' => 3,
            'payment_proof' => null,
            'is_settled' => false,
            'payment_due_at' => $now->copy()->addMinutes(15)->toDateTimeString(),
            'payment_reminded_at' => $now->copy()->subMinute()->toDateTimeString(),
        ]);

        $job = new class extends SendUnpaidBookingPaymentReminder {
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

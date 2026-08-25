<?php

namespace App\Jobs;

use App\Libs\NotificationUtilities;
use App\Models\BookingHeader;
use App\Models\FcmToken;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendUnpaidBookingPaymentReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = Carbon::now('Asia/Jakarta');
        $reminderLimit = $now->copy()->addMinutes(15);
        $nowString = $now->toDateTimeString();
        $reminderLimitString = $reminderLimit->toDateTimeString();

        BookingHeader::query()
            ->where('status_id', 3)
            ->whereNull('payment_reminded_at')
            ->whereNotNull('payment_due_at')
            ->where('payment_due_at', '>', $nowString)
            ->where('payment_due_at', '<=', $reminderLimitString)
            ->where(function ($query): void {
                $query->whereNull('payment_proof')
                    ->orWhere('payment_proof', '');
            })
            ->orderBy('id')
            ->chunkById(100, function ($bookings) use ($now): void {
                foreach ($bookings as $booking) {
                    $payload = DB::transaction(function () use ($booking, $now): ?array {
                        $bookingHeader = BookingHeader::query()
                            ->whereKey($booking->id)
                            ->lockForUpdate()
                            ->first();

                        if (! $this->isEligibleForReminder($bookingHeader, $now)) {
                            return null;
                        }

                        $payload = $this->buildNotificationPayload($bookingHeader);

                        UserNotification::query()->firstOrCreate(
                            ['message_id' => 'booking-payment-reminder-'.$bookingHeader->id],
                            [
                                'user_id' => $bookingHeader->user_id,
                                'notif_type' => 'booking_payment_due_reminder',
                                'model' => json_encode($payload['model']),
                                'title' => $payload['notification']['title'],
                                'body' => $payload['notification']['body'],
                                'status_id' => 1,
                            ]
                        );

                        $bookingHeader->payment_reminded_at = $now;
                        $bookingHeader->save();

                        return $payload;
                    });

                    if ($payload === null) {
                        continue;
                    }

                    $this->sendReminder($payload);
                }
            });
    }

    private function isEligibleForReminder(?BookingHeader $bookingHeader, Carbon $now): bool
    {
        if ($bookingHeader === null) {
            return false;
        }

        if ((int) $bookingHeader->status_id !== 3) {
            return false;
        }

        if ($bookingHeader->payment_reminded_at !== null) {
            return false;
        }

        if ($bookingHeader->payment_due_at === null) {
            return false;
        }

        $paymentDueAt = Carbon::parse($bookingHeader->payment_due_at, 'Asia/Jakarta');

        if ($paymentDueAt->lessThanOrEqualTo($now) || $paymentDueAt->greaterThan($now->copy()->addMinutes(15))) {
            return false;
        }

        if (! empty($bookingHeader->payment_proof)) {
            return false;
        }

        return true;
    }

    private function buildNotificationPayload(BookingHeader $booking): array
    {
        $orderNo = $booking->code ?? '-';
        $dueTime = Carbon::parse($booking->payment_due_at, 'Asia/Jakarta')->format('H:i');
        $title = 'Batas Pembayaran Booking Hampir Berakhir';
        $body = 'Booking Anda ('.$orderNo.') belum dibayar. Mohon unggah bukti pembayaran sebelum '.$dueTime.'.';
        $model = [
            'id' => $booking->id,
            'booking_id' => $booking->id,
            'order_no' => $orderNo,
            'type' => $booking->type ?? 'booking',
            'event' => 'booking_payment_due_reminder',
        ];

        return [
            'user_id' => $booking->user_id,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => [
                'title' => $title,
                'body' => $body,
                'type' => 'booking',
                'event' => 'booking_payment_due_reminder',
                'model' => $model,
            ],
            'model' => $model,
        ];
    }

    private function sendReminder(array $payload): void
    {
        $tokens = FcmToken::query()
            ->where('user_id', $payload['user_id'])
            ->whereNotNull('token')
            ->where('token', '!=', '')
            ->get();

        foreach ($tokens as $token) {
            Log::channel('firebase')->error('Sending Booking Payment Reminder Notification : '.$token->token);
            $this->sendPushNotification($token->token, $payload['notification'], $payload['data']);
        }
    }

    protected function sendPushNotification(string $token, array $notification, array $data): void
    {
        NotificationUtilities::sendNotification($token, $notification, $data);
    }
}

<?php

namespace App\Jobs;

use App\Libs\NotificationUtilities;
use App\Models\BookingHeader;
use App\Models\FcmToken;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBookingCancelledNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected BookingHeader $booking,
        protected string $source = 'customer',
    ) {
    }

    public function handle(): void
    {
        if (empty($this->booking->user_id)) {
            return;
        }

        $payload = $this->buildNotificationPayload();

        $notification = UserNotification::query()->firstOrCreate(
            ['message_id' => 'booking-cancelled-'.$this->booking->id],
            [
                'user_id' => $this->booking->user_id,
                'notif_type' => 'booking_cancelled',
                'model' => json_encode($payload['model']),
                'title' => $payload['notification']['title'],
                'body' => $payload['notification']['body'],
                'status_id' => 1,
            ]
        );

        if (! $notification->wasRecentlyCreated) {
            return;
        }

        $tokens = FcmToken::query()
            ->where('user_id', $this->booking->user_id)
            ->whereNotNull('token')
            ->where('token', '!=', '')
            ->get();

        foreach ($tokens as $token) {
            Log::channel('firebase')->error('Sending Booking Cancelled Notification : '.$token->token);
            $this->sendPushNotification($token->token, $payload['notification'], $payload['data']);
        }
    }

    private function buildNotificationPayload(): array
    {
        $orderNo = $this->booking->code ?? '-';
        [$title, $body] = $this->resolveMessage($orderNo);
        $model = [
            'id' => $this->booking->id,
            'booking_id' => $this->booking->id,
            'order_no' => $orderNo,
            'type' => $this->booking->type ?? 'booking',
            'event' => 'booking_cancelled',
            'source' => $this->source,
        ];

        return [
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => [
                'title' => $title,
                'body' => $body,
                'type' => 'booking',
                'event' => 'booking_cancelled',
                'model' => $model,
            ],
            'model' => $model,
        ];
    }

    private function resolveMessage(string $orderNo): array
    {
        if ($this->source === 'admin') {
            return [
                'Booking Dibatalkan oleh Admin',
                'Booking Anda ('.$orderNo.') dibatalkan oleh admin. Hubungi customer service jika perlu bantuan.',
            ];
        }

        if ($this->source === 'system_expired_payment') {
            return [
                'Booking Dibatalkan Otomatis',
                'Booking Anda ('.$orderNo.') dibatalkan karena melewati batas waktu pembayaran.',
            ];
        }

        return [
            'Booking Berhasil Dibatalkan',
            'Booking Anda ('.$orderNo.') telah dibatalkan.',
        ];
    }

    protected function sendPushNotification(string $token, array $notification, array $data): void
    {
        NotificationUtilities::sendNotification($token, $notification, $data);
    }
}

<?php

namespace App\Jobs;

use App\Libs\NotificationUtilities;
use App\Models\BookingHeader;
use App\Models\FcmToken;
use App\Models\PreweddingHeader;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBookingPaymentApprovedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected BookingHeader|PreweddingHeader $booking,
    ) {
    }

    public function handle(): void
    {
        $userId = $this->resolveUserId();

        if (empty($userId)) {
            return;
        }

        $type = $this->resolveBookingType();
        $payload = $this->buildNotificationPayload($type);

        $notification = UserNotification::query()->firstOrCreate(
            ['message_id' => 'booking-payment-approved-'.$type.'-'.$this->booking->id],
            [
                'user_id' => $userId,
                'notif_type' => 'booking_payment_approved',
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
            ->where('user_id', $userId)
            ->whereNotNull('token')
            ->where('token', '!=', '')
            ->get();

        foreach ($tokens as $token) {
            Log::channel('firebase')->error('Sending Booking Payment Approved Notification : '.$token->token);
            $this->sendPushNotification($token->token, $payload['notification'], $payload['data']);
        }
    }

    private function buildNotificationPayload(string $type): array
    {
        $orderNo = $this->booking->code ?? '-';
        $title = 'Pembayaran Booking Disetujui';
        $body = 'Bukti pembayaran booking Anda ('.$orderNo.') telah disetujui. Booking Anda sudah dikonfirmasi.';
        $model = [
            'id' => $this->booking->id,
            'booking_id' => $this->booking->id,
            'order_no' => $orderNo,
            'type' => $type,
            'event' => 'booking_payment_approved',
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
                'event' => 'booking_payment_approved',
                'model' => $model,
            ],
            'model' => $model,
        ];
    }

    private function resolveUserId(): ?int
    {
        if ($this->booking instanceof PreweddingHeader) {
            return $this->booking->member_id;
        }

        return $this->booking->user_id;
    }

    private function resolveBookingType(): string
    {
        if ($this->booking instanceof PreweddingHeader) {
            return 'pranikah';
        }

        return $this->booking->type ?? 'booking';
    }

    protected function sendPushNotification(string $token, array $notification, array $data): void
    {
        NotificationUtilities::sendNotification($token, $notification, $data);
    }
}

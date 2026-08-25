<?php

namespace App\Jobs;

use App\Libs\NotificationUtilities;
use App\Models\BookingHeader;
use App\Models\FcmToken;
use App\Models\PreweddingHeader;
use App\Models\UserNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBookingCreatedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected BookingHeader|PreweddingHeader $booking,
        protected ?string $bookingType = null,
    ) {
    }

    public function handle(): void
    {
        $userId = $this->resolveUserId();

        if (empty($userId)) {
            return;
        }

        $type = $this->resolveBookingType();
        $orderNo = $this->booking->code ?? '-';
        $title = 'Booking Berhasil Dibuat';
        $body = 'Booking Anda ('.$orderNo.') berhasil dibuat. Silakan unggah bukti pembayaran sebelum batas waktu pembayaran.';
        $model = [
            'id' => $this->booking->id,
            'order_no' => $orderNo,
            'type' => $type,
            'event' => 'booking_created',
        ];

        UserNotification::query()->create([
            'user_id' => $userId,
            'message_id' => 'booking-created-'.$type.'-'.$this->booking->id,
            'notif_type' => 'booking_created',
            'model' => json_encode($model),
            'title' => $title,
            'body' => $body,
            'status_id' => 1,
        ]);

        $notification = [
            'title' => $title,
            'body' => $body,
        ];

        $data = [
            'title' => $title,
            'body' => $body,
            'type' => 'booking',
            'event' => 'booking_created',
            'model' => $model,
        ];

        $tokens = FcmToken::query()->where('user_id', $userId)->get();

        foreach ($tokens as $token) {
            Log::channel('firebase')->error('Sending Booking Created Notification : '.$token->token);
            $this->sendPushNotification($token->token, $notification, $data);
        }
    }

    protected function sendPushNotification(string $token, array $notification, array $data): void
    {
        NotificationUtilities::sendNotification($token, $notification, $data);
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
        if (! empty($this->bookingType)) {
            return $this->bookingType;
        }

        if ($this->booking instanceof PreweddingHeader) {
            return 'pranikah';
        }

        return $this->booking->type ?? 'booking';
    }
}

<?php

namespace App\Jobs;

use App\Libs\NotificationUtilities;
use App\Models\FcmToken;
use App\Models\OrderVoucherHeader;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendVoucherCancelledNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected OrderVoucherHeader $order,
        protected string $source = 'admin',
    ) {
    }

    public function handle(): void
    {
        if (empty($this->order->created_by)) {
            return;
        }

        $payload = $this->buildNotificationPayload();

        $notification = UserNotification::query()->firstOrCreate(
            ['message_id' => 'voucher-cancelled-'.$this->order->id],
            [
                'user_id' => $this->order->created_by,
                'notif_type' => 'voucher_cancelled',
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
            ->where('user_id', $this->order->created_by)
            ->whereNotNull('token')
            ->where('token', '!=', '')
            ->get();

        foreach ($tokens as $token) {
            Log::channel('firebase')->error('Sending Voucher Cancelled Notification : '.$token->token);
            $this->sendPushNotification($token->token, $payload['notification'], $payload['data']);
        }
    }

    private function buildNotificationPayload(): array
    {
        $orderNo = $this->order->code ?? '-';
        [$title, $body] = $this->resolveMessage($orderNo);
        $model = [
            'id' => $this->order->id,
            'voucher_order_id' => $this->order->id,
            'order_no' => $orderNo,
            'type' => 'voucher',
            'event' => 'voucher_cancelled',
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
                'type' => 'voucher',
                'event' => 'voucher_cancelled',
                'model' => $model,
            ],
            'model' => $model,
        ];
    }

    private function resolveMessage(string $orderNo): array
    {
        if ($this->source === 'system_expired_payment') {
            return [
                'Voucher Dibatalkan Otomatis',
                'Transaksi voucher Anda ('.$orderNo.') dibatalkan karena melewati batas waktu pembayaran.',
            ];
        }

        return [
            'Voucher Dibatalkan oleh Admin',
            'Transaksi voucher Anda ('.$orderNo.') dibatalkan oleh admin. Hubungi customer service jika perlu bantuan.',
        ];
    }

    protected function sendPushNotification(string $token, array $notification, array $data): void
    {
        NotificationUtilities::sendNotification($token, $notification, $data);
    }
}

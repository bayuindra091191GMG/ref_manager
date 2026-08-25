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

class SendVoucherPaymentApprovedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected OrderVoucherHeader $order,
    ) {
    }

    public function handle(): void
    {
        if (empty($this->order->created_by)) {
            return;
        }

        $payload = $this->buildNotificationPayload();

        $notification = UserNotification::query()->firstOrCreate(
            ['message_id' => 'voucher-payment-approved-'.$this->order->id],
            [
                'user_id' => $this->order->created_by,
                'notif_type' => 'voucher_payment_approved',
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
            Log::channel('firebase')->error('Sending Voucher Payment Approved Notification : '.$token->token);
            $this->sendPushNotification($token->token, $payload['notification'], $payload['data']);
        }
    }

    private function buildNotificationPayload(): array
    {
        $orderNo = $this->order->code ?? '-';
        $title = 'Pembayaran Voucher Disetujui';
        $body = 'Bukti pembayaran voucher Anda ('.$orderNo.') telah disetujui. Voucher sudah aktif dan dapat digunakan.';
        $model = [
            'id' => $this->order->id,
            'voucher_order_id' => $this->order->id,
            'order_no' => $orderNo,
            'type' => 'voucher',
            'event' => 'voucher_payment_approved',
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
                'event' => 'voucher_payment_approved',
                'model' => $model,
            ],
            'model' => $model,
        ];
    }

    protected function sendPushNotification(string $token, array $notification, array $data): void
    {
        NotificationUtilities::sendNotification($token, $notification, $data);
    }
}

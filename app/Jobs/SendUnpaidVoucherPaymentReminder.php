<?php

namespace App\Jobs;

use App\Libs\NotificationUtilities;
use App\Models\FcmToken;
use App\Models\OrderVoucherHeader;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendUnpaidVoucherPaymentReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = Carbon::now('Asia/Jakarta');
        $reminderLimit = $now->copy()->addMinutes(15);
        $nowString = $now->toDateTimeString();
        $reminderLimitString = $reminderLimit->toDateTimeString();

        OrderVoucherHeader::query()
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
            ->chunkById(100, function ($orders) use ($now): void {
                foreach ($orders as $order) {
                    $payload = DB::transaction(function () use ($order, $now): ?array {
                        $voucherOrder = OrderVoucherHeader::query()
                            ->whereKey($order->id)
                            ->lockForUpdate()
                            ->first();

                        if (! $this->isEligibleForReminder($voucherOrder, $now)) {
                            return null;
                        }

                        $payload = $this->buildNotificationPayload($voucherOrder);

                        UserNotification::query()->firstOrCreate(
                            ['message_id' => 'voucher-payment-reminder-'.$voucherOrder->id],
                            [
                                'user_id' => $voucherOrder->created_by,
                                'notif_type' => 'voucher_payment_due_reminder',
                                'model' => json_encode($payload['model']),
                                'title' => $payload['notification']['title'],
                                'body' => $payload['notification']['body'],
                                'status_id' => 1,
                            ]
                        );

                        $voucherOrder->payment_reminded_at = $now;
                        $voucherOrder->save();

                        return $payload;
                    });

                    if ($payload === null) {
                        continue;
                    }

                    $this->sendReminder($payload);
                }
            });
    }

    private function isEligibleForReminder(?OrderVoucherHeader $voucherOrder, Carbon $now): bool
    {
        if ($voucherOrder === null) {
            return false;
        }

        if ((int) $voucherOrder->status_id !== 3) {
            return false;
        }

        if ($voucherOrder->payment_reminded_at !== null) {
            return false;
        }

        if ($voucherOrder->payment_due_at === null) {
            return false;
        }

        $paymentDueAt = Carbon::parse($voucherOrder->payment_due_at, 'Asia/Jakarta');

        if ($paymentDueAt->lessThanOrEqualTo($now) || $paymentDueAt->greaterThan($now->copy()->addMinutes(15))) {
            return false;
        }

        if (! empty($voucherOrder->payment_proof)) {
            return false;
        }

        return true;
    }

    private function buildNotificationPayload(OrderVoucherHeader $voucherOrder): array
    {
        $orderNo = $voucherOrder->code ?? '-';
        $dueTime = Carbon::parse($voucherOrder->payment_due_at, 'Asia/Jakarta')->format('H:i');
        $title = 'Batas Pembayaran Voucher Hampir Berakhir';
        $body = 'Voucher Anda ('.$orderNo.') belum dibayar. Mohon unggah bukti pembayaran sebelum '.$dueTime.'.';
        $model = [
            'id' => $voucherOrder->id,
            'voucher_order_id' => $voucherOrder->id,
            'order_no' => $orderNo,
            'type' => 'voucher',
            'event' => 'voucher_payment_due_reminder',
        ];

        return [
            'user_id' => $voucherOrder->created_by,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => [
                'title' => $title,
                'body' => $body,
                'type' => 'voucher',
                'event' => 'voucher_payment_due_reminder',
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
            Log::channel('firebase')->error('Sending Voucher Payment Reminder Notification : '.$token->token);
            $this->sendPushNotification($token->token, $payload['notification'], $payload['data']);
        }
    }

    protected function sendPushNotification(string $token, array $notification, array $data): void
    {
        NotificationUtilities::sendNotification($token, $notification, $data);
    }
}

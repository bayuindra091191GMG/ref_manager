<?php

namespace App\Jobs;

use App\Models\OrderVoucherHeader;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelExpiredVoucherPayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = Carbon::now('Asia/Jakarta');
        $nowString = $now->toDateTimeString();
        $cancelledCount = 0;

        OrderVoucherHeader::query()
            ->where('status_id', 3)
            ->whereNotNull('payment_due_at')
            ->where('payment_due_at', '<=', $nowString)
            ->where(function ($query): void {
                $query->whereNull('payment_proof')
                    ->orWhere('payment_proof', '');
            })
            ->orderBy('id')
            ->chunkById(100, function ($orders) use ($now, &$cancelledCount): void {
                foreach ($orders as $order) {
                    $cancelledOrder = DB::transaction(function () use ($order, $now, &$cancelledCount): ?OrderVoucherHeader {
                        $voucherOrder = OrderVoucherHeader::query()
                            ->whereKey($order->id)
                            ->lockForUpdate()
                            ->first();

                        if (! $this->isExpiredUnpaidVoucherOrder($voucherOrder, $now)) {
                            return null;
                        }

                        $voucherOrder->status_id = 10;
                        $voucherOrder->canceled_at = $now;
                        $voucherOrder->save();

                        $cancelledCount++;

                        return $voucherOrder;
                    });

                    if ($cancelledOrder !== null) {
                        SendVoucherCancelledNotification::dispatch($cancelledOrder, 'system_expired_payment');
                    }
                }
            });

        if ($cancelledCount > 0) {
            Log::channel('voucher')->info('Expired unpaid voucher orders cancelled', [
                'count' => $cancelledCount,
                'cancelled_at' => $now->toDateTimeString(),
            ]);
        }
    }

    private function isExpiredUnpaidVoucherOrder(?OrderVoucherHeader $voucherOrder, Carbon $now): bool
    {
        if ($voucherOrder === null) {
            return false;
        }

        if ((int) $voucherOrder->status_id !== 3) {
            return false;
        }

        if ($voucherOrder->payment_due_at === null || $voucherOrder->payment_due_at->greaterThan($now)) {
            return false;
        }

        if (! empty($voucherOrder->payment_proof)) {
            return false;
        }

        return true;
    }
}

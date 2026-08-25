<?php

namespace App\Jobs;

use App\Models\BookingHeader;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelExpiredBookingPayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = Carbon::now('Asia/Jakarta');
        $cancelledCount = 0;

        BookingHeader::query()
            ->where('status_id', 3)
            ->whereNotNull('payment_due_at')
            ->where('payment_due_at', '<=', $now)
            ->where(function ($query): void {
                $query->whereNull('payment_proof')
                    ->orWhere('payment_proof', '');
            })
            ->orderBy('id')
            ->chunkById(100, function ($bookings) use ($now, &$cancelledCount): void {
                foreach ($bookings as $booking) {
                    $cancelledBooking = DB::transaction(function () use ($booking, $now, &$cancelledCount): ?BookingHeader {
                        $bookingHeader = BookingHeader::query()
                            ->whereKey($booking->id)
                            ->lockForUpdate()
                            ->first();

                        if (! $this->isExpiredUnpaidBooking($bookingHeader, $now)) {
                            return null;
                        }

                        $bookingHeader->status_id = 10;
                        $bookingHeader->canceled_at = $now;
                        $bookingHeader->save();

                        $cancelledCount++;

                        return $bookingHeader;
                    });

                    if ($cancelledBooking !== null) {
                        SendBookingCancelledNotification::dispatch($cancelledBooking, 'system_expired_payment');
                    }
                }
            });

        if ($cancelledCount > 0) {
            Log::channel('booking')->info('Expired unpaid bookings cancelled', [
                'count' => $cancelledCount,
                'cancelled_at' => $now->toDateTimeString(),
            ]);
        }
    }

    private function isExpiredUnpaidBooking(?BookingHeader $bookingHeader, Carbon $now): bool
    {
        if ($bookingHeader === null) {
            return false;
        }

        if ((int) $bookingHeader->status_id !== 3) {
            return false;
        }

        if ($bookingHeader->payment_due_at === null || $bookingHeader->payment_due_at->greaterThan($now)) {
            return false;
        }

        if (! empty($bookingHeader->payment_proof)) {
            return false;
        }

        // if (! empty($bookingHeader->payment_at)) {
        //     return false;
        // }

        // if ((bool) $bookingHeader->is_settled) {
        //     return false;
        // }

        return true;
    }
}

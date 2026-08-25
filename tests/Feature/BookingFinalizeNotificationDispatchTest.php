<?php

namespace Tests\Feature;

use Tests\TestCase;

class BookingFinalizeNotificationDispatchTest extends TestCase
{
    public function test_finalize_dispatches_payment_approved_notification_after_commit(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/Admin/BookingController.php'));
        $finalizeStart = strpos($source, 'public function finalize');
        $assignStart = strpos($source, 'public function assign');

        $this->assertNotFalse($finalizeStart);
        $this->assertNotFalse($assignStart);

        $finalizeSource = substr($source, $finalizeStart, $assignStart - $finalizeStart);
        $commitPosition = strpos($finalizeSource, 'DB::commit();');
        $dispatchPosition = strpos(
            $finalizeSource,
            'SendBookingPaymentApprovedNotification::dispatch($booking);'
        );

        $this->assertNotFalse($commitPosition);
        $this->assertNotFalse($dispatchPosition);
        $this->assertGreaterThan($commitPosition, $dispatchPosition);
    }
}

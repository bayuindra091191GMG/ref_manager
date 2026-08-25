<?php

namespace Tests\Feature;

use Tests\TestCase;

class VoucherOrderPaymentSourceTest extends TestCase
{
    public function test_checkout_uses_header_as_payment_data_and_returns_empty_payments(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/Api/CheckoutController.php'));

        $this->assertStringNotContainsString('OrderVoucherPayment::create', $source);
        $this->assertStringNotContainsString('OrderVoucherPayment::where', $source);
        $this->assertStringContainsString("'payments'              => []", $source);
    }

    public function test_admin_approval_and_rejection_do_not_touch_voucher_payments_or_pg_fields(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/Admin/OrderVoucherController.php'));
        $approveStart = strpos($source, 'private function approveOrder');
        $rejectStart = strpos($source, 'private function rejectOrder');
        $generateStart = strpos($source, 'private function generateVoucherCodes');

        $this->assertNotFalse($approveStart);
        $this->assertNotFalse($rejectStart);
        $this->assertNotFalse($generateStart);

        $adminDecisionSource = substr($source, $approveStart, $generateStart - $approveStart);

        $this->assertStringNotContainsString('OrderVoucherPayment::', $adminDecisionSource);
        $this->assertStringNotContainsString('pg_status', $adminDecisionSource);
        $this->assertStringNotContainsString('pg_service', $adminDecisionSource);
        $this->assertStringNotContainsString('pg_settlement_date', $adminDecisionSource);
    }

    public function test_admin_approval_dispatches_payment_approved_notification_after_commit(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/Admin/OrderVoucherController.php'));
        $checkProofStart = strpos($source, 'public function checkProof');
        $cancelStart = strpos($source, 'public function cancel');

        $this->assertNotFalse($checkProofStart);
        $this->assertNotFalse($cancelStart);

        $checkProofSource = substr($source, $checkProofStart, $cancelStart - $checkProofStart);
        $commitPosition = strpos($checkProofSource, 'DB::commit();');
        $dispatchPosition = strpos(
            $checkProofSource,
            'SendVoucherPaymentApprovedNotification::dispatch($order);'
        );

        $this->assertNotFalse($commitPosition);
        $this->assertNotFalse($dispatchPosition);
        $this->assertGreaterThan($commitPosition, $dispatchPosition);
        $this->assertStringContainsString('$shouldSendPaymentApprovedNotification = true;', $checkProofSource);
    }
}

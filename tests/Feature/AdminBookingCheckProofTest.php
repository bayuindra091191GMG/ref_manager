<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\BookingController;
use App\Models\BookingHeader;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBookingCheckProofTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('booking_headers');

        Schema::create('booking_headers', function (Blueprint $table): void {
            $table->id();
            $table->integer('status_id')->nullable();
            $table->string('payment_proof')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function test_rejecting_payment_proof_again_clears_and_deletes_existing_proof(): void
    {
        Storage::fake('proofs');
        Storage::disk('proofs')->put('proof-old.jpg', 'proof');

        $booking = BookingHeader::forceCreate([
            'status_id' => 3,
            'payment_proof' => 'proof-old.jpg',
        ]);

        $controller = app(BookingController::class);
        $request = Request::create('/admin/booking/check-proof', 'POST', [
            'id' => $booking->id,
            'status' => 14,
        ]);

        $response = $controller->checkProof($request);

        $this->assertTrue($response->isRedirect());
        $this->assertDatabaseHas('booking_headers', [
            'id' => $booking->id,
            'status_id' => 14,
            'payment_proof' => null,
        ]);
        Storage::disk('proofs')->assertMissing('proof-old.jpg');
    }
}

<?php

namespace App\Jobs;

use App\Libs\NotificationUtilities;
use App\Models\FcmToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BookingReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $booking;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $notification = [
            'title' => 'Reminder: Sesi Pijat Anda dalam 1 Jam!',
            'body' => 'Jangan lupa! Sesi pijat Anda ('.$this->booking->code.') akan dimulai dalam 1 jam. Pastikan Anda siap dan rileks untuk pengalaman terbaik.',
        ];

        $bodyData = [
            'title' => 'Reminder: Sesi Pijat Anda dalam 1 Jam!',
            'body' => 'Jangan lupa! Sesi pijat Anda ('.$this->booking->code.') akan dimulai dalam 1 jam. Pastikan Anda siap dan rileks untuk pengalaman terbaik.',
            'type' => 'booking',
        ];

        $tokens = FcmToken::where('user_id', $this->booking->user_id)->get();

        foreach ($tokens as $token){
            Log::channel('firebase')->error('Sending Notification : ' . $token->token);
            NotificationUtilities::sendNotification($token->token, $notification, $bodyData);
        }
    }
}

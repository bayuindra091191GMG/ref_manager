<?php

namespace App\Jobs;

use App\Models\BookingHeader;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class InitiateReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    public function __construct()
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $bookings = BookingHeader::where('status_id', 3)->get();

        $now = Carbon::now();
        $jobs = [];
        foreach ($bookings as $booking){
            $date = Carbon::parse($booking->booking_date);
            $times = explode(' - ', $booking->booking_time);
            $time = Carbon::createFromFormat('H:i', $times[0]);
            $date->setTime($time->hour, $time->minute);

            if(abs($date->diffInMinutes($now)) == 60){
                $jobs[] = new BookingReminder($booking);
            }
        }

        Bus::batch($jobs)->allowFailures()->dispatch();
    }
}

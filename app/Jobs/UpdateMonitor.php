<?php

namespace App\Jobs;

use App\Models\Partner;
use App\Models\PreweddingDetail;
use App\Models\Room;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailPartner;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateMonitor implements ShouldQueue
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
        Log::channel('monitor_job')->info('Update Monitor');

        $today = Carbon::now();
        $runnings = TransactionDetail::where('start_time', '<=', $today)
            ->where('end_time', '>=', $today)
            ->where('started_from_cron', 0)
            ->get();

        $partners = [];
        $rooms = [];
        foreach ($runnings as $running) {
            $partner = TransactionDetailPartner::where('transaction_detail_id', $running->id)->first();
            $partners[] = $partner->partner_id;
            if($running->transaction_header->is_prewed == 1){
                $detail = PreweddingDetail::where('transaction_detail_id', $running->id)->first();
                $rooms[] = $detail->room_id;
            } else {
                $rooms[] = $running->transaction_header->room_id;
            }

            $running->update([
                'started_from_cron' => 1
            ]);
        }

        Partner::whereIn('id', $partners)->update([
            'status_id' => 6
        ]);

        Room::whereIn('id', $rooms)->update([

        ]);

        $dones = TransactionDetail::where(function ($query) use ($today) {
            $query->where('start_time', '>=', $today)
                ->orWhere('end_time', '<=', $today);
        })->where('started_from_cron', 1)
            ->get();

        $partners2 = [];
        foreach ($dones as $done) {
            $partner = TransactionDetailPartner::where('transaction_detail_id', $done->id)->first();
            $master = Partner::find($partner->partner_id);
            if($master->status_id == 6){
                $partners2[] = $partner->partner_id;
            }
        }

        Partner::whereIn('id', $partners2)->update([
            'status_id' => 12
        ]);
    }
}

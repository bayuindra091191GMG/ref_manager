<?php

namespace App\Jobs;

use App\Models\CommissionPartnerHistory;
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

class GenerateTransactionCommision implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */


    public function handle(): void
    {
        $transactionDetails = TransactionDetail::where('status_id', 9)
            ->whereDate('created_at', Carbon::yesterday())
            ->where('item_id', null) //get only treatment transaction
            ->get();

        foreach ($transactionDetails as $detail) {
            $header = $detail->transaction_header;
            $check = CommissionPartnerHistory::where('transaction_id', $header->id)->first();

            if ($check) {
                continue;
            }

            // if ($detail->package_id == null || $detail->price == 0) {
            if ($detail->price == 0) {
                continue;
            }

            $detailPartner = $detail->partners->first();
            if (!$detailPartner) {
                continue;
            }


            $package = $detail->package;
            $percentageCommision = $package->commission_percent ?? 0;

            $commision = $detail->subtotal * ($percentageCommision / 100);

            $history = CommissionPartnerHistory::create([
                'branch_id' => $header->branch_id,
                'transaction_id' => $header->id,
                'transaction_code' => $header->code,
                'partner_id' => $detailPartner->partner_id,
                'partner_number' => $detailPartner->partner->number ?? null,
                'partner_type' => $detailPartner->partner ? $detailPartner->partner->type : null,
                'partner_name' => $detailPartner->partner ? $detailPartner->partner->name : null,
                'partner_phone' => $detailPartner->partner ? $detailPartner->partner->phone : null,
                'package_id' => $package->id,
                'package_name' => $package->name,
                'package_code' => $package->code,
                'commission_percentage' => $percentageCommision,
                'commission_result' => $commision,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ]);
        }
    }
}

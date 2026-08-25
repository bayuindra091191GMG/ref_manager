<?php

namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class CommissionPartnerHistoryTransformer extends TransformerAbstract
{
    public function transform($history){
        $created_at = Carbon::parse($history->created_at)->toIso8601String();

        return [
            'transaction_code' => $history->transaction_code,
            'partner_number' => $history->partner_number,
            'partner_name' => $history->partner_name,
            'partner_alias' => $history->partner_alias,
            'package_code' => $history->package_code,
            'package_name' => $history->package_name,
            'commission_add' => $history->commission_add,
            'created_at' => $created_at
        ];
    }
}

<?php

namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class CommissionEmployeeHistoryTransformer extends TransformerAbstract
{
    public function transform($history){
        $created_at = Carbon::parse($history->created_at)->toIso8601String();

        return [
            'transaction_code' => $history->transaction_code,
            'employee_code' => $history->employee_code,
            'employee_name' => $history->employee_name,
            'item_name' => $history->item_name,
            'item_code' => $history->item_code,
            'commission_add' => $history->commission_add,
            'created_at' => $created_at
        ];
    }
}

<?php

namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class InventoryHistoryTransformer extends TransformerAbstract
{
    public function transform($history){
        $createdAt = Carbon::parse($history->updated_at)->toIso8601String();

        return[
            'created_at' => $createdAt,
            'warehouse_name' => $history->warehouse_name,
            'item_name' => $history->item_name,
            'item_code' => $history->item_code,
            'qty_add' => $history->qty_add,
            'qty_sub' => $history->qty_sub,
            'qty_result' => $history->qty_result,
            'reference' => $history->reference,
            'description' => $history->description,
        ];
    }
}

<?php

namespace App\Transformer;

use League\Fractal\TransformerAbstract;

class InventoryTransformer extends TransformerAbstract
{
    public function transform($inventory){

        return[
            'item_name' => $inventory->item_name,
            'item_code' => $inventory->item_code,
            'unit_name' => $inventory->unit_name,
            'warehouse_id' => $inventory->warehouse_id,
            'warehouse_name' => $inventory->warehouse_name,
            'qty' => $inventory->qty,
        ];
    }
}

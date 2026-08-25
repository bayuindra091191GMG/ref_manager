<?php

namespace App\Transformer;

use League\Fractal\TransformerAbstract;

class CommissionPositionTransformer extends TransformerAbstract
{
    public function transform($commission){
//        $updatedAt = Carbon::parse($commission->created_at)->toIso8601String();
        $action = "<button type='button' class='btn-edit btn btn-xs btn-primary' data-id='". $commission->id. "'
        data-item-name='". $commission->item_name. "'
        data-commission-percentage='". $commission->commission_percentage. "'
        data-commission-flat='". $commission->commission_flat. "'
        data-status-id='". $commission->status_id. "'><i class='fa fa-pencil-alt'></i></a>";

//        $inputPercent = "<input type='text' class='form-control' id='input_percent_". $commission->id. "' value='""'/>";

        return [
            'action' => $action,
            'position_code' => $commission->position_code,
            'position_name' => $commission->position_name,
            'item_name' => $commission->item_name,
            'item_code' => $commission->item_code,
            'commission_percentage' => $commission->commission_percentage,
            'commission_flat' => $commission->commission_flat,
            'status_id' => $commission->status_id,
//            'updated_at' => $updatedAt
        ];
    }
}

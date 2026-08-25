<?php

namespace App\Transformer;

use League\Fractal\TransformerAbstract;

class CommissionPartnerLevelTransformer extends TransformerAbstract
{
    public function transform($commission){
        $action = "<button type='button' class='btn-edit btn btn-xs btn-primary' data-id='". $commission->id. "'
        data-partner-level-name='". $commission->partner_level. "'
        data-package-name='". $commission->package_name. "'
        data-commission-percentage='". $commission->commission_percentage. "'
        data-commission-flat='". $commission->commission_flat. "'
        data-status-id='". $commission->status_id. "'><i class='fa fa-pencil-alt'></i></a>";

//        $inputPercent = "<input type='text' class='form-control' id='input_percent_". $commission->id. "' value='""'/>";

        return [
            'action' => $action,
            'partner_level' => $commission->partner_level,
            'package_name' => $commission->package_name,
            'commission_percentage' => $commission->commission_percentage,
            'commission_flat' => $commission->commission_flat,
            'status_id' => $commission->status_id
        ];
    }
}

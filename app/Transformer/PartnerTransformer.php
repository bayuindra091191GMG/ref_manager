<?php

namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PartnerTransformer extends TransformerAbstract
{
    public function transform($partner){
        $joinDate = Carbon::parse($partner->join_date)->toIso8601String();

        $action = '';
        if($partner->status_id !== 2){
            if($partner->is_present == 0){
                $action = "<button type='button' id='btn_present_". $partner->id. "' class='btn btn-xs btn-success btn-present' data-id='". $partner->id. "'>Hadir</button>&nbsp;";
            }
            else{
                $action = "<button type='button' id='btn_present_". $partner->id. "' class='btn btn-xs btn-warning btn-present' data-id='". $partner->id. "'>Tidak Hadir</button>&nbsp;";
            }
        }

        $action .= "<a class='btn btn-xs btn-primary' href='". route('admin.partner.show', $partner->id). "'><i class='fa fa-info'></i></a>";

        return [
            'action' => $action,
            'id' => $partner->id,
            'code' => $partner->code,
            'name' => $partner->name,
            'number' => $partner->number,
            'phone' => $partner->phone,
            'alias' => $partner->alias,
            'type' => $partner->type,
            'status_id' => $partner->status_id,
            'status_display' => $partner->status_display,
            'join_date' => $joinDate,
            'is_present' => $partner->is_present
        ];
    }
}

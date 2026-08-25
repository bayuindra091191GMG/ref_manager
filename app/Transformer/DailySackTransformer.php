<?php


namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class DailySackTransformer extends TransformerAbstract
{
    public function transform($data){
        $date = Carbon::parse($data->date)->toIso8601String();

        $urlShowMeta = route('admin.production.sack.edit', ['id' => $data->id]);
        $action = "<a class='btn btn-xs btn-info' href='". route('admin.production.sack.show', ['id' => $data->id]) ."'><i class='fas fa-xs fa-info'></i></a>";
        if($data->status_id == 1){
            $action .= "<a class='btn btn-xs btn-primary' href='". $urlShowMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
            $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' ><i class='fas fa-xs fa-times  '></i></a>";
        }


        return[
            'id'            => $data->id,
            'date'          => $date,
            'total_qty'     => $data->total_qty,
            'employee_name' => $data->employee_name,
            'status_id'     => $data->status_id,
            'status'        => $data->status->display,
            'action'        => $action
        ];
    }
}

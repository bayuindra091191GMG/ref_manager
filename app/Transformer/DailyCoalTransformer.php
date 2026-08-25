<?php


namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class DailyCoalTransformer extends TransformerAbstract
{
    public function transform($data){
        $date = Carbon::parse($data->date)->toIso8601String();

        $urlShowMeta = route('admin.production.coal.edit', ['id' => $data->id]);
        $action = "";
        if($data->status_id == 1){
            $action = "<a class='btn btn-xs btn-primary' href='". $urlShowMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
            $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' ><i class='fas fa-xs fa-times  '></i></a>";
        }


        return[
            'id'                => $data->id,
            'date'              => $date,
            'qty'               => $data->qty,
            'result_ab'         => $data->result_ab,
            'result_ak_zak'     => $data->result_ak_zak,
            'result_ak_kg'      => $data->result_ak_kg,
            'average_coal'      => $data->average_coal,
            'production_time'   => $data->production_time,
            'employee_name'     => $data->employee_name,
            'status_id'         => $data->status_id,
            'action'            => $action
        ];
    }
}

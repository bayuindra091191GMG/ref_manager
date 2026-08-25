<?php


namespace App\Transformer;

use App\Models\SoSchedule;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class SoScheduleTransformer extends TransformerAbstract
{
    public function transform(SoSchedule $data){
        $date = Carbon::parse($data->created_at)->toIso8601String();

        $urlEditMeta = route('admin.so_schedule.edit', ['id' => $data->id]);
        $urlShowMeta = route('admin.so_schedule.show', ['id' => $data->id]);
        $action = "<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
        $action .= "&nbsp;<a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";
        $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' data-name='". $data->code ."' ><i class='fas fa-xs fa-times  '></i></a>";

        return[
            'created_at'    => $date,
            'code'          => $data->code,
            'so_id'         => $data->so_id,
            'so_code'       => $data->so_code,
            'customer'      => $data->sales_order_header->customer->name,
            'no_plat'       => $data->no_plat,
            'driver_name'   => $data->driver_name,
            'destination'   => $data->destination,
            'date'          => $data->date->locale('id_ID')->format('d-M-Y H:i'),
            'status'        => $data->status->display,
            'action'        => $action
        ];
    }
}

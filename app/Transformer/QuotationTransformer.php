<?php


namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class QuotationTransformer extends TransformerAbstract
{
    public function transform($data){
        $date = Carbon::parse($data->created_at)->toIso8601String();
        $dateRequired = Carbon::parse($data->required_date)->toIso8601String();

        $urlEditMeta = route('admin.quotation.edit', ['id' => $data->id]);
        $urlShowMeta = route('admin.quotation.show', ['id' => $data->id]);
        $action = "<a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";
        if($data->status_id != 6){
            $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
            $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' data-number='$data->code' ><i class='fas fa-xs fa-times  '></i></a>";
        }

        return[
            'created_at'        => $date,
            'id'                => $data->id,
            'code'              => $data->code,
            'required_date'     => $dateRequired,
            'customer_id'       => $data->customer_id,
            'customer_name'     => $data->customer->name,
            'warehouse_id'      => $data->warehouse_id,
            'warehouse_name'    => $data->warehouse->name,
            'description'       => $data->description,
            'status_id'         => $data->status_id,
            'status'            => $data->status->display,
            'action'            => $action,
        ];
    }
}

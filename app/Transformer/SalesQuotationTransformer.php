<?php


namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Models\SalesOrderHeader;

class SalesQuotationTransformer extends TransformerAbstract
{
    public function transform($data){
        $date = Carbon::parse($data->created_at)->toIso8601String();
        $dateRequired = Carbon::parse($data->transaction_date)->toIso8601String();

        $urlEditMeta = route('admin.sales_quotation.edit', ['id' => $data->id]);
        $urlShowMeta = route('admin.sales_quotation.show', ['id' => $data->id]);
        $action = "<a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";
        if($data->status_id != 6 && $data->status_id != 17){
            $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
            $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' data-number='$data->code' ><i class='fas fa-xs fa-times  '></i></a>";
        }

        error_log($data->id);

        return[
            'created_at' => $date,
            'id' => $data->id,
            'code' => $data->code,
            'transaction_date' => $dateRequired,
            'customer_id' => $data->customer_id,
            'customer_name' => $data->customer_name,
            'customer_po_number'=> $data->customer_po_number,
//            'warehouse_id'      => $data->warehouse_id,
//            'warehouse_name'    => $data->warehouse->name,
            // 'description' => $data->description,
            'salesman_name' => $data->salesman_name,
            'grandtotal' => $data->grandtotal,
            'status_id' => $data->status_id,
            'status_display' => $data->status_display,
            'action' => $action,
        ];
    }
}

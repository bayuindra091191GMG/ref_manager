<?php


namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Models\AdminUser;

class PurchaseOrderTransformer extends TransformerAbstract
{
    public function transform($data){
        $date = Carbon::parse($data->created_at)->toIso8601String();
        $updatedAt = $data->updated_at == null ? $date : Carbon::parse($data->updated_at)->toIso8601String();
        $dateRequired = Carbon::parse($data->transaction_date)->toIso8601String();

        $updatedBy = $data->updated_by == null ? $data->created_by : $data->updated_by;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;

        $urlEditMeta = route('admin.purchase_order.edit', ['id' => $data->id]);
        $urlShowMeta = route('admin.purchase_order.show', ['id' => $data->id]);
        $urlPrintMeta = route('admin.purchase_order.show', ['id' => $data->id]) . "?type=print";

        $action = "";

        if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->guard('admin')->user()->hasAnyPermission('purchase_order.show')))
        {
            $action = "<a class='btn btn-xs btn-dark' href='". $urlPrintMeta."' ><i class='fas fa-xs fa-print'></i></a>";
            $action .= "&nbsp;<a class='btn btn-xs btn-info' href='". $urlShowMeta."' ><i class='fas fa-xs fa-info'></i></a>";
        }

        if($data->status_id == 1){
            if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
            (auth()->guard('admin')->user()->hasAnyPermission('purchase_order.edit')))
            {
                $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."' ><i class='fas fa-xs fa-pencil-alt'></i></a>";
            }

//            if (auth()->user()->is_super == 1 || auth()->user()->is_developer == 1 ||
//            (auth()->guard('admin')->user()->hasAnyPermission('purchase_order.delete')))
//            {
//                $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' data-number='$data->code'  ><i class='fas fa-xs fa-times  '></i></a>";
//            }
        }

        return[
            'created_at'        => $date,
            'id'                => $data->id,
            'code'              => $data->code,
            'reference_code'              => $data->reference_code,
            'transaction_date'     => $dateRequired,
            'supplier_id'       => $data->supplier_id,
            'supplier_name'     => $data->supplier->name,
//            'profit_center_id'  => $data->profit_center_id,
//            'profit_center_name'=> $data->profit_center->name,
//                        'warehouse_id'      => $data->warehouse_id,
            'warehouse_name'    => $data->warehouse->name,
            'description'       => $data->description,
            'type'              => $data->type,
            'text_quotation_no' => $data->text_quotation_no,
            'status_id'         => $data->status_id,
            'status'            => $data->status->display,
            'status po'         => $data->is_gr,
            'action'            => $action,
            'updated_at'        => $updatedAt,
            'updated_by'        => $updatedByName

        ];
    }
}

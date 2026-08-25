<?php


namespace App\Transformer;

use App\Models\AdminUser;
use App\Models\DeliveryOrderHeader;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use League\Fractal\TransformerAbstract;

class DeliveryOrderTransformer extends TransformerAbstract
{
    public function transform(DeliveryOrderHeader $data){
        $date = Carbon::parse($data->created_at)->toIso8601String();
        $updatedAt = $data->updated_at == null ? $date : Carbon::parse($data->updated_at)->toIso8601String();
        $dateRequired = Carbon::parse($data->date)->toIso8601String();
        
        $updatedBy = $data->updated_by == null ? $data->created_by : $data->updated_by;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;
        
        $urlEditMeta = route('admin.delivery_order.edit', ['id' => $data->id]);
        $urlPrintMeta = route('admin.delivery_order.show', ['id' => $data->id]) . "?type=print";
        $urlShowMeta = route('admin.delivery_order.show', ['id' => $data->id]);
        $action = "";
        if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
        || (Auth::guard('admin')->user()->hasPermissionTo('delivery_order.show'))){
            $action .= "<a class='btn btn-xs btn-dark' href='". $urlPrintMeta."' ><i class='fas fa-xs fa-print'></i></a>";
            $action .= "&nbsp;<a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";
        }
        if($data->status_id == 1){
            if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
            || (Auth::guard('admin')->user()->hasPermissionTo('delivery_order.edit'))){
                $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
            }

//            if(Auth::guard('admin')->user()->is_super == 1 || Auth::guard('admin')->user()->is_developer == 1
//            || (Auth::guard('admin')->user()->hasPermissionTo('delivery_order.delete'))){
//                $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' data-number='$data->code' ><i class='fas fa-xs fa-times  '></i></a>";
//            }
        }
        
        return[
            'created_at'        => $date,
            'id'                => $data->id,
            'code'              => $data->code,
            // 'quotation_code'    => $data->quotation_code,
            'so_code'           => $data->so_code,
            'schedule_code'           => $data->schedule_code,
            'profit_center_id'  => $data->profit_center_id,
            'profit_center_name'  => $data->profit_center_name,
            'date'              => $dateRequired,
            'customer_id'       => $data->customer_id,
            'customer_name'     => $data->customer_name,
            'warehouse_id'      => $data->warehouse_id,
            'warehouse_name'    => $data->warehouse_name,
            'description'       => $data->description,
            'plat_no'       => $data->plat_no,
            'status_id'         => $data->status_id,
            'status'            => $data->status,
            'is_printed'        => $data->is_printed,
            'customer_po_number'        => $data->customer_po_number,
            'shipping_agent'        => $data->shipping_agent,
            'action'            => $action,
            'updated_at'        => $updatedAt,
            'updated_by'        => $updatedByName
        ];
    }
}

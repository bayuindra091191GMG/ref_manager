<?php


namespace App\Transformer;

use App\Models\AdminUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use League\Fractal\TransformerAbstract;
use App\Models\SalesOrderHeader;

class SalesOrderTransformer extends TransformerAbstract
{
    public function transform($data){
        $date = Carbon::parse($data->created_at)->toIso8601String();
        $updatedAt = $data->updated_at == null ? $date : Carbon::parse($data->updated_at)->toIso8601String();
        $dateRequired = Carbon::parse($data->transaction_date)->toIso8601String();
        
        $updatedBy = $data->updated_by == null ? $data->created_by : $data->updated_by;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;

        $urlPrintMeta = route('admin.sales_order.print', ['id' => $data->id]);
        $urlEditMeta = route('admin.sales_order.edit', ['id' => $data->id]);
        $urlShowMeta = route('admin.sales_order.show', ['id' => $data->id]);
        $action = "";
        if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
        || (Auth::guard('admin')->user()->hasPermissionTo('sales_order.show'))){
            $action .= "<a class='btn btn-xs btn-dark' href='". $urlPrintMeta."' ><i class='fas fa-xs fa-print'></i></a>";
            $action .= "&nbsp;<a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";
        }
        if($data->status_id == 1){
            if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
            || (Auth::guard('admin')->user()->hasPermissionTo('sales_order.edit'))){
                $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
            }

//            if(Auth::guard('admin')->user()->is_super == 1 || Auth::guard('admin')->user()->is_developer == 1
//            || (Auth::guard('admin')->user()->hasPermissionTo('sales_order.delete'))){
//                $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' data-number='$data->code' ><i class='fas fa-xs fa-times  '></i></a>";
//            }
        }
        
        error_log($data->id);
        
        return[
            'created_at' => $date,
            'id' => $data->id,
            'code' => $data->code,
            'type' => strtoupper($data->type),
            'profit_center_id' => $data->profit_center_id,
            'profit_center_name' => $data->profit_center_name,
            'transaction_date' => $dateRequired,
            'customer_id' => $data->customer_id,
            'customer_name' => $data->customer_name,
            'customer_po_number'=> $data->customer_po_number,
            //            'warehouse_id'      => $data->warehouse_id,
            //            'warehouse_name'    => $data->warehouse->name,
            'description' => $data->description,
            'status_id' => $data->status_id,
            'status_display' => $data->status_display,
            'action' => $action,
            'updated_at' => $updatedAt,
            'updated_by' => $updatedByName
        ];
    }
}

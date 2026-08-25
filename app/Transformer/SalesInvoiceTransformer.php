<?php


namespace App\Transformer;

use App\Models\AdminUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use League\Fractal\TransformerAbstract;

class SalesInvoiceTransformer extends TransformerAbstract
{
    public function transform($data){
        $date = Carbon::parse($data->created_at)->toIso8601String();
        $updatedAt = $data->updated_at == null ? $data->created_at : Carbon::parse($data->updated_at)->toIso8601String();
        $dateRequired = Carbon::parse($data->date)->toIso8601String();
        
        $updatedBy = $data->updated_by == null ? $data->created_by : $data->updated_by;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;
        
        $urlEditMeta = route('admin.sales_invoice.edit', ['id' => $data->id]);
        $urlShowMeta = route('admin.sales_invoice.show', ['id' => $data->id]);
        $urlPrintMeta = route('admin.sales_invoice.print', ['id' => $data->id]);
        $action = "";
        if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
        || (Auth::guard('admin')->user()->hasPermissionTo('sales_invoice.show'))){
            $action .= "<a class='btn btn-xs btn-dark' href='". $urlPrintMeta."' target='_blank'><i class='fas fa-xs fa-print'></i></a>";
            $action .= "&nbsp;<a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";
        }
        if($data->status_id == 1){
            if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
            || (Auth::guard('admin')->user()->hasPermissionTo('sales_invoice.edit'))){
                $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
            }
            
//            if(Auth::guard('admin')->user()->is_super == 1 || Auth::guard('admin')->user()->is_developer == 1
//            || (Auth::guard('admin')->user()->hasPermissionTo('sales_invoice.delete'))){
//                $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' data-number='$data->code' ><i class='fas fa-xs fa-times  '></i></a>";
//            }
        }
        
        
        return[
            'created_at'        => $date,
            'customer_received_at'        => $data->customer_received_at,
            'id'                => $data->id,
            'code'              => $data->code,
            'date'              => $dateRequired,
            'customer_id'       => $data->customer_id,
            'customer_name'     => $data->customer->name,
            'profit_center_id'  => $data->profit_center_id,
            'profit_center_name'=> $data->profit_center->name,
            'warehouse_id'      => $data->warehouse_id,
            'warehouse_name'    => $data->warehouse->name,
            'description'       => $data->description,
            'status_id'         => $data->status_id,
            'status'            => $data->status->display,
            'action'            => $action,
            'customer_po_number' => $data->customer_po_number,
            'updated_at'        => $updatedAt,
            'updated_by'        => $updatedByName
        ];
    }
}

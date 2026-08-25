<?php


namespace App\Transformer;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use League\Fractal\TransformerAbstract;
use App\Models\SalesOrderHeader;

class SalesReturnTransformer extends TransformerAbstract
{
    public function transform($data){
        $urlPrintMeta = route('admin.sales_return.print', ['id' => $data->id]);
        $urlEditMeta = route('admin.sales_return.edit', ['id' => $data->id]);
        $urlShowMeta = route('admin.sales_return.show', ['id' => $data->id]);
        $action = "";
        if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
            || (Auth::guard('admin')->user()->hasPermissionTo('advance_payment.show'))){
            $action .= "<a class='btn btn-xs btn-dark' href='". $urlPrintMeta."' ><i class='fas fa-xs fa-print'></i></a>";
            $action .= "&nbsp;<a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";
        }
        if($data->status_id != 6 && $data->status_id != 17){
            if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
                || (Auth::guard('admin')->user()->hasPermissionTo('advance_payment.edit'))){
                $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
            }
            if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
                || (Auth::guard('admin')->user()->hasPermissionTo('advance_payment.delete'))){
                $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' data-number='$data->code' ><i class='fas fa-xs fa-times  '></i></a>";
            }
        }

        return[
            'created_at' => $data->created_at,
            'id' => $data->id,
            'code' => $data->code,
            'date' => $data->date,
            'customer_name' => $data->customer_name,
            'reference_code'=> $data->reference_code,
            'profit_center_id' => $data->profit_center_id,
            'profit_center_name' => $data->profit_center_name,
            'warehouse_name'    => $data->warehouse_name,
            'description' => $data->description,
            'status_id' => $data->status_id,
            'status' => $data->status,
            'action' => $action,
        ];
    }
}

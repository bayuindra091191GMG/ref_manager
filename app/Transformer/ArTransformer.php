<?php


namespace App\Transformer;

use App\Models\AdminUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use League\Fractal\TransformerAbstract;

class ArTransformer extends TransformerAbstract
{
    public function transform($data){
        $date = Carbon::parse($data->created_at)->toIso8601String();
        $updatedAt = $data->updated_at == null ? $date : Carbon::parse($data->updated_at)->toIso8601String();
        $dateRequired = Carbon::parse($data->date)->toIso8601String();
        
        $updatedBy = $data->updated_by == null ? $data->created_by : $data->updated_by;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;
        
        $urlEditMeta = route('admin.ar.edit', ['id' => $data->id]);
        $urlShowMeta = route('admin.ar.show', ['id' => $data->id]);
        $urlPrintMeta = route('admin.ar.show', ['id' => $data->id]) . "?type=print";

        $action = "";
        if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
        || (Auth::guard('admin')->user()->hasPermissionTo('ar.show'))){
            $action .= "<a class='btn btn-xs btn-dark' href='". $urlPrintMeta."' ><i class='fas fa-xs fa-print'></i></a>";
            $action .= "&nbsp;<a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";
        }
        if($data->status_id != 6){
            if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
            || (Auth::guard('admin')->user()->hasPermissionTo('ar.edit'))){
                $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
            }
            if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
            || (Auth::guard('admin')->user()->hasPermissionTo('ar.delete'))){
                $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' data-number='$data->code' ><i class='fas fa-xs fa-times'></i></a>";
            }
        }
        
        return[
            'created_at'        => $date,
            'id'                => $data->id,
            'code'              => $data->code,
            'profit_center'     => $data->profit_center,
            'date'              => $dateRequired,
            'warehouse_id'       => $data->warehouse_id,
            'warehouse_name'     => $data->warehouse_name,
            'customer_id'       => $data->customer_id,
            'customer_name'     => $data->customer_name,
            'grand_total'       => $data->grandtotal,
            'description'       => $data->description,
            'status_id'         => $data->status_id,
            'status'            => $data->status,
            'action'            => $action,
            'updated_at'        => $updatedAt,
            'updated_by'        => $updatedByName
        ];
    }
}

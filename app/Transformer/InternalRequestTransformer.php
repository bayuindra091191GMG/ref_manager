<?php


namespace App\Transformer;

use App\Models\AdminUser;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class InternalRequestTransformer extends TransformerAbstract
{
    public function transform($data){
        $date = Carbon::parse($data->created_at)->toIso8601String();
        $updatedAt = $data->updated_at != null ? Carbon::parse($data->updated_at)->toIso8601String() : $date;
        //        $dateRequired = Carbon::parse($data->required_date)->toIso8601String();
        
        $updatedBy = $data->updated_by != null ? $data->updated_by : $data->created_by;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;

        $urlPrintMeta = route('admin.material_request.show', ['id' => $data->id]) . "?type=print";
        $urlEditMeta = route('admin.material_request.edit', ['id' => $data->id]);
        $urlShowMeta = route('admin.material_request.show', ['id' => $data->id]);
        
        $action = '';
        if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->guard('admin')->user()->hasAnyPermission('material_request.show')))
        {
            $action .= "<a class='btn btn-xs btn-dark' href='". $urlPrintMeta."' ><i class='fas fa-xs fa-print'></i></a>";
            $action .= "&nbsp;<a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";
        }
        
        
        if($data->status_id == 1){
            if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
            (auth()->guard('admin')->user()->hasAnyPermission('material_request.edit')))
            {
                $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
                
            }
            
//            if (auth()->user()->is_super == 1 || auth()->user()->is_developer == 1 ||
//            (auth()->guard('admin')->user()->hasAnyPermission('material_request.delete')))
//            {
//                $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' data-number='$data->code' ><i class='fas fa-xs fa-times  '></i></a>";
//            }
        }
        
        return[
            'created_at'        => $date,
            'id'                => $data->id,
            'code'              => $data->code,
            'profit_center_id'  => $data->profit_center_id,
            'profit_center_name'=> $data->profit_center->name ?? '-',
            //            'required_date'     => $dateRequired,
            //            'supplier_id'       => $data->supplier_id,
            //            'supplier_name'     => $data->supplier->name,
            //            'warehouse_id'      => $data->warehouse_id,
            //            'warehouse_name'    => $data->warehouse->name,
            'description'       => $data->description,
            'status_id'         => $data->status_id,
            'status'            => $data->status->display,
            'action'            => $action,
            'updated_at'        => $updatedAt,
            'updated_by'        => $updatedByName
        ];
    }
}

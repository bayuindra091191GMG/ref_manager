<?php


namespace App\Transformer;

use App\Models\AdminUser;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class InventoryInTransformer extends TransformerAbstract
{
    public function transform($inventory){
        $date = Carbon::parse($inventory->created_at)->toIso8601String();
        $updatedAt = $inventory->updated_at == null ? $date : Carbon::parse($inventory->updated_at)->toIso8601String();

        $updatedBy = $inventory->updated_by == null ? $inventory->created_by : $inventory->updated_by;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;

        $urlShowMeta = route('admin.inventory_in.show', ['id' => $inventory->id]);
        //        $action = "<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
        $action = "";
        if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 || auth()->guard('admin')->user()->hasAnyPermission('inventory_in.show')){
            $action = "&nbsp; <a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";
        }

        if($inventory->status_id != 6 && $inventory->status_id != 13){
            if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 || auth()->guard('admin')->user()->hasAnyPermission('inventory_in.edit'))
            {
                $urlEditMeta = route('admin.inventory_in.edit', ['id' => $inventory->id]);
                $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
            }

            if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 || auth()->guard('admin')->user()->hasAnyPermission('inventory_in.delete')){

                $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $inventory->id ."' data-number='$inventory->code' ><i class='fas fa-xs fa-times  '></i></a>";

            }
        }




        return[
            'id'    => $inventory->id,
            'created_at'    => $date,
            'code'          => $inventory->code,
            'warehouse_name'        => $inventory->warehouse_name,
            'status'        => $inventory->status->display,
            'status_id'        => $inventory->status_id,
            'action'        => $action,
            'updated_at'    => $updatedAt,
            'updated_by'    => $updatedByName
        ];
    }
}

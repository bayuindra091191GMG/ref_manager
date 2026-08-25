<?php


namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Models\AdminUser;

class InventoryOutTransformer extends TransformerAbstract
{
    public function transform($inventory){
        $createdAt = Carbon::parse($inventory->created_at)->toIso8601String();
        $updatedAt = $inventory->updated_at == null ? $createdAt : Carbon::parse($inventory->updated_at)->toIso8601String();
        $date = Carbon::parse($inventory->date)->toIso8601String();

        $updatedBy = $inventory->updated_by == null ? $inventory->created_by : $inventory->updated_by;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;

        if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->guard('admin')->user()->hasAnyPermission('inventory_out.show')))
        {
            //        $urlEditMeta = route('admin.inventory_out.edit', ['id' => $inventory->id]);
            $urlShowMeta = route('admin.inventory_out.show', ['id' => $inventory->id]);
            //        $action = "<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
            $action = "&nbsp;<a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";
            //        $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $inventory->id ."' data-number='". $inventory->code ."' ><i class='fas fa-xs fa-times  '></i></a>";
        }



        if($inventory->status_id != 6 && $inventory->status_id != 13){
            if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
            (auth()->guard('admin')->user()->hasAnyPermission('inventory_out.edit')))
            {
                $urlEditMeta = route('admin.inventory_out.edit', ['id' => $inventory->id]);
                $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
            }

            if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
            (auth()->guard('admin')->user()->hasAnyPermission('inventory_out.delete')))
            {
                $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $inventory->id ."' data-number='$inventory->code' ><i class='fas fa-xs fa-times  '></i></a>";
            }
        }

        return[
            'id' => $inventory->id,
            'created_at' => $createdAt,
            'date' => $date,
            'code' => $inventory->code,
            'warehouse_name' => $inventory->warehouse_name,
            'status' => $inventory->status->display,
            'status_id' => $inventory->status_id,
            'action' => $action,
            'updated_by' => $updatedByName,
            'updated_at' => $updatedAt
        ];
    }
}

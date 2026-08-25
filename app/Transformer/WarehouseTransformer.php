<?php


namespace App\Transformer;

use App\Models\AdminUser;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class WarehouseTransformer extends TransformerAbstract
{
    public function transform($warehouse){
        $date = Carbon::parse($warehouse->created_at)->toIso8601String();
        $updatedAt = $warehouse->updated_at == null ? $date : Carbon::parse($warehouse->updated_at)->toIso8601String();
        
        $updatedBy = $warehouse->updated_by == null ? $warehouse->created_by : $warehouse->updated_by;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;
        
        $urlShowMeta = route('admin.warehouse.edit', ['id' => $warehouse->id]);
        
        $action = "";
        
        if (auth()->user()->can('branch.scope_all') == 1 ||
        auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->user()->employee != null &&
        auth()->guard('admin')->user()->hasAnyPermission('warehouse.edit')))
        {
            $action = "<a class='btn btn-xs btn-primary' href='". $urlShowMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
        }
        
        if (auth()->user()->can('branch.scope_all') == 1 ||
        auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->user()->employee != null &&
        auth()->guard('admin')->user()->hasAnyPermission('warehouse.delete')))
        {
            $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $warehouse->id ."' data-number='". $warehouse->number ."' ><i class='fas fa-xs fa-times  '></i></a>";
        }  
        
        return[
            'created_at'    => $date,
            'name'          => $warehouse->name ?? '-',
            'code'          => $warehouse->code,
            'branch'        => $warehouse->branch->name,
            'status'        => $warehouse->status->display,
            'description'   => $warehouse->description ?? '-',
            'action'        => $action,
            'updated_at'    => $updatedAt,
            'updated_by'    => $updatedByName
        ];
    }
}

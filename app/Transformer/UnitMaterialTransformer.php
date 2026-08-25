<?php


namespace App\Transformer;

use App\Models\AdminUser;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class UnitMaterialTransformer extends TransformerAbstract
{
    public function transform($uom){
        $date = Carbon::parse($uom->created_at)->toIso8601String();
        $updatedAt = $uom->updated_at == null ? $date : Carbon::parse($uom->updated_at)->toIso8601String();
        
        $updatedBy = $uom->updatedBy == null ? $uom->createdAt : $uom->updatedBy;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;
        
        $urlShowMeta = route('admin.uom.edit', ['id' => $uom->id]);
        $action = '';
        if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->guard('admin')->user()->hasAnyPermission('item_unit.list')))
        {
            $action = "<a class='btn btn-xs btn-primary' href='". $urlShowMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
        }
        
        if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->guard('admin')->user()->hasAnyPermission('item_unit.delete')))
        {
            $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $uom->id ."' data-number='". $uom->number ."' ><i class='fas fa-xs fa-times  '></i></a>";
        }
        
        return[
            'created_at'    => $date,
            'name'          => $uom->name ?? '-',
            'branch'        => $uom->branch->name,
            'status'        => $uom->status->display,
            'description'   => $uom->description ?? '-',
            'action'        => $action,
            'updated_at'    => $updatedAt,
            'updated_by'    => $updatedByName
        ];
    }
}

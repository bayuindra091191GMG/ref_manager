<?php


namespace App\Transformer;

use App\Models\AdminUser;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ItemCategoryTransformer extends TransformerAbstract
{
    public function transform($uom){
        $date = Carbon::parse($uom->created_at)->toIso8601String();
        $updatedAt = $uom->updatedAt == null ? $date : Carbon::parse($uom->updatedAt)->toIso8601String();
        $updatedBy = $uom->updatedBy == null ? $uom->createdAt : $uom->updatedBy;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;
        
        $urlShowMeta = route('admin.item_category.edit', ['id' => $uom->id]);
        
        $action = "";
        if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->guard('admin')->user()->hasAnyPermission('item_category.edit')))
        {
            $action = "<a class='btn btn-xs btn-primary' href='". $urlShowMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
        }
        
        if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->guard('admin')->user()->hasAnyPermission('item_category.delete')))
        {
            $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $uom->id ."'><i class='fas fa-xs fa-times  '></i></a>";
        }
        
        return[
            'created_at'    => $date,
            'name'          => $uom->name ?? '-',
            //            'branch'        => $uom->branch->name,
            'status'        => $uom->status->display,
            'description'   => $uom->desc ?? '-',
            'action'        => $action,
            'updated_at'    => $updatedAt,
            'updated_by'    => $updatedByName
        ];
    }
}

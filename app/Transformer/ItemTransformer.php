<?php


namespace App\Transformer;

use App\Models\AdminUser;
use App\Models\ItemCategory;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform($item){
        $date = Carbon::parse($item->created_at)->toIso8601String();
        $updatedAt = $item->updated_at == null ? $date : Carbon::parse($item->updated_at)->toIso8601String();
        
        $updatedBy = $item->updated_by == null ? $item->created_by : $item->updated_by;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;
        
        $urlShowMeta = route('admin.item.edit', ['id' => $item->id]);
        $action = "";
        
        if (auth()->user()->can('branch.scope_all') == 1 ||
        auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->user()->employee != null &&
        auth()->guard('admin')->user()->hasAnyPermission('item.edit')))
        {
            $action = "<a class='btn btn-xs btn-primary' href='". $urlShowMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
            
        }
        
        if (auth()->user()->can('branch.scope_all') == 1 ||
        auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->user()->employee != null &&
        auth()->guard('admin')->user()->hasAnyPermission('item.delete')))
        {
            $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $item->id ."' data-number='". $item->code ."' ><i class='fas fa-xs fa-times  '></i></a>";
        }
        
        return[
            'created_at'    => $date,
            'name'          => $item->name ?? '-',
            'code'          => $item->code,
            'barcode'       => $item->barcode,
            'status'        => $item->status_name,
            'notes'        => $item->notes,
            'category_name'        => $item->category_name,
            'action'        => $action,
            'updated_at'    => $updatedAt,
            'updated_by'    => $updatedByName
        ];
    }
}

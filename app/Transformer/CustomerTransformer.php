<?php


namespace App\Transformer;

use App\Models\AdminUser;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class CustomerTransformer extends TransformerAbstract
{
    public function transform($data){
        $date = Carbon::parse($data->created_at)->toIso8601String();
        $updatedAt = $data->updated_at == null ? $date : Carbon::parse($data->updated_at)->toIso8601String();
        
        $updatedBy = $data->updated_by == null ? $data->created_by : $data->updated_by;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;
        
        $urlShowMeta = route('admin.customer.edit', ['id' => $data->id]);
        $action = "";
        
        if($data->status_id != 6)
        {
            if (auth()->user()->can('branch.scope_all') == 1 ||
            auth()->user()->hasRole('superadmin') == 1 ||
            (auth()->user()->employee != null &&
            auth()->guard('admin')->user()->hasAnyPermission('customer.edit')))
            {
                $action = "<a class='btn btn-xs btn-primary' href='". $urlShowMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
                
            }
            
            if (auth()->user()->can('branch.scope_all') == 1 ||
            auth()->user()->hasRole('superadmin') == 1 ||
            (auth()->user()->employee != null &&
            auth()->guard('admin')->user()->hasAnyPermission('customer.delete')))
            {
                $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' ><i class='fas fa-xs fa-times  '></i></a>";
                
            }
        }
        
        return[
            'created_at'    => $date,
            'name'          => $data->name ?? '-',
            'code'          => $data->code,
            'branch'        => $data->branch->name,
            'status'        => $data->status->display,
            'description'   => $data->description ?? '-',
            'action'        => $action,
            'updated_at'    => $updatedAt,
            'updated_by'    => $updatedByName
        ];
    }
}

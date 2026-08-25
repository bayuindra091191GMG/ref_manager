<?php


namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Models\AdminUser;
use App\Models\ItemCategory;

class ServicesTransformer extends TransformerAbstract
{
    public function transform($data){
        
        $urlEditMeta = route('admin.services.edit', ['id' => $data->id]);
        $urlShowMeta = route('admin.services.show', ['id' => $data->id]);
        
        $action = "";
        if (auth()->user()->can('branch.scope_all') == 1 ||
        auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->user()->employee != null &&
        auth()->guard('admin')->user()->hasAnyPermission('service.show')))
        {
            $action = "<a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";
        }
        
        if (auth()->user()->can('branch.scope_all') == 1 ||
        auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->user()->employee != null &&
        auth()->guard('admin')->user()->hasAnyPermission('service.edit')))
        {
            $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
        }
        
        if (auth()->user()->can('branch.scope_all') == 1 ||
        auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->user()->employee != null &&
        auth()->guard('admin')->user()->hasAnyPermission('service.delete')))
        {
            $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' data-number='$data->code' ><i class='fas fa-xs fa-times  '></i></a>";
        }
        
        
        $user_name = AdminUser::where('id', $data->updated_by)->first()->name;
        $category_name = ItemCategory::where('id', $data->category_id)->first()->name;
        return[
            'updated_at'        => Carbon::parse($data->updated_at)->locale('id')->isoFormat('D MMMM YYYY HH:mm'),
            'id'                => $data->id,
            'code'              => $data->code,
            'name'              => $data->name,
            'category'          =>  $category_name,
            'price'          => 'Rp. ' . number_format($data->price, 0, ',', '.'),
            'notes'              => $data->notes,
            'created_by'        => $data->created_by,
            'status_id'         => $data->status_id,
            'action'            => $action,
            'updated_by'        => $user_name
        ];
    }
}

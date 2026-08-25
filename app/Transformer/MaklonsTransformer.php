<?php


namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Models\AdminUser;

class MaklonsTransformer extends TransformerAbstract
{
    public function transform($data){
        $urlEditMeta = route('admin.maklons.edit', ['id' => $data->id]);
        $urlShowMeta = route('admin.maklons.show', ['id' => $data->id]);
        
        $action = "";
        
        if (auth()->user()->can('branch.scope_all') == 1 ||
        auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->user()->employee != null &&
        auth()->guard('admin')->user()->hasAnyPermission('maklon.show')))
        {
            $action = "<a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";
        }
        
        if (auth()->user()->can('branch.scope_all') == 1 ||
        auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->user()->employee != null &&
        auth()->guard('admin')->user()->hasAnyPermission('maklon.edit')))
        {
            $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
        }
        
        if (auth()->user()->can('branch.scope_all') == 1 ||
        auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->user()->employee != null &&
        auth()->guard('admin')->user()->hasAnyPermission('maklon.delete')))
        {
            // dd('dfsfs');
            $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' data-number='$data->code' ><i class='fas fa-xs fa-times  '></i></a>";
            
        }
        
        $user_name = !empty($data->created_by) ? AdminUser::where('id', $data->created_by)->first()->name : '-';
        
        return[
            'created_at'        => $data->created_at,
            'id'                => $data->id,
            'code'              => $data->code,
            'name'              => $data->name,
            'address1'          => $data->address1,
            'address2'          => $data->address2,
            'city'              => $data->city,
            'contact'           => $data->phone,
            // 'description'       => $data->description,
            'status_id'         => $data->status_id,
            'action'            => $action,
            'created_by'        => $user_name
        ];
    }
}

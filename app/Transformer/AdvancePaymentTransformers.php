<?php


namespace App\Transformer;

use App\Models\AdminUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use League\Fractal\TransformerAbstract;

class AdvancePaymentTransformers extends TransformerAbstract
{
    public function transform($data){
        $date = Carbon::parse($data->created_at)->toIso8601String();
        $updatedAt = $data->updated_at == null ? $date : Carbon::parse($data->updated_at)->toIso8601String();
        $dateRequired = Carbon::parse($data->date)->toIso8601String();
        
        $updatedBy = $data->updated_by == null ? $data->created_at : $data->updated_by;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;
        
        $urlEditMeta = route('admin.adv.edit', ['id' => $data->id]);
        $urlShowMeta = route('admin.adv.show', ['id' => $data->id]);
        $action = "";
        if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
        || (Auth::guard('admin')->user()->hasPermissionTo('advance_payment.show'))){
            $action .= "<a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";
        }
        if($data->status_id != 6){
            if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
            || (Auth::guard('admin')->user()->hasPermissionTo('advance_payment.edit'))){
                $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
            }
            if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
            || (Auth::guard('admin')->user()->hasPermissionTo('advance_payment.delete'))){
                $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' data-number='$data->code' ><i class='fas fa-xs fa-times  '></i></a>";
            }
        }
        
        return[
            'created_at'        => $date,
            'id'                => $data->id,
            'code'              => $data->code,
            'profit_center'     => $data->profit_center->name,
            'date'              => $dateRequired,
            'supplier_id'       => $data->supplier_id,
            'supplier_name'     => $data->supplier->name,
            'ref_number'        => $data->ref_number,
            'description'       => $data->description,
            'status_id'         => $data->status_id,
            'status'            => $data->status->display,
            'action'            => $action,
            'updated_at'        => $updatedAt,
            'updated_by'        => $updatedByName
        ];
    }
}

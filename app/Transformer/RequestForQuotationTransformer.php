<?php


namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Models\AdminUser;

class RequestForQuotationTransformer extends TransformerAbstract
{
    public function transform($data){
        $date = Carbon::parse($data->created_at)->toIso8601String();
        $updatedAt = $data->updated_at == null ? $date : Carbon::parse($data->updated_at)->toIso8601String();
        
        $updatedBy = $data->updated_by == null ? $data->created_by : $data->updated_by;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;

        $urlPrintMeta = route('admin.request_for_quotation.show', ['id' => $data->id]) . "?type=print";
        $urlEditMeta = route('admin.request_for_quotation.edit', ['id' => $data->id]);
        $urlShowMeta = route('admin.request_for_quotation.show', ['id' => $data->id]);
        
        $action = "";
        
        if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->guard('admin')->user()->hasAnyPermission('request_for_quotation.show')))
        {
            $action .= "<a class='btn btn-xs btn-dark' href='". $urlPrintMeta."' ><i class='fas fa-xs fa-print'></i></a>";
            $action = "&nbsp;<a class='btn btn-xs btn-info' href='". $urlShowMeta."' ><i class='fas fa-xs fa-info'></i></a>";
        }
        
        if($data->status_id == 1){
            if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
            (auth()->guard('admin')->user()->hasAnyPermission('request_for_quotation.edit')))
            {
                $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."' ><i class='fas fa-xs fa-pencil-alt'></i></a>";
            }
            
//            if (auth()->user()->is_super == 1 || auth()->user()->is_developer == 1 ||
//            (auth()->guard('admin')->user()->hasAnyPermission('request_for_quotation.delete')))
//            {
//                $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' data-number='$data->code'  ><i class='fas fa-xs fa-times  '></i></a>";
//            }
        }
        
        return[
            'created_at'        => $date,
            'id'                => $data->id,
            'code'              => $data->code,
            'reference_code'    => $data->reference_code,
            'profit_center_id'  => $data->profit_center_id,
            'profit_center_name' => $data->profit_center->name,
            'transaction_date'     => Carbon::parse($data->transaction_date)->toIso8601String(),
            'deadline_date'     => Carbon::parse($data->deadline_date)->toIso8601String(),
            'supplier_id'       => $data->supplier_id,
            'supplier_name'     => $data->supplier->name,
            'description'       => $data->description,
            'status_id'         => $data->status_id,
            'status'            => $data->status->display,
            'action'            => $action,
            'updated_at'        => $updatedAt,
            'updated_by'        => $updatedByName
            
        ];
    }
}

<?php


namespace App\Transformer;

use App\Models\AdminUser;
use App\Models\StockOpnameHeader;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class StockOpnameTransformer extends TransformerAbstract
{
    public function transform(StockOpnameHeader $data){
        $date = Carbon::parse($data->created_at)->toIso8601String();
        $date2 = Carbon::parse($data->transaction_date)->toIso8601String();
        $updatedAt = $data->updated_at == null ? $date : Carbon::parse($data->updated_at)->toIso8601String();
        
        $updatedBy = $data->updated_by == null ? $data->created_by : $data->updated_by;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;
        
        $urlEditMeta = route('admin.opname.edit', ['id' => $data->id]);
        $urlShowMeta = route('admin.opname.show', ['id' => $data->id]);
        $action = "&nbsp;<a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";
        if($data->status_id != 6){
            $action = "";
            
            if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
            (auth()->guard('admin')->user()->hasAnyPermission('stock_opname.edit')))
            {
                $action .= "<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
            }
            
            if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
            (auth()->guard('admin')->user()->hasAnyPermission('stock_opname.delete')))
            {
                $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' data-name='". $data->code ."' ><i class='fas fa-xs fa-times  '></i></a>";
            }
        }
        
        return[
            'id' => $data->id,
            'created_at'    => $date,
            'code'          => $data->code,
            'warehouse'     => $data->warehouse,
            'date'          => $data->transaction_date,
            'status_id'     => $data->status_id,
            'status'        => $data->display,
            'action'        => $action,
            'updated_at'    => $updatedAt,
            'updated_by'    => $updatedByName
        ];
    }
}

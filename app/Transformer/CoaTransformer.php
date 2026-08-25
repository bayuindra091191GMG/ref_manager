<?php
/**
* Created by PhpStorm.
* User: GMG-Developer
* Date: 13/02/2018
* Time: 11:34
*/

namespace App\Transformer;

use App\Models\AdminUser;
use App\Models\Coa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class CoaTransformer extends TransformerAbstract
{
    public function transform(Coa $coa){

        try{
            $createdDate = Carbon::parse($coa->created_at)->toIso8601String();
            $updatedAt = $coa->updated_at == null ? $createdDate : Carbon::parse($coa->updated_at)->toIso8601String();

            $updatedBy = $coa->updated_by == null ? $coa->created_by : $coa->updated_by;
            $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;

            $profitCenterName = $coa->profit_center == null ? "" : $coa->profit_center->name;

            $action = "";
            if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
            || (Auth::guard('admin')->user()->hasPermissionTo('coa.edit')))
            {
                $action = "<a class='btn btn-xs btn-info' href='edit/".$coa->id."' data-toggle='tooltip' data-placement='top'><i class='fa fa-pencil-alt'></i></a>";
            }

            if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
            || (Auth::guard('admin')->user()->hasPermissionTo('coa.delete')))
            {
                $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $coa->id ."' ><i class='fa fa-eraser'></i></a>";
            }


            return[
                'code'              => $coa->code,
                'name'              => $coa->name,
                'debit_credit'      => $coa->debit_credit == "D" ? "DEBIT" : "CREDIT",
                'type'              => $coa->type,
                'created_at'        => $createdDate,
                'action'            => $action,
                'updated_at'        => $updatedAt,
                'updated_by'        => $updatedByName,
                'profit_center'     => $profitCenterName
            ];
        }
        catch(\Exception $ex){
            Log::error('Admin/Coa - transform error EX: '. $ex);
            return null;
        }
    }
}

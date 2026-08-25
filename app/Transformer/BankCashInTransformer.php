<?php


namespace App\Transformer;

use App\Models\AdminUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use League\Fractal\TransformerAbstract;

class BankCashInTransformer extends TransformerAbstract
{
    public function transform($data){
        $date = Carbon::parse($data->created_at)->toIso8601String();
        $updatedAt = $data->updated_at == null ? $date : Carbon::parse($data->updated_at)->toIso8601String();
        $dateReceive = Carbon::parse($data->receive_date)->toIso8601String();

        $updatedBy = $data->updated_by == null ? $data->created_by : $data->updated_by;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;

        //        $dateRequired = Carbon::parse($data->required_date)->toIso8601String();

        $urlPrintMeta = route('admin.bank_cash_in.show', ['id' => $data->id]) . "?type=print";
        $urlEditMeta = route('admin.bank_cash_in.edit', ['id' => $data->id]);
        $urlShowMeta = route('admin.bank_cash_in.show', ['id' => $data->id]);
        $action = "";
        if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
        || (Auth::guard('admin')->user()->hasPermissionTo('bank_cash_in.show'))){
            $action .= "<a class='btn btn-xs btn-dark' href='". $urlPrintMeta."' ><i class='fas fa-xs fa-print'></i></a>";
            $action .= "&nbsp;<a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";
        }
        if($data->status_id != 13){
            if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
            || (Auth::guard('admin')->user()->hasPermissionTo('bank_cash_in.edit'))){
                $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
            }
            if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
            || (Auth::guard('admin')->user()->hasPermissionTo('bank_cash_in.delete'))){
                $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' data-number='$data->code' ><i class='fas fa-xs fa-times  '></i></a>";
            }
        }

        return[
            'created_at'        => $date,
            'id'                => $data->id,
            'code'              => $data->code,
            'coa'               => $data->coa_bank_code . " - " .$data->coa_bank_name,
            'total_price'       => $data->total_price,
            'total_price_str'   => number_format($data->total_price, 2,',', '.'),
            'receive_date'        => $dateReceive,
            //            'required_date'     => $dateRequired,
            //            'supplier_id'       => $data->supplier_id,
            //            'supplier_name'     => $data->supplier->name,
            //            'warehouse_id'      => $data->warehouse_id,
            //            'warehouse_name'    => $data->warehouse->name,
            'description'       => $data->description,
            'status_id'         => $data->status_id,
            'status'            => $data->status,
            'action'            => $action,
            'updated_at'        => $updatedAt,
            'updated_by'        => $updatedByName
        ];
    }
}

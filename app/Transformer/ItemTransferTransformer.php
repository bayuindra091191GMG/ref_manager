<?php


namespace App\Transformer;

use App\Models\AdminUser;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ItemTransferTransformer extends TransformerAbstract
{
    public function transform($warehouse){
        $date = Carbon::parse($warehouse->created_at)->toIso8601String();
        $updatedAt = $warehouse->updated_at != null ? Carbon::parse($warehouse->updated_at)->toIso8601String() : $date;

        $updatedBy = $warehouse->updated_by != null ? $warehouse->updated_by : $warehouse->created_by;
        $updatedByName = $updatedBy == null ? AdminUser::find(1)->name : AdminUser::find($updatedBy)->name;

        // $urlPrintMeta = route('admin.item_transfer.print', ['id' => $warehouse->id]);
        $urlEditMeta = route('admin.item_transfer.edit', ['id' => $warehouse->id]);
        $urlShowMeta = route('admin.item_transfer.show', ['id' => $warehouse->id]);
        $action = "&nbsp;<a class='btn btn-xs btn-info' href='". $urlShowMeta."'><i class='fas fa-xs fa-info'></i></a>";

        if($warehouse->status_id != 6 && $warehouse->status_id != 17){
            $action = "";

            if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
            (auth()->guard('admin')->user()->hasAnyPermission('item_transfer.edit')))
            {
                // $action .= "<a class='btn btn-xs btn-dark' href='". $urlPrintMeta."' ><i class='fas fa-xs fa-print'></i></a>";
                $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";

            }

            if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
            (auth()->guard('admin')->user()->hasAnyPermission('item_transfer.delete')))
            {
                $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $warehouse->id ."' data-number='$warehouse->code' ><i class='fas fa-xs fa-times  '></i></a>";

            }
        }

        return[
            'id'    => $warehouse->id,
            'created_at'    => $date,
            'code'          => $warehouse->code,
            'warehouse_origin_name'        => $warehouse->warehouse_origin_name,
            'warehouse_destination_name'   => $warehouse->warehouse_destination_name,
            // 'reference_code'   => $warehouse->reference_code,
            'status'        => $warehouse->status,
            'status_id'         => $warehouse->status_id,
            'action'        => $action,
            'updated_at'    => $updatedAt,
            'updated_by'    => $updatedByName
        ];
    }
}

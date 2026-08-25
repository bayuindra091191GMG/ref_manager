<?php
/**
 * Created by PhpStorm.
 * User: GMG-Developer
 * Date: 13/02/2018
 * Time: 11:34
 */

namespace App\Transformer;


use App\Models\CommissionItemManualHeader;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class CommissionItemManualTransformer extends TransformerAbstract
{
    public function transform($itemCommission){
        $date = Carbon::parse($itemCommission->date)->toIso8601String();

        $action = "<a class='btn btn-xs btn-info' href='#'><i class='fa fa-info'></i></a>";
        $action .= "&nbsp;<button type='button' class='btn btn-xs btn-danger btn-delete' data-id='". $itemCommission->id. "'><i class='fa fa-trash'></i></button>";

        return[
            'employee_name' => $itemCommission->employee_name,
            'employee_code' => $itemCommission->employee_code,
            'partner_number' => $itemCommission->partner_number,
            'partner_alias' => $itemCommission->partner_alias,
            'total_qty' => $itemCommission->total_qty,
            'total_commission_amount' => $itemCommission->total_commission_amount,
            'date' => $date,
            'action' => $action,
        ];
    }
}

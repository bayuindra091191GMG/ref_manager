<?php
/**
 * Created by PhpStorm.
 * User: GMG-Developer
 * Date: 13/02/2018
 * Time: 11:34
 */

namespace App\Transformer;


use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class CouponTransformer extends TransformerAbstract
{
    public function transform(Coupon $coupon){
        try{
            $createdDate = Carbon::parse($coupon->created_at)->format('d M Y');
            $updatedDate = Carbon::parse($coupon->updated_at)->format('d M Y');

            $action = "<a class='btn btn-xs btn-info' href='edit/".$coupon->id."' data-toggle='tooltip' data-placement='top'><i class='fa fa-pencil-alt'></i></a>";
            $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $coupon->id ."' ><i class='fa fa-eraser'></i></a>";


            return[
                'code'                  => $coupon->code,
                'discount_amount'       => $coupon->discount_amount,
                'discount_percentage'   => $coupon->discount_percentage,
                'created_by'            => $coupon->createdBy->name,
                'created_at'            => $createdDate,
                'updated_by'            => $coupon->updatedBy->name,
                'updated_at'            => $updatedDate,
                'action'                => $action
            ];
        }
        catch(\Exception $ex){
            Log::error('Admin/Coa - transform error EX: '. $ex);
            return null;
        }
    }
}

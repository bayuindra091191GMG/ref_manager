<?php
/**
 * Created by PhpStorm.
 * User: GMG-Developer
 * Date: 13/02/2018
 * Time: 11:34
 */

namespace App\Transformer;


use App\Models\Coa;
use App\Models\Coupon;
use App\Models\CouponList;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class VoucherExcelTransformer extends TransformerAbstract
{
    public function transform(CouponList $voucher){
        try{
            $type = 'percentage';
            if($voucher->coupon->discount_amount > 0){
                $type = 'amount';
            }

            $createdDate = Carbon::parse($voucher->created_at)->format('d M Y');
            $updatedDate = Carbon::parse($voucher->updated_at)->format('d M Y');
            $expDate = '-';
            $expDateFormat = Carbon::now('Asia/Jakarta')->format('d-m-Y');

            $isUsed = 'Belum dipakai';
            if($voucher->is_used == 1){
                $isUsed = 'Sudah dipakai';
            }
            $isExpired = 'Belum Expired';
            if($voucher->exp_date != null){
                $expDate = Carbon::parse($voucher->exp_date)->format('d M Y');
                $expDateFormat = Carbon::parse($voucher->exp_date)->format('d-m-Y');
                if($voucher->exp_date <= Carbon::now('Asia/Jakarta')){
                    $isExpired = 'Sudah Expired';
                }
            }

            return[
                'code'                  => $voucher->code,
                'voucher_type'          => $voucher->coupon->code,
                'discount_amount'       => $voucher->coupon->discount_amount,
                'discount_percentage'   => $voucher->coupon->discount_percentage,
                'created_by'            => $voucher->createdBy->name,
                'created_at'            => $createdDate,
                'updated_by'            => $voucher->updatedBy->name,
                'updated_at'            => $updatedDate,
                'is_used'               => $isUsed,
                'is_expired'            => $isExpired,
                'exp_date'              => $expDate,
                'type'                  => $type
            ];
        }
        catch(\Exception $ex){
            Log::error('Admin/Voucher - transform error EX: '. $ex);
            return null;
        }
    }
}

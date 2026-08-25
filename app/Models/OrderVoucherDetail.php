<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderVoucherDetail extends Model
{
	protected $table = 'order_voucher_details';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function header()
	{
		return $this->belongsTo(OrderVoucherHeader::class, 'header_id');
	}

    public function coupon()
	{
		return $this->belongsTo(Coupon::class, 'voucher_id', 'id');
	}
}

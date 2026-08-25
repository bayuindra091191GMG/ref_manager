<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderVoucherPayment extends Model
{
	protected $table = 'order_voucher_payments';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function header()
	{
		return $this->belongsTo(OrderVoucherHeader::class);
	}
}

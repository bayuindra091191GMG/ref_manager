<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderVoucherHeader extends Model
{
	protected $table = 'order_voucher_headers';

    protected $casts = [
        'grand_total' => 'float',
        'payment_charge_ppn_percentage' => 'float',
        'payment_charge_ppn_value' => 'float',
        'total_payment' => 'float',
        'payment_due_at' => 'datetime',
        'payment_reminded_at' => 'datetime',
        'payment_at' => 'datetime',
        'payment_approve_at' => 'datetime'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function details()
	{
		return $this->hasMany(OrderVoucherDetail::class, 'header_id');
	}

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }
}

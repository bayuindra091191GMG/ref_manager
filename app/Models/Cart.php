<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
	protected $table = 'carts';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function coupon()
	{
		return $this->belongsTo(Coupon::class, 'voucher_id');
	}
}

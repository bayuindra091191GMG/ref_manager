<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherPromoCode extends Model
{
	protected $table = 'voucher_promo_codes';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];
}

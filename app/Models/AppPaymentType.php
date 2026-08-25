<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppPaymentType extends Model
{
	protected $table = 'app_payment_types';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];
}

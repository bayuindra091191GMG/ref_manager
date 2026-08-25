<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class VoucherPackage extends Authenticatable
{
    protected $table = 'voucher_packages';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function details()
    {
        return $this->hasMany(VoucherPackageDetail::class, 'header_id');
    }
}

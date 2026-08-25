<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class VoucherPackageDetail extends Authenticatable
{
    protected $table = 'voucher_package_details';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function header()
    {
        return $this->belongsTo(VoucherPackage::class, 'header_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
}

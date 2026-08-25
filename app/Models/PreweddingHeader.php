<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class PreweddingHeader extends Authenticatable
{
    protected $table = 'prewedding_headers';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id')->withTrashed();
    }

    public function package()
    {
        return $this->belongsTo(PreweddingPackage::class, 'package_id');
    }

    public function details()
    {
        return $this->hasMany(PreweddingDetail::class, 'header_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}

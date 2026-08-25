<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class PreweddingPackage extends Authenticatable
{
    protected $table = 'prewedding_packages';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];
}

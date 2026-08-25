<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class PreweddingDetail extends Authenticatable
{
    protected $table = 'prewedding_details';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function header()
    {
        return $this->belongsTo(PreweddingHeader::class, 'header_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}

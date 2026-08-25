<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class PreweddingReceipt extends Authenticatable
{
    protected $table = 'prewedding_receipts';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function header()
    {
        return $this->belongsTo(PreweddingHeader::class, 'header_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function details()
    {
        return $this->hasMany(PreweddingDetail::class, 'receipt_id');
    }
}

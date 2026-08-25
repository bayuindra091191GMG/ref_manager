<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionInvoice extends Model
{
    protected $table = 'transaction_invoices';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function transaction_payment()
    {
        return $this->hasOne(TransactionPayment::class, 'invoice_id');
    }
    public function transactionHeader()
    {
        return $this->hasOne(TransactionHeader::class, 'invoice_id');
    }
    
}

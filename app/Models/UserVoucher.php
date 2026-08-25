<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVoucher extends Model
{
	protected $table = 'user_vouchers';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withTrashed();
    }

    public function createdBy()
    {
        return $this->belongsTo(AdminUser::class, 'created_by', 'id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }

    public function coupon()
	{
		return $this->belongsTo(Coupon::class, 'voucher_id', 'id');
	}

    public function voucher_package()
    {
        return $this->belongsTo(VoucherPackage::class, 'voucher_id', 'id');
    }

    public function user_voucher_code()
    {
        return $this->hasMany(UserVoucherCode::class, 'header_id', 'id');
    }
}

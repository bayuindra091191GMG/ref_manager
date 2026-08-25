<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
	protected $table = 'coupons';

	protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

    public function createdBy()
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

	public function admin_user()
	{
		return $this->belongsTo(AdminUser::class, 'updated_by');
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function coupon_lists()
	{
		return $this->hasMany(CouponList::class);
	}

	public function packages()
	{
		return $this->belongsToMany(Package::class, 'coupon_package_links')
					->withPivot('id')
					->withTimestamps();
	}
}

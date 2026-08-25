<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
	protected $table = 'banners';

	protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CouponPackageLink
 * 
 * @property int $id
 * @property int|null $coupon_id
 * @property int|null $package_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Coupon|null $coupon
 * @property Package|null $package
 *
 * @package App\Models
 */
class CouponPackageLink extends Model
{
	protected $table = 'coupon_package_links';

	protected $casts = [
		'coupon_id' => 'int',
		'package_id' => 'int'
	];

	protected $fillable = [
		'coupon_id',
		'package_id'
	];

	public function coupon()
	{
		return $this->belongsTo(Coupon::class);
	}

	public function package()
	{
		return $this->belongsTo(Package::class);
	}
}

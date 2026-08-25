<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AppMembershipBonusCoupon
 * 
 * @property int $id
 * @property int|null $user_id
 * @property string|null $user_phone
 * @property string|null $user_name
 * @property int|null $coupon_list_id
 * @property string|null $coupon_code
 * @property string|null $membership_before
 * @property string|null $membership_after
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property CouponList|null $coupon_list
 *
 * @package App\Models
 */
class AppMembershipBonusCoupon extends Model
{
	protected $table = 'app_membership_bonus_coupons';

	protected $casts = [
		'user_id' => 'int',
		'coupon_list_id' => 'int',
		'start_date' => 'datetime',
		'end_date' => 'datetime'
	];

	protected $fillable = [
		'user_id',
		'user_phone',
		'user_name',
		'coupon_list_id',
		'coupon_code',
		'membership_before',
		'membership_after',
		'start_date',
		'end_date'
	];

	public function coupon_list()
	{
		return $this->belongsTo(CouponList::class);
	}
}

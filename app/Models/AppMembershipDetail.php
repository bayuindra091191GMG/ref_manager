<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AppMembershipDetail
 *
 * @property int $id
 * @property int|null $membership_id
 * @property string|null $type
 * @property float|null $discount_percentage
 * @property float|null $max_discount_amount
 * @property int|null $max_discount_usage
 * @property float|null $bonus_point_percentage
 * @property float|null $max_bonus_point
 * @property float|null $birthday_discount_percentage
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property AppMembership|null $app_membership
 *
 * @package App\Models
 */
class AppMembershipDetail extends Model
{
	protected $table = 'app_membership_details';

	protected $casts = [
		'membership_id' => 'int',
		'discount_percentage' => 'float',
		'max_discount_amount' => 'float',
		'max_discount_usage' => 'int',
		'bonus_point_percentage' => 'float',
		'max_bonus_point' => 'float',
        'birthday_discount_percentage' => 'float'
	];

	protected $fillable = [
		'membership_id',
		'type',
		'discount_percentage',
		'max_discount_amount',
		'max_discount_usage',
		'bonus_point_percentage',
		'max_bonus_point',
        'birthday_discount_percentage'
	];

	public function app_membership()
	{
		return $this->belongsTo(AppMembership::class, 'membership_id');
	}
}

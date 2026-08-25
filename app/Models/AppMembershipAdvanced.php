<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AppMembershipAdvanced
 * 
 * @property int $id
 * @property int|null $membership_id
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property float|null $discount_percentage
 * @property float|null $max_discount_amount
 * @property int|null $max_discount_usage
 * @property float|null $bonus_point_percentage
 * @property float|null $max_bonus_point
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property AppMembership|null $app_membership
 *
 * @package App\Models
 */
class AppMembershipAdvanced extends Model
{
	protected $table = 'app_membership_advanceds';

	protected $casts = [
		'membership_id' => 'int',
		'reference_id' => 'int',
		'discount_percentage' => 'float',
		'max_discount_amount' => 'float',
		'max_discount_usage' => 'int',
		'bonus_point_percentage' => 'float',
		'max_bonus_point' => 'float',
		'status_id' => 'int'
	];

	protected $fillable = [
		'membership_id',
		'reference_type',
		'reference_id',
		'discount_percentage',
		'max_discount_amount',
		'max_discount_usage',
		'bonus_point_percentage',
		'max_bonus_point',
		'status_id'
	];

	public function app_membership()
	{
		return $this->belongsTo(AppMembership::class, 'membership_id');
	}
}

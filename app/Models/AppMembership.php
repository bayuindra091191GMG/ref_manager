<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AppMembership
 * 
 * @property int $id
 * @property string|null $code
 * @property string|null $name
 * @property float|null $min_spending
 * @property float|null $discount_percentage
 * @property float|null $max_discount_amount
 * @property int|null $max_discount_usage
 * @property float|null $bonus_point_percentage
 * @property float|null $max_bonus_point
 * @property string|null $description
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|AppMembershipBenefit[] $app_membership_benefits
 * @property Collection|AppMembershipDetail[] $app_membership_details
 * @property Collection|AppMembershipDiscount[] $app_membership_discounts
 *
 * @package App\Models
 */
class AppMembership extends Model
{
	protected $table = 'app_memberships';

	protected $casts = [
		'min_spending' => 'float',
		'discount_percentage' => 'float',
		'max_discount_amount' => 'float',
		'max_discount_usage' => 'int',
		'bonus_point_percentage' => 'float',
		'max_bonus_point' => 'float',
		'status_id' => 'int'
	];

	protected $fillable = [
		'code',
		'name',
		'min_spending',
		'discount_percentage',
		'max_discount_amount',
		'max_discount_usage',
		'bonus_point_percentage',
		'max_bonus_point',
		'description',
		'status_id'
	];

	public function app_membership_benefits()
	{
		return $this->hasMany(AppMembershipBenefit::class, 'membership_id');
	}

	public function app_membership_details()
	{
		return $this->hasMany(AppMembershipDetail::class, 'membership_id');
	}

	public function app_membership_discounts()
	{
		return $this->hasMany(AppMembershipDiscount::class, 'membership_id');
	}
}

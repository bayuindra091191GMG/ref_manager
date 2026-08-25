<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MembershipBenefit
 * 
 * @property int $id
 * @property int|null $membership_id
 * @property string|null $type
 * @property float|null $discount_percentage
 * @property float|null $discount_flat
 * @property int|null $free_amount
 * @property int|null $usage_count
 * @property int|null $item_category_id
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property Membership|null $membership
 *
 * @package App\Models
 */
class MembershipBenefit extends Model
{
	protected $table = 'membership_benefits';

	protected $casts = [
		'membership_id' => 'int',
		'discount_percentage' => 'float',
		'discount_flat' => 'float',
		'free_amount' => 'int',
		'usage_count' => 'int',
		'item_category_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'membership_id',
		'type',
		'discount_percentage',
		'discount_flat',
		'free_amount',
		'usage_count',
		'item_category_id',
		'created_by',
		'updated_by'
	];

	public function membership()
	{
		return $this->belongsTo(Membership::class);
	}
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AppMembershipBenefit
 * 
 * @property int $id
 * @property int|null $membership_id
 * @property int|null $index
 * @property string|null $text
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property AppMembership|null $app_membership
 *
 * @package App\Models
 */
class AppMembershipBenefit extends Model
{
	protected $table = 'app_membership_benefits';

	protected $casts = [
		'membership_id' => 'int',
		'index' => 'int'
	];

	protected $fillable = [
		'membership_id',
		'index',
		'text'
	];

	public function app_membership()
	{
		return $this->belongsTo(AppMembership::class, 'membership_id');
	}
}

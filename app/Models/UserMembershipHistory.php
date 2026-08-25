<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserMembershipHistory
 * 
 * @property int $id
 * @property int|null $user_id
 * @property string|null $user_name
 * @property string|null $user_phone
 * @property string|null $user_email
 * @property int|null $membership_id
 * @property string|null $membership_name
 * @property Carbon|null $start_at
 * @property Carbon|null $end_at
 * @property Carbon|null $expired_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class UserMembershipHistory extends Model
{
	protected $table = 'user_membership_histories';

	protected $casts = [
		'user_id' => 'int',
		'membership_id' => 'int',
		'start_at' => 'datetime',
		'end_at' => 'datetime',
		'expired_at' => 'datetime'
	];

	protected $fillable = [
		'user_id',
		'user_name',
		'user_phone',
		'user_email',
		'membership_id',
		'membership_name',
		'start_at',
		'end_at',
		'expired_at'
	];
}

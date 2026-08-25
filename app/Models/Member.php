<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Member
 * 
 * @property int $id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $email
 * @property Carbon|null $join_date
 * @property int|null $duration
 * @property int|null $is_infinite
 * @property int|null $membership_id
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * @property int|null $is_active
 * 
 * @property AdminUser|null $admin_user
 * @property Membership|null $membership
 *
 * @package App\Models
 */
class Member extends Model
{
	protected $table = 'members';

	protected $casts = [
		'duration' => 'int',
		'is_infinite' => 'int',
		'membership_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int',
		'is_active' => 'int'
	];

	protected $dates = [
		'join_date'
	];

	protected $fillable = [
		'code',
		'name',
		'phone',
		'address',
		'email',
		'join_date',
		'duration',
		'is_infinite',
		'membership_id',
		'created_by',
		'updated_by',
		'is_active'
	];

	public function admin_user()
	{
		return $this->belongsTo(AdminUser::class, 'updated_by');
	}

	public function membership()
	{
		return $this->belongsTo(Membership::class);
	}
}

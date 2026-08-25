<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Contracts\OAuthenticatable;

/**
 * Class User
 *
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $status_id
 * @property string $name
 * @property string $phone
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $otp_code
 * @property string|null $image_path
 * @property int|null $point
 * @property Carbon|null $active_at
 * @property string|null $forgot_password_otp_code
 * @property Carbon|null $forgot_password_otp_expired_at
 * @property string|null $forgot_password_unique_code
 * @property int|null $membership_id
 * @property string|null $membership_status
 * @property Carbon|null $membership_start_at
 * @property Carbon|null $birthday_date
 *
 * @property Branch|null $branch
 * @property Status|null $status
 *
 * @package App\Models
 */
class User extends Authenticatable implements OAuthenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
	protected $table = 'users';

	protected $casts = [
		'branch_id' => 'int',
        'point' => 'int'
	];

	protected $dates = [
		'email_verified_at',
        'birthday_date',
        'deleted_at'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $guarded = [
		'id',
		'created_at',
		'updated_at',
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function membership()
    {
        return$this->belongsTo(AppMembership::class, 'membership_id', 'id');
    }
}

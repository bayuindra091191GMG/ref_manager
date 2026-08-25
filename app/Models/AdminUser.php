<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;


/**
 * Class AdminUser
 *
 * @property int $id
 * @property int|null $branch_id
 * @property string $username
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property bool|null $is_super
 * @property bool|null $is_developer
 * @property bool|null $is_bod
 * @property bool|null $is_finance
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Branch|null $branch
 * @property Status|null $status
 * @property Collection|Employee[] $employees
 * @property Collection|TransactionHeader[] $transaction_headers
 *
 * @package App\Models
 */
class AdminUser extends Authenticatable
{
    use HasRoles, HasPermissions;

	protected $table = 'admin_users';

	protected $casts = [
		'branch_id' => 'int',
		'status_id' => 'int',
        'is_bod' => 'bool',
        'is_finance' => 'bool'
	];

	protected $dates = [
		'email_verified_at'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'branch_id',
        'username',
		'name',
		'email',
		'email_verified_at',
		'password',
		'remember_token',
        'is_bod',
		'status_id',
        'is_finance'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function employee()
	{
		return $this->hasOne(Employee::class, 'admin_user_id');
	}

	public function transaction_headers()
	{
		return $this->hasMany(TransactionHeader::class, 'updated_by');
	}

    protected function isSuper(): Attribute
    {
        return Attribute::get(fn (): bool => $this->can('branch.scope_all'));
    }

    protected function isDeveloper(): Attribute
    {
        return Attribute::get(fn (): bool => $this->hasRole('superadmin'));
    }
}

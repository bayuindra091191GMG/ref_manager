<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AuthorizationPosition
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $position_id
 * @property int|null $auth_module_id
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property AuthorizationModule|null $authorization_module
 * @property Branch|null $branch
 * @property EmployeePosition|null $employee_position
 *
 * @package App\Models
 */
class AuthorizationPosition extends Model
{
	protected $table = 'authorization_positions';

	protected $casts = [
		'branch_id' => 'int',
		'position_id' => 'int',
		'auth_module_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'position_id',
		'auth_module_id',
		'created_by',
		'updated_by'
	];

	public function authorization_module()
	{
		return $this->belongsTo(AuthorizationModule::class, 'auth_module_id');
	}

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function employee_position()
	{
		return $this->belongsTo(EmployeePosition::class, 'position_id');
	}
}

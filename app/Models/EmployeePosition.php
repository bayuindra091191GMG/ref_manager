<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EmployeePosition
 *
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $description
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property int|null $updated_by
 * @property Carbon|null $updated_at
 *
 * @property Branch|null $branch
 * @property Collection|AuthorizationPosition[] $authorization_positions
 * @property Collection|Employee[] $employees
 *
 * @package App\Models
 */
class EmployeePosition extends Model
{

	protected $table = 'employee_positions';


	protected $casts = [
		'branch_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'code',
		'name',
		'description',
		'created_by',
		'updated_by'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function authorization_positions()
	{
		return $this->hasMany(AuthorizationPosition::class, 'position_id');
	}

	public function employees()
	{
		return $this->hasMany(Employee::class, 'position_id');
	}
}

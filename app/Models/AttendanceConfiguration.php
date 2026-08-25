<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AttendanceConfiguration
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $department_id
 * @property string|null $type
 * @property float|null $late_penalty_amount
 * @property float|null $alpha_penalty_amount
 * @property int|null $late_chance
 * @property int|null $alpha_chance
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property Branch|null $branch
 * @property Department|null $department
 *
 * @package App\Models
 */
class AttendanceConfiguration extends Model
{
	protected $table = 'attendance_configurations';

	protected $casts = [
		'branch_id' => 'int',
		'department_id' => 'int',
		'late_penalty_amount' => 'float',
		'alpha_penalty_amount' => 'float',
		'late_chance' => 'int',
		'alpha_chance' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'department_id',
		'type',
		'late_penalty_amount',
		'alpha_penalty_amount',
		'late_chance',
		'alpha_chance',
		'created_by',
		'updated_by'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function department()
	{
		return $this->belongsTo(Department::class);
	}
}

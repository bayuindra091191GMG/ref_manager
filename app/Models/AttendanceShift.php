<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AttendanceShift
 * 
 * @property int $id
 * @property int|null $department_id
 * @property int|null $shift_index
 * @property string|null $shift_name
 * @property string|null $time_start
 * @property string|null $time_end
 * @property bool|null $is_end_at_closing
 * @property bool|null $is_next_day
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property Department|null $department
 *
 * @package App\Models
 */
class AttendanceShift extends Model
{
	protected $table = 'attendance_shifts';

	protected $casts = [
		'department_id' => 'int',
		'shift_index' => 'int',
		'is_end_at_closing' => 'bool',
		'is_next_day' => 'bool',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'department_id',
		'shift_index',
		'shift_name',
		'time_start',
		'time_end',
		'is_end_at_closing',
		'is_next_day',
		'created_by',
		'updated_by'
	];

	public function department()
	{
		return $this->belongsTo(Department::class);
	}
}

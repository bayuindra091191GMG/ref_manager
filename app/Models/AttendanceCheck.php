<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AttendanceCheck
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $branch_name
 * @property string|null $branch_code
 * @property string|null $type
 * @property Carbon|null $current_date
 * @property int|null $employee_id
 * @property string|null $employee_code
 * @property string|null $employee_name
 * @property string|null $employee_phone
 * @property string|null $employee_department
 * @property int|null $partner_id
 * @property string|null $partner_code
 * @property string|null $partner_number
 * @property string|null $partner_name
 * @property string|null $partner_alias
 * @property string|null $raw
 * @property bool|null $is_attend
 * @property bool|null $is_late
 * @property bool|null $is_alpha
 * @property bool|null $is_long_shift
 * @property bool|null $is_leave
 * @property bool|null $is_off
 * @property int|null $long_shift_hour
 * @property int|null $long_shift_minutes
 * @property int|null $late_hour
 * @property int|null $late_minutes
 * @property float|null $alpha_penalty_amount
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 *
 * @package App\Models
 */
class AttendanceCheck extends Model
{
	protected $table = 'attendance_checks';

	protected $casts = [
		'branch_id' => 'int',
		'employee_id' => 'int',
		'partner_id' => 'int',
		'is_attend' => 'bool',
		'is_late' => 'bool',
		'is_alpha' => 'bool',
		'is_long_shift' => 'bool',
		'is_leave' => 'bool',
		'is_off' => 'bool',
		'long_shift_hour' => 'int',
		'long_shift_minutes' => 'int',
		'late_hour' => 'int',
		'late_minutes' => 'int',
		'alpha_penalty_amount' => 'float',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'current_date'
	];

	protected $fillable = [
		'branch_id',
		'branch_name',
		'branch_code',
		'type',
		'current_date',
		'employee_id',
		'employee_code',
		'employee_name',
		'employee_phone',
		'employee_department',
		'partner_id',
		'partner_code',
		'partner_number',
		'partner_name',
		'partner_alias',
		'raw',
		'is_attend',
		'is_late',
		'is_alpha',
		'is_long_shift',
		'is_leave',
		'is_off',
		'long_shift_hour',
		'long_shift_minutes',
		'late_hour',
		'late_minutes',
		'alpha_penalty_amount',
		'created_by',
		'updated_by'
	];
}

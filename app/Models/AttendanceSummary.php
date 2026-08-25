<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AttendanceSummary
 *
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $branch_code
 * @property string|null $branch_name
 * @property string|null $type
 * @property Carbon|null $date_period
 * @property int|null $month_period
 * @property string|null $year_period
 * @property int|null $employee_id
 * @property string|null $employee_name
 * @property string|null $employee_code
 * @property string|null $employee_department
 * @property int|null $partner_id
 * @property string|null $partner_name
 * @property string|null $partner_code
 * @property string|null $partner_number
 * @property string|null $partner_alias
 * @property int|null $total_attend_count
 * @property int|null $total_late_count
 * @property int|null $total_alpha_count
 * @property float|null $total_late_penalty_amount
 * @property float|null $total_alpha_penalty_amount
 * @property float|null $grand_total_penaly_amount
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $total_off_count
 * @property int|null $total_leave_count
 * @property int|null $total_long_shift_count
 * @property int|null $total_long_shift_hours
 * @property int|null $total_long_shift_minutes
 * @property int|null $total_late_hours
 * @property int|null $total_late_minutes
 *
 * @package App\Models
 */
class AttendanceSummary extends Model
{
	protected $table = 'attendance_summaries';
    protected $connection = 'hrd';

	protected $casts = [
		'branch_id' => 'int',
		'month_period' => 'int',
		'employee_id' => 'int',
		'partner_id' => 'int',
		'total_attend_count' => 'int',
		'total_late_count' => 'int',
		'total_alpha_count' => 'int',
		'total_late_penalty_amount' => 'float',
		'total_alpha_penalty_amount' => 'float',
		'grand_total_penaly_amount' => 'float',
		'status_id' => 'int',
		'total_off_count' => 'int',
		'total_leave_count' => 'int',
		'total_long_shift_count' => 'int',
		'total_long_shift_hours' => 'int',
		'total_long_shift_minutes' => 'int',
		'total_late_hours' => 'int',
		'total_late_minutes' => 'int'
	];

	protected $dates = [
		'date_period'
	];

	protected $fillable = [
		'branch_id',
		'branch_code',
		'branch_name',
		'type',
		'date_period',
		'month_period',
		'year_period',
		'employee_id',
		'employee_name',
		'employee_code',
		'employee_department',
		'partner_id',
		'partner_name',
		'partner_code',
		'partner_number',
		'partner_alias',
		'total_attend_count',
		'total_late_count',
		'total_alpha_count',
		'total_late_penalty_amount',
		'total_alpha_penalty_amount',
		'grand_total_penaly_amount',
		'status_id',
		'total_off_count',
		'total_leave_count',
		'total_long_shift_count',
		'total_long_shift_hours',
		'total_long_shift_minutes',
		'total_late_hours',
		'total_late_minutes'
	];
}

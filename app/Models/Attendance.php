<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Attendance
 *
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $branch_code
 * @property string|null $branch_name
 * @property int|null $employee_id
 * @property string|null $employee_code
 * @property string|null $employee_name
 * @property string|null $employee_phone
 * @property string|null $employee_department
 * @property int|null $partner_id
 * @property string|null $partner_number
 * @property string|null $partner_name
 * @property string|null $partner_phone
 * @property Carbon|null $current_date
 * @property Carbon|null $in_time
 * @property Carbon|null $out_time
 * @property bool|null $is_late
 * @property int|null $status_id
 * @property string|null $udf_01
 * @property string|null $udf_02
 * @property string|null $udf_03
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * @property string|null $img_path
 *
 * @property Branch|null $branch
 *
 * @package App\Models
 */
class Attendance extends Model
{
	protected $table = 'attendances';
    protected $connection = 'hrd';

	protected $casts = [
		'branch_id' => 'int',
		'employee_id' => 'int',
		'partner_id' => 'int',
		'is_late' => 'bool',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'current_date',
		'in_time',
		'out_time'
	];

	protected $fillable = [
		'branch_id',
        'branch_name',
        'branch_code',
		'employee_id',
		'employee_code',
		'employee_name',
		'employee_phone',
		'employee_department',
		'partner_id',
		'partner_number',
		'partner_name',
		'partner_phone',
		'current_date',
		'in_time',
		'out_time',
		'is_late',
		'status_id',
		'udf_01',
		'udf_02',
		'udf_03',
		'created_by',
		'updated_by',
		'img_path',
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}
}

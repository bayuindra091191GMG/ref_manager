<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class EmployeeContract
 *
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $employee_id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $marital_status
 * @property string|null $gender
 * @property string|null $education
 * @property string|null $address
 * @property string|null $description
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property int|null $updated_by
 * @property Carbon|null $updated_at
 * @property string|null $ktp_image_path
 * @property string|null $pkwt_image_path
 *
 * @property Branch|null $branch
 * @property Employee|null $employee
 *
 * @package App\Models
 */
class EmployeeContract extends Model
{
    use SoftDeletes;

	protected $table = 'employee_contracts';

	protected $casts = [
		'branch_id' => 'int',
		'employee_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

    protected $dates = [
        'start_date',
        'end_date'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function employee()
	{
		return $this->belongsTo(Employee::class);
	}
}

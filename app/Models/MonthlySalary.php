<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MonthlySalary
 *
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $branch_name
 * @property string|null $branch_code
 * @property Carbon|null $period_date
 * @property int|null $employee_id
 * @property string|null $employee_name
 * @property string|null $employee_code
 * @property string|null $employee_department
 * @property int|null $partner_id
 * @property string|null $partner_name
 * @property string|null $partner_code
 * @property string|null $partner_number
 * @property string|null $partner_alias
 * @property float|null $basic_salary_amount
 * @property float|null $commission_amount
 * @property float|null $penalty_amount
 * @property float|null $other_amount
 * @property float|null $total_salary_amount
 * @property string|null $udf_01
 * @property string|null $udf_02
 * @property string|null $udf_03
 * @property Carbon|null $updated_at
 * @property Carbon|null $created_at
 *
 * @property Collection|MonthlySalaryCommissionDetail[] $monthly_salary_commission_details
 * @property Collection|MonthlySalaryOtherDetail[] $monthly_salary_other_details
 *
 * @package App\Models
 */
class MonthlySalary extends Model
{
	protected $table = 'monthly_salaries';
    protected $connection = 'hrd';

	protected $casts = [
		'branch_id' => 'int',
		'employee_id' => 'int',
		'partner_id' => 'int',
		'basic_salary_amount' => 'float',
		'commission_amount' => 'float',
		'penalty_amount' => 'float',
		'other_amount' => 'float',
		'total_salary_amount' => 'float'
	];

	protected $dates = [
		'period_date'
	];

	protected $fillable = [
		'branch_id',
		'branch_name',
		'branch_code',
		'period_date',
		'employee_id',
		'employee_name',
		'employee_code',
		'employee_department',
		'partner_id',
		'partner_name',
		'partner_code',
		'partner_number',
        'partner_alias',
		'basic_salary_amount',
		'commission_amount',
		'penalty_amount',
		'other_amount',
		'total_salary_amount',
		'udf_01',
		'udf_02',
		'udf_03'
	];

	public function monthly_salary_commission_details()
	{
		return $this->hasMany(MonthlySalaryCommissionDetail::class, 'header_id');
	}

	public function monthly_salary_other_details()
	{
		return $this->hasMany(MonthlySalaryOtherDetail::class, 'header_id');
	}
}

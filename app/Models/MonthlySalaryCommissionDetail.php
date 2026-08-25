<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MonthlySalaryCommissionDetail
 *
 * @property int $id
 * @property int|null $header_id
 * @property string|null $commission_type
 * @property string|null $commission_display
 * @property float|null $commission_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property MonthlySalary|null $monthly_salary
 *
 * @package App\Models
 */
class MonthlySalaryCommissionDetail extends Model
{
	protected $table = 'monthly_salary_commission_details';
    protected $connection = 'hrd';

	protected $casts = [
		'header_id' => 'int',
		'commission_amount' => 'float'
	];

	protected $fillable = [
		'header_id',
		'commission_type',
        'commission_display',
		'commission_amount'
	];

	public function monthly_salary()
	{
		return $this->belongsTo(MonthlySalary::class, 'header_id');
	}
}

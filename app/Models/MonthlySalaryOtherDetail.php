<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MonthlySalaryOtherDetail
 * 
 * @property int $id
 * @property int|null $header_id
 * @property string|null $type
 * @property string|null $reference
 * @property string|null $description
 * @property float|null $add_amount
 * @property float|null $sub_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property MonthlySalary|null $monthly_salary
 *
 * @package App\Models
 */
class MonthlySalaryOtherDetail extends Model
{
	protected $table = 'monthly_salary_other_details';

	protected $casts = [
		'header_id' => 'int',
		'add_amount' => 'float',
		'sub_amount' => 'float'
	];

	protected $fillable = [
		'header_id',
		'type',
		'reference',
		'description',
		'add_amount',
		'sub_amount'
	];

	public function monthly_salary()
	{
		return $this->belongsTo(MonthlySalary::class, 'header_id');
	}
}

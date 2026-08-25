<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommissionItemMonthlyEmployee
 * 
 * @property int $id
 * @property int|null $header_id
 * @property int|null $employee_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Employee|null $employee
 * @property CommissionItemMonthlyHeader|null $commission_item_monthly_header
 *
 * @package App\Models
 */
class CommissionItemMonthlyEmployee extends Model
{
	protected $table = 'commission_item_monthly_employees';

	protected $casts = [
		'header_id' => 'int',
		'employee_id' => 'int'
	];

	protected $fillable = [
		'header_id',
		'employee_id'
	];

	public function employee()
	{
		return $this->belongsTo(Employee::class);
	}

	public function commission_item_monthly_header()
	{
		return $this->belongsTo(CommissionItemMonthlyHeader::class, 'header_id');
	}
}

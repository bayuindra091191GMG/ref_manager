<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BankCashoutHeader
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $code
 * @property int|null $employee_id
 * @property string|null $employee_name
 * @property int|null $department_id
 * @property string|null $department_name
 * @property int|null $coa_bank_id
 * @property string|null $coa_bank_code
 * @property string|null $coa_bank_name
 * @property Carbon|null $payment_date
 * @property int|null $currency_id
 * @property int|null $status_id
 * @property string|null $description
 * @property float|null $total_price
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * @property int|null $delete_status
 * @property string|null $delete_at
 * @property int|null $delete_by
 * 
 * @property Branch|null $branch
 * @property Coa|null $coa
 * @property Currency|null $currency
 * @property Department|null $department
 * @property Employee|null $employee
 * @property Status|null $status
 * @property Collection|BankCashoutDetail[] $bank_cashout_details
 *
 * @package App\Models
 */
class BankCashoutHeader extends Model
{
	protected $table = 'bank_cashout_headers';

	protected $casts = [
		'branch_id' => 'int',
		'employee_id' => 'int',
		'department_id' => 'int',
		'coa_bank_id' => 'int',
		'currency_id' => 'int',
		'status_id' => 'int',
		'total_price' => 'float',
		'created_by' => 'int',
		'updated_by' => 'int',
		'delete_status' => 'int',
		'delete_by' => 'int'
	];

	protected $dates = [
		'payment_date'
	];

	protected $fillable = [
		'branch_id',
		'code',
		'employee_id',
		'employee_name',
		'department_id',
		'department_name',
		'coa_bank_id',
		'coa_bank_code',
		'coa_bank_name',
		'payment_date',
		'currency_id',
		'status_id',
		'description',
		'total_price',
		'created_by',
		'updated_by',
		'delete_status',
		'delete_at',
		'delete_by'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function coa()
	{
		return $this->belongsTo(Coa::class, 'coa_bank_id');
	}

	public function currency()
	{
		return $this->belongsTo(Currency::class);
	}

	public function department()
	{
		return $this->belongsTo(Department::class);
	}

	public function employee()
	{
		return $this->belongsTo(Employee::class);
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function bank_cashout_details()
	{
		return $this->hasMany(BankCashoutDetail::class, 'header_id');
	}
}

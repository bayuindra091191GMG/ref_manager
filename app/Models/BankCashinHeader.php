<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BankCashinHeader
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $code
 * @property int|null $department_id
 * @property string|null $department_name
 * @property Carbon|null $receive_date
 * @property int|null $employee_id
 * @property string|null $employee_name
 * @property int|null $coa_bank_id
 * @property string|null $coa_bank_code
 * @property string|null $coa_bank_name
 * @property int|null $currency_id
 * @property int|null $status_id
 * @property string|null $description
 * @property float|null $total_price
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property Branch|null $branch
 * @property Coa|null $coa
 * @property Currency|null $currency
 * @property Department|null $department
 * @property Employee|null $employee
 * @property Status|null $status
 * @property Collection|BankCashinDetail[] $bank_cashin_details
 *
 * @package App\Models
 */
class BankCashinHeader extends Model
{
	protected $table = 'bank_cashin_headers';

	protected $casts = [
		'branch_id' => 'int',
		'department_id' => 'int',
		'employee_id' => 'int',
		'coa_bank_id' => 'int',
		'currency_id' => 'int',
		'status_id' => 'int',
		'total_price' => 'float',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'receive_date'
	];

	protected $fillable = [
		'branch_id',
		'code',
		'department_id',
		'department_name',
		'receive_date',
		'employee_id',
		'employee_name',
		'coa_bank_id',
		'coa_bank_code',
		'coa_bank_name',
		'currency_id',
		'status_id',
		'description',
		'total_price',
		'created_by',
		'updated_by'
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

	public function bank_cashin_details()
	{
		return $this->hasMany(BankCashinDetail::class, 'header_id');
	}
}

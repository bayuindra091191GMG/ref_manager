<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Employee
 *
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $admin_user_id
 * @property int|null $department_id
 * @property int|null $position_id
 * @property string|null $code
 * @property int|null $number
 * @property string|null $name
 * @property string|null $phone
 * @property string|null $npwp
 * @property string|null $id_card_no
 * @property string|null $address
 * @property string|null $photo_path
 * @property string|null $role
 * @property Carbon|null $join_date
 * @property int|null $attend_machine_id
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * @property string|null $udf_01
 * @property bool|null $is_external
 * @property float|null $basic_salary_amount
 *
 * @property AdminUser|null $admin_user
 * @property Branch|null $branch
 * @property EmployeePosition|null $employee_position
 * @property Status|null $status
 * @property Collection|BankCashinHeader[] $bank_cashin_headers
 * @property Collection|BankCashoutHeader[] $bank_cashout_headers
 * @property Collection|Item[] $items
 * @property Collection|TransactionHeader[] $transaction_headers
 *
 * @package App\Models
 */
class Employee extends Model
{
	protected $table = 'employees';

	protected $casts = [
		'branch_id' => 'int',
		'admin_user_id' => 'int',
		'department_id' => 'int',
		'position_id' => 'int',
		'number' => 'int',
		'attend_machine_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int',
		'is_external' => 'bool',
		'basic_salary_amount' => 'float'
	];

	protected $dates = [
		'join_date'
	];

	protected $fillable = [
		'branch_id',
		'admin_user_id',
		'department_id',
		'position_id',
		'code',
		'number',
		'name',
		'phone',
		'npwp',
		'id_card_no',
		'address',
		'photo_path',
		'role',
		'join_date',
		'attend_machine_id',
		'status_id',
		'created_by',
		'updated_by',
		'udf_01',
		'is_external',
		'basic_salary_amount'
	];

	public function admin_user()
	{
		return $this->belongsTo(AdminUser::class);
	}

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function employee_position()
	{
		return $this->belongsTo(EmployeePosition::class, 'position_id');
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function bank_cashin_headers()
	{
		return $this->hasMany(BankCashinHeader::class);
	}

	public function bank_cashout_headers()
	{
		return $this->hasMany(BankCashoutHeader::class);
	}

	public function items()
	{
		return $this->belongsToMany(Item::class, 'commission_employee_items')
					->withPivot('id', 'branch_id', 'template_id', 'item_unit_id', 'commission_percentage', 'commission_flat', 'udf_01', 'udf_02', 'udf_03', 'status_id', 'created_by', 'updated_by')
					->withTimestamps();
	}

	public function transaction_headers()
	{
		return $this->hasMany(TransactionHeader::class);
	}

    public function contract()
    {
        return $this->hasOne(EmployeeContract::class);
    }
}

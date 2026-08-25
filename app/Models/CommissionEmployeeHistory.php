<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommissionEmployeeHistory
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $transaction_id
 * @property string|null $transaction_code
 * @property Carbon|null $transaction_date
 * @property int|null $employee_id
 * @property string|null $employee_code
 * @property string|null $employee_name
 * @property string|null $employee_phone
 * @property int|null $item_id
 * @property int|null $item_qty
 * @property string|null $item_code
 * @property string|null $item_name
 * @property float|null $commission_percentage
 * @property float|null $commission_add
 * @property float|null $commission_sub
 * @property float|null $commission_result
 * @property string|null $udf_01
 * @property string|null $udf_02
 * @property string|null $udf_03
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property int|null $updated_by
 * @property Carbon|null $updated_at
 * 
 * @property Branch|null $branch
 *
 * @package App\Models
 */
class CommissionEmployeeHistory extends Model
{
	protected $table = 'commission_employee_histories';

	protected $casts = [
		'branch_id' => 'int',
		'transaction_id' => 'int',
		'employee_id' => 'int',
		'item_id' => 'int',
		'item_qty' => 'int',
		'commission_percentage' => 'float',
		'commission_add' => 'float',
		'commission_sub' => 'float',
		'commission_result' => 'float',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'transaction_date'
	];

	protected $fillable = [
		'branch_id',
		'transaction_id',
		'transaction_code',
		'transaction_date',
		'employee_id',
		'employee_code',
		'employee_name',
		'employee_phone',
		'item_id',
		'item_qty',
		'item_code',
		'item_name',
		'commission_percentage',
		'commission_add',
		'commission_sub',
		'commission_result',
		'udf_01',
		'udf_02',
		'udf_03',
		'created_by',
		'updated_by'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}
}

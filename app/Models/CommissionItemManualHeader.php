<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommissionItemManualHeader
 *
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $branch_code
 * @property string|null $branch_name
 * @property int|null $employee_id
 * @property string|null $employee_name
 * @property string|null $employee_code
 * @property string|null $employee_department
 * @property int|null $partner_id
 * @property string|null $partner_name
 * @property string|null $partner_code
 * @property string|null $partner_number
 * @property string|null $partner_alias
 * @property Carbon|null $date
 * @property float|null $total_commission_amount
 * @property int|null $total_qty
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|CommissionItemManualDetail[] $commission_item_manual_details
 *
 * @package App\Models
 */
class CommissionItemManualHeader extends Model
{
	protected $table = 'commission_item_manual_headers';
    protected $connection = 'hrd';

	protected $casts = [
		'branch_id' => 'int',
		'employee_id' => 'int',
		'partner_id' => 'int',
		'total_commission_amount' => 'float',
		'total_qty' => 'int',
		'status_id' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'branch_id',
		'branch_code',
		'branch_name',
		'employee_id',
		'employee_name',
		'employee_code',
		'employee_department',
		'partner_id',
		'partner_name',
		'partner_code',
		'partner_number',
		'partner_alias',
		'date',
		'total_commission_amount',
		'total_qty',
		'status_id'
	];

	public function commission_item_manual_details()
	{
		return $this->hasMany(CommissionItemManualDetail::class, 'header_id');
	}
}

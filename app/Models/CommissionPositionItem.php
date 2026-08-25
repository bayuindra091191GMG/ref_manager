<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommissionPositionItem
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $template_id
 * @property int|null $position_id
 * @property int|null $item_id
 * @property int|null $item_unit_id
 * @property float|null $commission_percentage
 * @property float|null $commission_flat
 * @property string|null $udf_01
 * @property string|null $udf_02
 * @property string|null $udf_03
 * @property int|null $status_id
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property int|null $updated_by
 * @property Carbon|null $updated_at
 * 
 * @property Branch|null $branch
 * @property Item|null $item
 * @property ItemUnit|null $item_unit
 * @property EmployeePosition|null $employee_position
 * @property CommissionTemplate|null $commission_template
 *
 * @package App\Models
 */
class CommissionPositionItem extends Model
{
	protected $table = 'commission_position_items';

	protected $casts = [
		'branch_id' => 'int',
		'template_id' => 'int',
		'position_id' => 'int',
		'item_id' => 'int',
		'item_unit_id' => 'int',
		'commission_percentage' => 'float',
		'commission_flat' => 'float',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'template_id',
		'position_id',
		'item_id',
		'item_unit_id',
		'commission_percentage',
		'commission_flat',
		'udf_01',
		'udf_02',
		'udf_03',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function item()
	{
		return $this->belongsTo(Item::class);
	}

	public function item_unit()
	{
		return $this->belongsTo(ItemUnit::class);
	}

	public function employee_position()
	{
		return $this->belongsTo(EmployeePosition::class, 'position_id');
	}

	public function commission_template()
	{
		return $this->belongsTo(CommissionTemplate::class, 'template_id');
	}
}

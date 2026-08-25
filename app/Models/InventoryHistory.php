<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class InventoryHistory
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $branch_name
 * @property int|null $warehouse_id
 * @property string|null $warehouse_name
 * @property int|null $item_id
 * @property string|null $item_code
 * @property string|null $item_name
 * @property float|null $qty_sub
 * @property float|null $qty_add
 * @property float|null $qty_result
 * @property float|null $qty_total
 * @property string|null $reference
 * @property string|null $description
 * @property string|null $udf_1
 * @property string|null $udf_2
 * @property string|null $udf_3
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property Branch|null $branch
 * @property Item|null $item
 * @property Warehouse|null $warehouse
 *
 * @package App\Models
 */
class InventoryHistory extends Model
{
	protected $table = 'inventory_histories';

	protected $casts = [
		'branch_id' => 'int',
		'warehouse_id' => 'int',
		'item_id' => 'int',
		'qty_sub' => 'float',
		'qty_add' => 'float',
		'qty_result' => 'float',
		'qty_total' => 'float',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'branch_name',
		'warehouse_id',
		'warehouse_name',
		'item_id',
		'item_code',
		'item_name',
		'qty_sub',
		'qty_add',
		'qty_result',
		'qty_total',
		'reference',
		'description',
		'udf_1',
		'udf_2',
		'udf_3',
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

	public function warehouse()
	{
		return $this->belongsTo(Warehouse::class);
	}
}

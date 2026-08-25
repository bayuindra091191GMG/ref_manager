<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Inventory
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $item_id
 * @property int|null $warehouse_id
 * @property float|null $qty
 * @property string|null $batch_number
 * @property float|null $batch_cost_price
 * @property Carbon|null $batch_date
 * @property Carbon|null $batch_expired_date
 * @property Carbon|null $batch_shelf_date
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
class Inventory extends Model
{
	protected $table = 'inventories';

	protected $casts = [
		'branch_id' => 'int',
		'item_id' => 'int',
		'warehouse_id' => 'int',
		'qty' => 'float',
		'batch_cost_price' => 'float',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'batch_date',
		'batch_expired_date',
		'batch_shelf_date'
	];

	protected $fillable = [
		'branch_id',
		'item_id',
		'warehouse_id',
		'qty',
		'batch_number',
		'batch_cost_price',
		'batch_date',
		'batch_expired_date',
		'batch_shelf_date',
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

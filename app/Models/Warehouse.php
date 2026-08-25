<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Warehouse
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $description
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property Status|null $status
 * @property Branch|null $branch
 * @property Collection|Inventory[] $inventories
 * @property Collection|InventoryHistory[] $inventory_histories
 * @property Collection|ItemTransferHeader[] $item_transfer_headers
 *
 * @package App\Models
 */
class Warehouse extends Model
{
	protected $table = 'warehouses';

	protected $casts = [
		'branch_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'code',
		'name',
		'description',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function inventories()
	{
		return $this->hasMany(Inventory::class);
	}

	public function inventory_histories()
	{
		return $this->hasMany(InventoryHistory::class);
	}

	public function item_transfer_headers()
	{
		return $this->hasMany(ItemTransferHeader::class, 'warehouse_origin_id');
	}
}

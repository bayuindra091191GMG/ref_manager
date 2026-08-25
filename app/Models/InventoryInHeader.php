<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class InventoryInHeader
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $code
 * @property Carbon|null $date
 * @property int|null $warehouse_id
 * @property string|null $warehouse_name
 * @property string|null $description
 * @property float|null $total_qty
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property Branch|null $branch
 * @property Status|null $status
 * @property Warehouse|null $warehouse
 * @property Collection|InventoryInDetail[] $inventory_in_details
 *
 * @package App\Models
 */
class InventoryInHeader extends Model
{
	protected $table = 'inventory_in_headers';

	protected $casts = [
		'branch_id' => 'int',
		'warehouse_id' => 'int',
		'total_qty' => 'float',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'branch_id',
		'code',
		'date',
		'warehouse_id',
		'warehouse_name',
		'description',
		'total_qty',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function warehouse()
	{
		return $this->belongsTo(Warehouse::class);
	}

	public function inventory_in_details()
	{
		return $this->hasMany(InventoryInDetail::class, 'header_id');
	}
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ItemTransferHeader
 *
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $code
 * @property Carbon|null $transfer_date
 * @property int|null $warehouse_origin_id
 * @property string|null $warehouse_origin_name
 * @property int|null $warehouse_destination_id
 * @property string|null $warehouse_destination_name
 * @property Carbon|null $period_time
 * @property string|null $description
 * @property float|null $total_qty
 * @property int|null $status_id
 * @property string|null $reject_reason
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 *
 * @property Branch|null $branch
 * @property Status|null $status
 * @property Warehouse|null $warehouse
 * @property Collection|ItemTransferDetail[] $item_transfer_details
 *
 * @package App\Models
 */
class ItemTransferHeader extends Model
{
	protected $table = 'item_transfer_headers';

	protected $casts = [
		'branch_id' => 'int',
		'warehouse_origin_id' => 'int',
		'warehouse_destination_id' => 'int',
		'total_qty' => 'float',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'transfer_date',
		'period_time'
	];

	protected $fillable = [
		'branch_id',
		'code',
		'transfer_date',
		'warehouse_origin_id',
		'warehouse_origin_name',
		'warehouse_destination_id',
		'warehouse_destination_name',
		'period_time',
		'description',
		'total_qty',
		'status_id',
		'reject_reason',
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
		return $this->belongsTo(Warehouse::class, 'warehouse_origin_id');
	}

	public function item_transfer_details()
	{
		return $this->hasMany(ItemTransferDetail::class, 'header_id');
	}
}

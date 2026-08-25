<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class InternalRequestHeader
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $is_approve
 * @property string|null $code
 * @property int|null $supplier_id
 * @property int|null $warehouse_id
 * @property Carbon|null $required_date
 * @property string|null $description
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * @property int|null $is_pr
 * 
 * @property Branch|null $branch
 * @property Status|null $status
 * @property Supplier|null $supplier
 * @property Warehouse|null $warehouse
 * @property Collection|InternalRequestDetail[] $internal_request_details
 *
 * @package App\Models
 */
class InternalRequestHeader extends Model
{
	protected $table = 'internal_request_headers';

	protected $casts = [
		'branch_id' => 'int',
		'is_approve' => 'int',
		'supplier_id' => 'int',
		'warehouse_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int',
		'is_pr' => 'int'
	];

	protected $dates = [
		'required_date'
	];

	protected $fillable = [
		'branch_id',
		'is_approve',
		'code',
		'supplier_id',
		'warehouse_id',
		'required_date',
		'description',
		'status_id',
		'created_by',
		'updated_by',
		'is_pr'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function supplier()
	{
		return $this->belongsTo(Supplier::class);
	}

	public function warehouse()
	{
		return $this->belongsTo(Warehouse::class);
	}

	public function internal_request_details()
	{
		return $this->hasMany(InternalRequestDetail::class, 'header_id');
	}
}

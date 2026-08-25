<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StockInHeader
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $warehouse_id
 * @property string|null $warehouse_name
 * @property string|null $code
 * @property string|null $requested_by
 * @property string|null $purpose
 * @property string|null $description
 * @property string|null $udf_01
 * @property string|null $udf_02
 * @property string|null $udf_03
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property Branch|null $branch
 * @property Warehouse|null $warehouse
 *
 * @package App\Models
 */
class StockInHeader extends Model
{
	protected $table = 'stock_in_headers';

	protected $casts = [
		'branch_id' => 'int',
		'warehouse_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'warehouse_id',
		'warehouse_name',
		'code',
		'requested_by',
		'purpose',
		'description',
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

	public function warehouse()
	{
		return $this->belongsTo(Warehouse::class);
	}
}

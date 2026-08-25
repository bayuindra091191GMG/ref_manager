<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ItemCategory
 *
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $desc
 * @property int|null $coa_stock
 * @property int|null $coa_cost
 * @property int|null $status_id
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property int|null $updated_by
 * @property Carbon|null $updated_at
 *
 * @property Branch|null $branch
 * @property Coa|null $coas_stock
 * @property Coa|null $coas_cost
 * @property Status|null $status
 * @property Collection|Item[] $items
 *
 * @package App\Models
 */
class ItemCategory extends Model
{
	protected $table = 'item_categories';

	protected $casts = [
		'branch_id' => 'int',
		'coa_stock' => 'int',
		'coa_cost' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'code',
		'name',
		'desc',
		'coa_stock',
		'coa_cost',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function coas_stock()
	{
		return $this->belongsTo(Coa::class, 'coa_stock');
	}

	public function coas_cost()
	{
		return $this->belongsTo(Coa::class, 'coa_cost');
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function items()
	{
		return $this->hasMany(Item::class, 'category_id');
	}
}

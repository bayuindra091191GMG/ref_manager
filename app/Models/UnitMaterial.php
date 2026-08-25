<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UnitMaterial
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $name
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property Branch|null $branch
 * @property Status|null $status
 * @property Collection|ItemUnit[] $item_units
 *
 * @package App\Models
 */
class UnitMaterial extends Model
{
	protected $table = 'unit_materials';

	protected $casts = [
		'branch_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'name',
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

	public function item_units()
	{
		return $this->hasMany(ItemUnit::class, 'unit_id');
	}
}

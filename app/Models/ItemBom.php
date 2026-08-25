<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ItemBom
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $item_id
 * @property int|null $qty
 * @property int|null $material_id
 * @property string|null $material_name
 * @property int|null $uom_id
 * 
 * @property Branch|null $branch
 * @property Item|null $item
 * @property UnitMaterial|null $unit_material
 *
 * @package App\Models
 */
class ItemBom extends Model
{
	protected $table = 'item_boms';
	public $timestamps = false;

	protected $casts = [
		'branch_id' => 'int',
		'item_id' => 'int',
		'qty' => 'int',
		'material_id' => 'int',
		'uom_id' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'item_id',
		'qty',
		'material_id',
		'material_name',
		'uom_id'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function item()
	{
		return $this->belongsTo(Item::class, 'material_id');
	}

	public function unit_material()
	{
		return $this->belongsTo(UnitMaterial::class, 'uom_id');
	}
}

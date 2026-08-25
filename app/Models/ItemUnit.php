<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ItemUnit
 * 
 * @property int $id
 * @property int|null $item_id
 * @property int|null $unit_id
 * @property float|null $qty
 * @property float|null $purchase_price
 * @property float|null $selling_price
 * 
 * @property Item|null $item
 * @property UnitMaterial|null $unit_material
 *
 * @package App\Models
 */
class ItemUnit extends Model
{
	protected $table = 'item_units';
	public $timestamps = false;

	protected $casts = [
		'item_id' => 'int',
		'unit_id' => 'int',
		'qty' => 'float',
		'purchase_price' => 'float',
		'selling_price' => 'float'
	];

	protected $fillable = [
		'item_id',
		'unit_id',
		'qty',
		'purchase_price',
		'selling_price'
	];

	public function item()
	{
		return $this->belongsTo(Item::class);
	}

	public function unit_material()
	{
		return $this->belongsTo(UnitMaterial::class, 'unit_id');
	}
}

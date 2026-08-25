<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GoodReceiveDetail
 * 
 * @property int $id
 * @property string|null $header_code
 * @property int|null $header_id
 * @property int|null $item_id
 * @property int|null $unit_id
 * @property float|null $qty
 * @property float|null $purchase_price
 * @property float|null $total
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * @property float|null $max_qty
 * 
 * @property GoodReceiveHeader|null $good_receive_header
 * @property Item|null $item
 * @property UnitMaterial|null $unit_material
 *
 * @package App\Models
 */
class GoodReceiveDetail extends Model
{
	protected $table = 'good_receive_details';

	protected $casts = [
		'header_id' => 'int',
		'item_id' => 'int',
		'unit_id' => 'int',
		'qty' => 'float',
		'purchase_price' => 'float',
		'total' => 'float',
		'created_by' => 'int',
		'updated_by' => 'int',
		'max_qty' => 'float'
	];

	protected $fillable = [
		'header_code',
		'header_id',
		'item_id',
		'unit_id',
		'qty',
		'purchase_price',
		'total',
		'description',
		'created_by',
		'updated_by',
		'max_qty'
	];

	public function good_receive_header()
	{
		return $this->belongsTo(GoodReceiveHeader::class, 'header_id');
	}

	public function item()
	{
		return $this->belongsTo(Item::class);
	}

	public function unit_material()
	{
		return $this->belongsTo(UnitMaterial::class, 'unit_id');
	}
}

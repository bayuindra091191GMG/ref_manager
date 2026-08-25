<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PurchaseOrderDetail
 * 
 * @property int $id
 * @property string|null $header_code
 * @property int|null $header_id
 * @property int|null $item_id
 * @property int|null $unit_id
 * @property float|null $qty_pr
 * @property float|null $qty
 * @property float|null $price
 * @property float|null $total
 * @property int|null $disc1_percent
 * @property float|null $disc1_value
 * @property int|null $disc2_percent
 * @property float|null $disc2_value
 * @property int|null $ppn_percent
 * @property float|null $ppn_value
 * @property float|null $subtotal
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * @property float|null $qty_gr
 * 
 * @property PurchaseOrderHeader|null $purchase_order_header
 * @property Item|null $item
 * @property UnitMaterial|null $unit_material
 *
 * @package App\Models
 */
class PurchaseOrderDetail extends Model
{
	protected $table = 'purchase_order_details';

	protected $casts = [
		'header_id' => 'int',
		'item_id' => 'int',
		'unit_id' => 'int',
		'qty_pr' => 'float',
		'qty' => 'float',
		'price' => 'float',
		'total' => 'float',
		'disc1_percent' => 'int',
		'disc1_value' => 'float',
		'disc2_percent' => 'int',
		'disc2_value' => 'float',
		'ppn_percent' => 'int',
		'ppn_value' => 'float',
		'subtotal' => 'float',
		'created_by' => 'int',
		'updated_by' => 'int',
		'qty_gr' => 'float'
	];

	protected $fillable = [
		'header_code',
		'header_id',
		'item_id',
		'unit_id',
		'qty_pr',
		'qty',
		'price',
		'total',
		'disc1_percent',
		'disc1_value',
		'disc2_percent',
		'disc2_value',
		'ppn_percent',
		'ppn_value',
		'subtotal',
		'description',
		'created_by',
		'updated_by',
		'qty_gr'
	];

	public function purchase_order_header()
	{
		return $this->belongsTo(PurchaseOrderHeader::class, 'header_id');
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

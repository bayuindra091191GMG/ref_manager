<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class InvoiceManualDetail
 * 
 * @property int $id
 * @property int|null $header_id
 * @property string|null $header_code
 * @property int|null $item_id
 * @property float|null $qty
 * @property int|null $unit_id
 * @property float|null $qty_unit
 * @property float|null $price
 * @property int|null $disc_percent
 * @property float|null $disc_value
 * @property int|null $ppn_percent
 * @property float|null $ppn_value
 * @property float|null $total
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property InvoiceManualHeader|null $invoice_manual_header
 * @property Item|null $item
 * @property UnitMaterial|null $unit_material
 *
 * @package App\Models
 */
class InvoiceManualDetail extends Model
{
	protected $table = 'invoice_manual_details';

	protected $casts = [
		'header_id' => 'int',
		'item_id' => 'int',
		'qty' => 'float',
		'unit_id' => 'int',
		'qty_unit' => 'float',
		'price' => 'float',
		'disc_percent' => 'int',
		'disc_value' => 'float',
		'ppn_percent' => 'int',
		'ppn_value' => 'float',
		'total' => 'float',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'header_id',
		'header_code',
		'item_id',
		'qty',
		'unit_id',
		'qty_unit',
		'price',
		'disc_percent',
		'disc_value',
		'ppn_percent',
		'ppn_value',
		'total',
		'description',
		'created_by',
		'updated_by'
	];

	public function invoice_manual_header()
	{
		return $this->belongsTo(InvoiceManualHeader::class, 'header_id');
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

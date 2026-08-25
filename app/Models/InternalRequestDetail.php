<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class InternalRequestDetail
 * 
 * @property int $id
 * @property string|null $header_code
 * @property int|null $header_id
 * @property int|null $item_id
 * @property float|null $qty
 * @property int|null $unit_id
 * @property float|null $qty_unit
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * @property float|null $qty_pr
 * 
 * @property InternalRequestHeader|null $internal_request_header
 * @property Item|null $item
 * @property UnitMaterial|null $unit_material
 *
 * @package App\Models
 */
class InternalRequestDetail extends Model
{
	protected $table = 'internal_request_details';

	protected $casts = [
		'header_id' => 'int',
		'item_id' => 'int',
		'qty' => 'float',
		'unit_id' => 'int',
		'qty_unit' => 'float',
		'created_by' => 'int',
		'updated_by' => 'int',
		'qty_pr' => 'float'
	];

	protected $fillable = [
		'header_code',
		'header_id',
		'item_id',
		'qty',
		'unit_id',
		'qty_unit',
		'description',
		'created_by',
		'updated_by',
		'qty_pr'
	];

	public function internal_request_header()
	{
		return $this->belongsTo(InternalRequestHeader::class, 'header_id');
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

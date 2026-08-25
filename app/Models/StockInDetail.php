<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StockInDetail
 * 
 * @property int $id
 * @property int|null $header_id
 * @property int|null $item_id
 * @property string|null $item_name
 * @property string|null $item_code
 * @property int|null $item_unit_id
 * @property float|null $qty
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property StockInHeader|null $stock_in_header
 * @property Item|null $item
 *
 * @package App\Models
 */
class StockInDetail extends Model
{
	protected $table = 'stock_in_details';

	protected $casts = [
		'header_id' => 'int',
		'item_id' => 'int',
		'item_unit_id' => 'int',
		'qty' => 'float'
	];

	protected $fillable = [
		'header_id',
		'item_id',
		'item_name',
		'item_code',
		'item_unit_id',
		'qty'
	];

	public function stock_in_header()
	{
		return $this->belongsTo(StockInHeader::class, 'header_id');
	}

	public function item()
	{
		return $this->belongsTo(Item::class);
	}
}

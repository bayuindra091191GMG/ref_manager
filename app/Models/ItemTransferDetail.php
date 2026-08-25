<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ItemTransferDetail
 * 
 * @property int $id
 * @property string|null $header_code
 * @property int|null $header_id
 * @property int|null $item_id
 * @property float|null $qty_transfer
 * @property float|null $cost_price
 * @property float|null $sell_price
 * @property float|null $total_cost
 * @property float|null $total_sell
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property ItemTransferHeader|null $item_transfer_header
 * @property Item|null $item
 *
 * @package App\Models
 */
class ItemTransferDetail extends Model
{
	protected $table = 'item_transfer_details';

	protected $casts = [
		'header_id' => 'int',
		'item_id' => 'int',
		'qty_transfer' => 'float',
		'cost_price' => 'float',
		'sell_price' => 'float',
		'total_cost' => 'float',
		'total_sell' => 'float',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'header_code',
		'header_id',
		'item_id',
		'qty_transfer',
		'cost_price',
		'sell_price',
		'total_cost',
		'total_sell',
		'created_by',
		'updated_by'
	];

	public function item_transfer_header()
	{
		return $this->belongsTo(ItemTransferHeader::class, 'header_id');
	}

	public function item()
	{
		return $this->belongsTo(Item::class);
	}
}

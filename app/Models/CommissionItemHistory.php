<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommissionItemHistory
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $branch_code
 * @property string|null $branch_name
 * @property string|null $commission_type
 * @property int|null $transaction_header_id
 * @property string|null $transaction_code
 * @property Carbon|null $transaction_date
 * @property int|null $item_id
 * @property string|null $item_code
 * @property string|null $item_name
 * @property float|null $item_price
 * @property int|null $item_qty
 * @property float|null $item_subtotal
 * @property float|null $commission_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class CommissionItemHistory extends Model
{
	protected $table = 'commission_item_histories';

	protected $casts = [
		'branch_id' => 'int',
		'transaction_header_id' => 'int',
		'item_id' => 'int',
		'item_price' => 'float',
		'item_qty' => 'int',
		'item_subtotal' => 'float',
		'commission_amount' => 'float'
	];

	protected $dates = [
		'transaction_date'
	];

	protected $fillable = [
		'branch_id',
		'branch_code',
		'branch_name',
		'commission_type',
		'transaction_header_id',
		'transaction_code',
		'transaction_date',
		'item_id',
		'item_code',
		'item_name',
		'item_price',
		'item_qty',
		'item_subtotal',
		'commission_amount'
	];
}

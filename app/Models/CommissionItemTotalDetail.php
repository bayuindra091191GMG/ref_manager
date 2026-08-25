<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommissionItemTotalDetail
 *
 * @property int $id
 * @property int|null $header_id
 * @property int|null $branch_id
 * @property string|null $branch_code
 * @property string|null $branch_name
 * @property int|null $item_id
 * @property string|null $item_code
 * @property string|null $item_name
 * @property int|null $total_qty
 * @property float|null $total_sales_amount
 * @property float|null $total_commission_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property CommissionItemTotalHeader|null $commission_item_total_header
 *
 * @package App\Models
 */
class CommissionItemTotalDetail extends Model
{
	protected $table = 'commission_item_total_details';
    protected $connection = 'hrd';

	protected $casts = [
		'header_id' => 'int',
		'branch_id' => 'int',
		'item_id' => 'int',
		'total_qty' => 'int',
		'total_sales_amount' => 'float',
		'total_commission_amount' => 'float'
	];

	protected $fillable = [
		'header_id',
		'branch_id',
		'branch_code',
		'branch_name',
		'item_id',
		'item_code',
		'item_name',
		'total_qty',
		'total_sales_amount',
		'total_commission_amount'
	];

	public function commission_item_total_header()
	{
		return $this->belongsTo(CommissionItemTotalHeader::class, 'header_id');
	}
}

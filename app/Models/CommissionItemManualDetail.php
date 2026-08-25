<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommissionItemManualDetail
 *
 * @property int $id
 * @property int|null $header_id
 * @property int|null $branch_id
 * @property string|null $branch_code
 * @property string|null $branch_name
 * @property int|null $item_id
 * @property string|null $item_code
 * @property string|null $item_name
 * @property int|null $qty
 * @property float|null $sales_amount
 * @property float|null $commission_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property CommissionItemManualHeader|null $commission_item_manual_header
 *
 * @package App\Models
 */
class CommissionItemManualDetail extends Model
{
	protected $table = 'commission_item_manual_details';
    protected $connection = 'hrd';

	protected $casts = [
		'header_id' => 'int',
		'branch_id' => 'int',
		'item_id' => 'int',
		'qty' => 'int',
		'sales_amount' => 'float',
		'commission_amount' => 'float'
	];

	protected $fillable = [
		'header_id',
		'branch_id',
		'branch_code',
		'branch_name',
		'item_id',
		'item_code',
		'item_name',
		'qty',
		'sales_amount',
		'commission_amount'
	];

	public function commission_item_manual_header()
	{
		return $this->belongsTo(CommissionItemManualHeader::class, 'header_id');
	}
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommissionItemMonthlyDetail
 * 
 * @property int $id
 * @property int|null $header_id
 * @property int|null $item_id
 * @property int|null $status_id
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property int|null $updated_by
 * @property Carbon|null $updated_at
 * 
 * @property CommissionItemMonthlyHeader|null $commission_item_monthly_header
 * @property Item|null $item
 *
 * @package App\Models
 */
class CommissionItemMonthlyDetail extends Model
{
	protected $table = 'commission_item_monthly_details';

	protected $casts = [
		'header_id' => 'int',
		'item_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'header_id',
		'item_id',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function commission_item_monthly_header()
	{
		return $this->belongsTo(CommissionItemMonthlyHeader::class, 'header_id');
	}

	public function item()
	{
		return $this->belongsTo(Item::class);
	}
}

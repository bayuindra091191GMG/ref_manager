<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommissionItemTotalHeader
 *
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $branch_code
 * @property string|null $branch_name
 * @property string|null $type
 * @property string|null $display
 * @property Carbon|null $date
 * @property float|null $commission_percentage
 * @property float|null $commission_flat
 * @property float|null $total_sales_amount
 * @property float|null $total_commission_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|CommissionItemTotalDetail[] $commission_item_total_details
 *
 * @package App\Models
 */
class CommissionItemTotalHeader extends Model
{
	protected $table = 'commission_item_total_headers';
    protected $connection = 'hrd';

	protected $casts = [
		'branch_id' => 'int',
		'commission_percentage' => 'float',
		'commission_flat' => 'float',
		'total_sales_amount' => 'float',
		'total_commission_amount' => 'float'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'branch_id',
		'branch_code',
		'branch_name',
		'type',
        'display',
		'date',
		'commission_percentage',
		'commission_flat',
		'total_sales_amount',
		'total_commission_amount'
	];

	public function commission_item_total_details()
	{
		return $this->hasMany(CommissionItemTotalDetail::class, 'header_id');
	}
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommissionItemMonthlyHeader
 *
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $type
 * @property string|null $display
 * @property int|null $status_id
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property int|null $updated_by
 * @property Carbon|null $updated_at
 *
 * @property Branch|null $branch
 * @property Collection|CommissionItemMonthlyDetail[] $commission_item_monthly_details
 *
 * @package App\Models
 */
class CommissionItemMonthlyHeader extends Model
{
	protected $table = 'commission_item_monthly_headers';

	protected $casts = [
		'branch_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'type',
        'display',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function commission_item_monthly_details()
	{
		return $this->hasMany(CommissionItemMonthlyDetail::class, 'header_id');
	}
}

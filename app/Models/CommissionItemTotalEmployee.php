<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommissionItemTotalEmployee
 *
 * @property int $id
 * @property int|null $header_id
 * @property int|null $employee_id
 * @property string|null $employee_name
 * @property string|null $employee_code
 * @property float|null $commission_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property CommissionItemTotalHeader|null $commission_item_total_header
 *
 * @package App\Models
 */
class CommissionItemTotalEmployee extends Model
{
	protected $table = 'commission_item_total_employees';
    protected $connection = 'hrd';

	protected $casts = [
		'header_id' => 'int',
		'employee_id' => 'int',
		'commission_amount' => 'float'
	];

	protected $fillable = [
		'header_id',
		'employee_id',
		'employee_name',
		'employee_code',
		'commission_amount'
	];

	public function commission_item_total_header()
	{
		return $this->belongsTo(CommissionItemTotalHeader::class, 'header_id');
	}
}

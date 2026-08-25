<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ArHeader
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $is_approve
 * @property string|null $code
 * @property Carbon|null $date
 * @property int|null $reference_id
 * @property string|null $reference_code
 * @property int|null $coa_id
 * @property string|null $coa_code
 * @property int|null $partner_id
 * @property string|null $partner_number
 * @property string|null $partner_alias
 * @property int|null $employee_id
 * @property string|null $employee_name
 * @property float|null $subtotal
 * @property float|null $grandtotal
 * @property string|null $description
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property Branch|null $branch
 * @property Coa|null $coa
 * @property Status|null $status
 * @property Collection|ArDetail1[] $ar_detail1s
 * @property Collection|ArDetail2[] $ar_detail2s
 *
 * @package App\Models
 */
class ArHeader extends Model
{
	protected $table = 'ar_headers';

	protected $casts = [
		'branch_id' => 'int',
		'is_approve' => 'int',
		'reference_id' => 'int',
		'coa_id' => 'int',
		'partner_id' => 'int',
		'employee_id' => 'int',
		'subtotal' => 'float',
		'grandtotal' => 'float',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'branch_id',
		'is_approve',
		'code',
		'date',
		'reference_id',
		'reference_code',
		'coa_id',
		'coa_code',
		'partner_id',
		'partner_number',
		'partner_alias',
		'employee_id',
		'employee_name',
		'subtotal',
		'grandtotal',
		'description',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function coa()
	{
		return $this->belongsTo(Coa::class);
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function ar_detail1s()
	{
		return $this->hasMany(ArDetail1::class, 'header_id');
	}

	public function ar_detail2s()
	{
		return $this->hasMany(ArDetail2::class, 'header_id');
	}
}

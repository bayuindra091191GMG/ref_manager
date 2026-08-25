<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommissionPartnerLevelPackage
 *
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $template_id
 * @property string|null $partner_level
 * @property int|null $package_id
 * @property float|null $commission_percentage
 * @property float|null $commission_flat
 * @property float|null $deduction_before
 * @property int|null $divider_after
 * @property float|null $extend_percentage
 * @property float|null $extend_flat
 * @property string|null $udf_01
 * @property string|null $udf_02
 * @property string|null $udf_03
 * @property int|null $status_id
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property int|null $updated_by
 * @property Carbon|null $updated_at
 *
 * @property Branch|null $branch
 * @property Package|null $package
 *
 * @package App\Models
 */
class CommissionPartnerLevelPackage extends Model
{
	protected $table = 'commission_partner_level_packages';

	protected $casts = [
		'branch_id' => 'int',
		'template_id' => 'int',
		'package_id' => 'int',
		'commission_percentage' => 'float',
		'commission_flat' => 'float',
		'deduction_before' => 'float',
		'divider_after' => 'int',
		'extend_percentage' => 'float',
		'extend_flat' => 'float',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'template_id',
		'partner_level',
		'package_id',
		'commission_percentage',
		'commission_flat',
		'deduction_before',
		'divider_after',
		'extend_percentage',
		'extend_flat',
		'udf_01',
		'udf_02',
		'udf_03',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function package()
	{
		return $this->belongsTo(Package::class);
	}
}

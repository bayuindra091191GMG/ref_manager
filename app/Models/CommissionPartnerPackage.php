<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommissionPartnerPackage
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $template_id
 * @property int|null $partner_id
 * @property int|null $package_id
 * @property float|null $commission_percentage
 * @property float|null $commission_flat
 * @property float|null $extend_percentage
 * @property float|null $extend_float
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
 * @property Partner|null $partner
 * @property CommissionTemplate|null $commission_template
 *
 * @package App\Models
 */
class CommissionPartnerPackage extends Model
{
	protected $table = 'commission_partner_packages';

	protected $casts = [
		'branch_id' => 'int',
		'template_id' => 'int',
		'partner_id' => 'int',
		'package_id' => 'int',
		'commission_percentage' => 'float',
		'commission_flat' => 'float',
		'extend_percentage' => 'float',
		'extend_float' => 'float',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'template_id',
		'partner_id',
		'package_id',
		'commission_percentage',
		'commission_flat',
		'extend_percentage',
		'extend_float',
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

	public function partner()
	{
		return $this->belongsTo(Partner::class);
	}

	public function commission_template()
	{
		return $this->belongsTo(CommissionTemplate::class, 'template_id');
	}
}

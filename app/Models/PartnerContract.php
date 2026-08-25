<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PartnerContract
 *
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $partner_id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $marital_status
 * @property string|null $gender
 * @property string|null $education
 * @property string|null $address
 * @property string|null $description
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property int|null $updated_by
 * @property Carbon|null $updated_at
 * @property string|null $ktp_image_path
 * @property string|null $pkwt_image_path
 *
 * @property Branch|null $branch
 * @property Partner|null $partner
 *
 * @package App\Models
 */
class PartnerContract extends Model
{
    use SoftDeletes;

	protected $table = 'partner_contracts';

	protected $casts = [
		'branch_id' => 'int',
		'partner_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

    protected $dates = [
        'start_date',
        'end_date'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function partner()
	{
		return $this->belongsTo(Partner::class);
	}
}

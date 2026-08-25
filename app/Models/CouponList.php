<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CouponList
 *
 * @property int $id
 * @property int|null $coupon_id
 * @property string|null $code
 * @property int|null $is_used
 * @property Carbon|null $exp_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $branch_id
 *
 * @property Branch|null $branch
 * @property Coupon|null $coupon
 * @property AdminUser|null $createdBy
 * @property AdminUser|null $updatedBy
 *
 * @package App\Models
 */
class CouponList extends Model
{
	protected $table = 'coupon_lists';

	protected $casts = [
		'coupon_id' => 'int',
		'is_used' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int',
		'branch_id' => 'int',
        'user_id' => 'int'
	];

	protected $dates = [
		'exp_date'
	];

	protected $fillable = [
		'coupon_id',
        'user_id',
		'code',
		'is_used',
		'exp_date',
		'created_by',
		'updated_by',
		'branch_id'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function coupon()
	{
		return $this->belongsTo(Coupon::class);
	}

    public function createdBy()
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }
}

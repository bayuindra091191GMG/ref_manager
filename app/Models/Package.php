<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Package
 *
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $description
 * @property int|null $duration
 * @property float|null $price
 * @property float|null $second_price
 * @property int|null $partner_count
 * @property int|null $room_type_id
 * @property int|null $extend_duration
 * @property float|null $extend_price
 * @property float|null $additional_person_price
 * @property float|null $additional_partner_price
 * @property bool|null $is_price_cut
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * @property int|null $status_id
 * @property int|null $item_id
 * @property int|null $oil_count_override
 * @property float|null $commission_price
 * @property bool|null $is_home_service
 * @property bool|null $is_happy_hour
 * @property bool|null $is_birthday_promo
 *
 * @property AdminUser|null $admin_user
 * @property Branch|null $branch
 * @property RoomType|null $room_type
 * @property Status|null $status
 * @property Collection|BookingDetail[] $booking_details
 * @property Collection|CommissionPartnerLevelPackage[] $commission_partner_level_packages
 * @property Collection|Partner[] $partners
 * @property Collection|Coupon[] $coupons
 * @property Collection|TransactionDetail[] $transaction_details
 *
 * @package App\Models
 */
class Package extends Model
{
	protected $table = 'packages';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function createdBy()
	{
		return $this->belongsTo(AdminUser::class, 'created_by');
	}

	public function updatedBy()
	{
		return $this->belongsTo(AdminUser::class, 'updated_by');
	}

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function room_type()
	{
		return $this->belongsTo(RoomType::class);
	}

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function booking_details()
	{
		return $this->hasMany(BookingDetail::class);
	}

	public function commission_partner_level_packages()
	{
		return $this->hasMany(CommissionPartnerLevelPackage::class);
	}

	public function partners()
	{
		return $this->belongsToMany(Partner::class, 'commission_partner_packages')
					->withPivot('id', 'branch_id', 'template_id', 'commission_percentage', 'commission_flat', 'extend_percentage', 'extend_float', 'udf_01', 'udf_02', 'udf_03', 'status_id', 'created_by', 'updated_by')
					->withTimestamps();
	}

	public function coupons()
	{
		return $this->belongsToMany(Coupon::class, 'coupon_package_links')
					->withPivot('id')
					->withTimestamps();
	}

	public function transaction_details()
	{
		return $this->hasMany(TransactionDetail::class);
	}
}

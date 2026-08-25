<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TransactionHeader
 *
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $locker_id
 * @property float|null $grand_total
 * @property int|null $total_qty
 * @property float|null $pph
 * @property float|null $ppn
 * @property string|null $voucher_code
 * @property float|null $voucher_value
 * @property float|null $discount
 * @property float|null $amount
 * @property float|null $rounding_amount
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property int|null $updated_by
 * @property Carbon|null $updated_at
 * @property int|null $status_id
 * @property string|null $code
 * @property float|null $return_value
 * @property Carbon|null $start_time
 * @property Carbon|null $end_time
 * @property int|null $employee_id
 * @property float|null $discount_total
 * @property string|null $foc_type
 * @property string|null $foc_number
 * @property int|null $is_happy_hour
 * @property float|null $total_after_commission
 * @property int|null $is_showing
 * @property string|null $customer_name
 * @property string|null $app_coupon_code
 * @property string|null $extra_voucher_code
 * @property bool|null $is_approved
 * @property Carbon|null $approved_at
 * @property int|null $approved_by
 * @property Carbon|null $voided_at
 * @property bool|null $is_app_transaction
 * @property bool|null $is_app_user_phone_input
 * @property int|null $app_user_id
 * @property string|null $app_user_phone
 * @property string|null $app_user_name
 * @property int|null $app_bonus_point
 * @property bool|null $is_paid_with_points
 * @property int|null $app_point_used
 *
 * @property Branch|null $branch
 * @property AdminUser|null $admin_user
 * @property Employee|null $employee
 * @property Locker|null $locker
 * @property Status|null $status
 * @property Collection|AppBirthdayPromoUsage[] $app_birthday_promo_usages
 * @property Collection|BookingHeader[] $booking_headers
 * @property Collection|TransactionDetail[] $transaction_details
 * @property Collection|TransactionPayment[] $transaction_payments
 *
 * @package App\Models
 */
class TransactionHeader extends Model
{
	protected $table = 'transaction_headers';

	protected $casts = [
		'branch_id' => 'int',
		'locker_id' => 'int',
		'grand_total' => 'float',
		'total_qty' => 'int',
		'pph' => 'float',
		'ppn' => 'float',
		'voucher_value' => 'float',
		'discount' => 'float',
		'amount' => 'float',
		'rounding_amount' => 'float',
		'created_by' => 'int',
		'updated_by' => 'int',
		'status_id' => 'int',
		'return_value' => 'float',
		'start_time' => 'datetime',
		'end_time' => 'datetime',
		'employee_id' => 'int',
		'discount_total' => 'float',
		'is_happy_hour' => 'int',
		'total_after_commission' => 'float',
		'is_showing' => 'int',
		'is_approved' => 'bool',
		'approved_at' => 'datetime',
		'approved_by' => 'int',
		'voided_at' => 'datetime',
		'is_app_transaction' => 'bool',
		'is_app_user_phone_input' => 'bool',
		'app_user_id' => 'int',
		'app_bonus_point' => 'int',
        'is_paid_with_points' => 'bool',
		'app_point_used' => 'int',
	];

	protected $guarded = [
		'id',
		'created_at',
		'updated_at',
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

	public function createdBy()
	{
		return $this->belongsTo(AdminUser::class, 'created_by');
	}

    public function updatedBy()
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

	public function employee()
	{
		return $this->belongsTo(Employee::class);
	}

	public function locker()
	{
		return $this->belongsTo(Locker::class);
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function app_birthday_promo_usages()
	{
		return $this->hasMany(AppBirthdayPromoUsage::class, 'transaction_id');
	}

    public function membership()
    {
        return $this->belongsTo(AppMembership::class, 'member_membership_id', 'id');
    }

	public function booking_headers()
	{
		return $this->hasMany(BookingHeader::class, 'transaction_id');
	}

	public function transaction_details()
	{
		return $this->hasMany(TransactionDetail::class, 'header_id');
	}

	public function transaction_payments()
	{
		return $this->hasMany(TransactionPayment::class, 'header_id');
	}
}

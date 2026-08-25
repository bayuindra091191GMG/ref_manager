<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BookingHeader
 *
 * @property int $id
 * @property string|null $type
 * @property int|null $branch_id
 * @property string|null $branch_code
 * @property string|null $branch_name
 * @property int|null $user_id
 * @property string|null $user_phone
 * @property string|null $user_name
 * @property string|null $user_email
 * @property string|null $code
 * @property float|null $pph
 * @property float|null $ppn
 * @property float|null $total_discount
 * @property float|null $grand_total
 * @property string|null $voucher_code
 * @property float|null $voucher_value
 * @property float|null $payment_charge_ppn_percentage
 * @property float|null $payment_charge_ppn_value
 * @property float|null $payment_charge
 * @property float|null $payment_extra_charge
 * @property float|null $payment_extra_percentage
 * @property float|null $total_payment_before
 * @property float|null $total_payment
 * @property string|null $payment_type
 * @property string|null $pg_code
 * @property string|null $card_owner
 * @property string|null $card_number
 * @property string|null $pg_url
 * @property string|null $pg_service
 * @property string|null $pg_acquirer
 * @property bool|null $is_settled
 * @property string|null $pg_request_id
 * @property Carbon|null $pg_settlement_date
 * @property Carbon|null $pg_expired_date
 * @property string|null $pg_status
 * @property Carbon|null $canceled_at
 * @property Carbon|null $payment_at
 * @property Carbon|null $payment_due_at
 * @property Carbon|null $booking_date
 * @property string|null $udf_01
 * @property string|null $udf_02
 * @property string|null $udf_03
 * @property int|null $status_id
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property int|null $updated_by
 * @property Carbon|null $updated_at
 * @property int|null $transaction_id
 * @property int|null $customer_country_id
 * @property string|null $customer_country_name
 * @property int|null $customer_province_id
 * @property string|null $customer_province_name
 * @property int|null $customer_city_id
 * @property string|null $customer_city_name
 * @property int|null $customer_district_id
 * @property string|null $customer_district_name
 * @property string|null $customer_mail
 * @property string|null $customer_tel_1
 * @property string|null $customer_full_address
 * @property string|null $customer_postal_code
 * @property float|null $customer_latitude
 * @property float|null $customer_longitude
 * @property float|null $customer_distance
 * @property string|null $booked_gender
 * @property string|null $booked_massage_style
 * @property string|null $booking_time
 * @property string|null $booking_notes
 * @property int|null $point_used
 * @property int|null $point_bonus
 * @property Carbon|null $home_service_finished_at
 * @property string|null $app_coupon_code
 * @property int|null $membership_id
 * @property string|null $membership_name
 * @property float|null $membership_discount_percentage
 * @property bool|null $is_membership_discount
 * @property bool|null $is_birthday_discount
 * @property float|null $birthday_discount_percentage
 *
 * @property Branch|null $branch
 * @property Status|null $status
 * @property TransactionHeader|null $transaction_header
 * @property User|null $user
 * @property Collection|BookingDetail[] $booking_details
 *
 * @package App\Models
 */
class BookingHeader extends Model
{
	protected $table = 'booking_headers';

	protected $casts = [
		'branch_id' => 'int',
		'user_id' => 'int',
		'pph' => 'float',
		'ppn' => 'float',
		'total_discount' => 'float',
		'grand_total' => 'float',
		'voucher_value' => 'float',
		'payment_charge_ppn_percentage' => 'float',
		'payment_charge_ppn_value' => 'float',
		'payment_charge' => 'float',
		'payment_extra_charge' => 'float',
		'payment_extra_percentage' => 'float',
		'total_payment_before' => 'float',
		'total_payment' => 'float',
		'is_settled' => 'bool',
		'pg_settlement_date' => 'datetime',
		'pg_expired_date' => 'datetime',
		'canceled_at' => 'datetime',
		'payment_at' => 'datetime',
		'payment_due_at' => 'datetime',
		'payment_reminded_at' => 'datetime',
        'payment_approved_at' => 'datetime',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int',
		'transaction_id' => 'int',
		'customer_country_id' => 'int',
		'customer_province_id' => 'int',
		'customer_city_id' => 'int',
		'customer_district_id' => 'int',
		'customer_latitude' => 'float',
		'customer_longitude' => 'float',
		'customer_distance' => 'float',
		'point_used' => 'int',
		'point_bonus' => 'int',
		'home_service_finished_at' => 'datetime',
		'membership_id' => 'int',
		'membership_discount_percentage' => 'float',
		'is_membership_discount' => 'bool',
        'is_birthday_discount' => 'bool',
        'birthday_discount_percentage' => 'float'
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

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function transaction_header()
	{
		return $this->belongsTo(TransactionHeader::class, 'transaction_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class)->withTrashed();
	}

	public function booking_details()
	{
		return $this->hasMany(BookingDetail::class, 'header_id');
	}
}

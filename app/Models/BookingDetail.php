<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BookingDetail
 *
 * @property int $id
 * @property int|null $header_id
 * @property int|null $package_id
 * @property string|null $package_code
 * @property string|null $package_name
 * @property int|null $room_id
 * @property string|null $room_name
 * @property string|null $room_code
 * @property int|null $item_id
 * @property string|null $item_name
 * @property string|null $item_code
 * @property int|null $qty
 * @property float|null $price
 * @property int|null $discount_percentage
 * @property float|null $discount_amount
 * @property float|null $subtotal
 * @property Carbon|null $start_time
 * @property Carbon|null $end_time
 * @property string|null $booking_time
 * @property string|null $massaging_option
 * @property string|null $therapist_gender
 * @property Carbon|null $canceled_at
 * @property string|null $ts_notes
 * @property int|null $status_id
 * @property string|null $udf_01
 * @property string|null $udf_02
 * @property string|null $udf_03
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property BookingHeader|null $booking_header
 * @property Item|null $item
 * @property Package|null $package
 * @property Room|null $room
 * @property Collection|TransactionBookingSlot[] $transaction_booking_slots
 *
 * @package App\Models
 */
class BookingDetail extends Model
{
	protected $table = 'booking_details';

	protected $casts = [
		'header_id' => 'int',
		'package_id' => 'int',
		'room_id' => 'int',
		'item_id' => 'int',
		'qty' => 'int',
		'price' => 'float',
		'discount_percentage' => 'int',
		'discount_amount' => 'float',
		'subtotal' => 'float',
		'status_id' => 'int'
	];

	protected $dates = [
		'start_time',
		'end_time',
		'canceled_at'
	];

	protected $fillable = [
		'header_id',
		'package_id',
		'package_code',
		'package_name',
		'room_id',
		'room_name',
		'room_code',
		'item_id',
		'item_name',
		'item_code',
		'qty',
		'price',
		'discount_percentage',
		'discount_amount',
		'subtotal',
		'start_time',
		'end_time',
        'booking_time',
		'massaging_option',
		'therapist_gender',
		'canceled_at',
        'ts_notes',
		'status_id',
		'udf_01',
		'udf_02',
		'udf_03'
	];

	public function booking_header()
	{
		return $this->belongsTo(BookingHeader::class, 'header_id');
	}

    public function booking_detail_partner()
    {
        return $this->hasOne(BookingDetailPartner::class, 'detail_id');
    }

	public function item()
	{
		return $this->belongsTo(Item::class);
	}

	public function package()
	{
		return $this->belongsTo(Package::class);
	}

	public function room()
	{
		return $this->belongsTo(Room::class);
	}

	public function transaction_booking_slots()
	{
		return $this->hasMany(TransactionBookingSlot::class);
	}

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}

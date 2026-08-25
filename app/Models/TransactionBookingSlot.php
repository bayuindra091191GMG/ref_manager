<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TransactionBookingSlot
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $user_id
 * @property int|null $booking_detail_id
 * @property Carbon|null $date
 * @property string|null $time_start
 * @property int|null $hour_start
 * @property int|null $minute_start
 * @property string|null $time_end
 * @property int|null $hour_end
 * @property int|null $minutes_end
 * @property int|null $status_id
 * @property Carbon|null $canceled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property BookingDetail|null $booking_detail
 * @property Branch|null $branch
 * @property User|null $user
 *
 * @package App\Models
 */
class TransactionBookingSlot extends Model
{
	protected $table = 'transaction_booking_slots';

	protected $casts = [
		'branch_id' => 'int',
		'user_id' => 'int',
		'booking_detail_id' => 'int',
		'hour_start' => 'int',
		'minute_start' => 'int',
		'hour_end' => 'int',
		'minutes_end' => 'int',
		'status_id' => 'int'
	];

	protected $dates = [
		'date',
		'canceled_at'
	];

	protected $fillable = [
		'branch_id',
		'user_id',
		'booking_detail_id',
		'date',
		'time_start',
		'hour_start',
		'minute_start',
		'time_end',
		'hour_end',
		'minutes_end',
		'status_id',
		'canceled_at'
	];

	public function booking_detail()
	{
		return $this->belongsTo(BookingDetail::class);
	}

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class)->withTrashed();
	}
}

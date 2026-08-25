<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AppBirthdayPromoUsage
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $user_phone
 * @property string|null $user_name
 * @property int|null $booking_id
 * @property int|null $transaction_id
 * @property string|null $membership
 * @property string|null $year
 * @property float|null $birthday_discount_percentage
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property BookingHeader|null $booking_header
 * @property TransactionHeader|null $transaction_header
 *
 * @package App\Models
 */
class AppBirthdayPromoUsage extends Model
{
	protected $table = 'app_birthday_promo_usages';

	protected $casts = [
		'user_id' => 'int',
		'booking_id' => 'int',
		'transaction_id' => 'int',
		'birthday_discount_percentage' => 'float',
		'status_id' => 'int',
	];

	protected $fillable = [
		'user_id',
		'user_phone',
		'user_name',
		'booking_id',
		'transaction_id',
		'membership',
		'year',
		'birthday_discount_percentage',
        'status_id'
	];

	public function booking_header()
	{
		return $this->belongsTo(BookingHeader::class, 'booking_id');
	}

	public function transaction_header()
	{
		return $this->belongsTo(TransactionHeader::class, 'transaction_id');
	}
}

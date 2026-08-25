<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FoodBookingDetail
 * 
 * @property int $id
 * @property int|null $locker_id
 * @property int|null $transaction_details_id
 * @property string|null $number
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class FoodBookingDetail extends Model
{
	protected $table = 'food_booking_details';

	protected $casts = [
		'locker_id' => 'int',
		'transaction_details_id' => 'int'
	];

	protected $fillable = [
		'locker_id',
		'transaction_details_id',
		'number'
	];
}

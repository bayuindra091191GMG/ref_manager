<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TempBooking
 * 
 * @property int $id
 * @property int|null $partner_id
 * @property int|null $room_id
 * @property int|null $status_id
 * @property int|null $branch_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @package App\Models
 */
class TempBooking extends Model
{
	protected $table = 'temp_bookings';

	protected $casts = [
		'partner_id' => 'int',
		'room_id' => 'int',
		'status_id' => 'int',
		'branch_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'partner_id',
		'room_id',
		'status_id',
		'branch_id',
		'created_by',
		'updated_by'
	];
}

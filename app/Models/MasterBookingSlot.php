<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MasterBookingSlot
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $time_start
 * @property int|null $hour_start
 * @property int|null $minutes_start
 * @property string|null $time_end
 * @property int|null $hour_end
 * @property int|null $minutes_end
 * @property int|null $status_id
 * 
 * @property Branch|null $branch
 *
 * @package App\Models
 */
class MasterBookingSlot extends Model
{
	protected $table = 'master_booking_slots';
	public $timestamps = false;

	protected $casts = [
		'branch_id' => 'int',
		'hour_start' => 'int',
		'minutes_start' => 'int',
		'hour_end' => 'int',
		'minutes_end' => 'int',
		'status_id' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'time_start',
		'hour_start',
		'minutes_start',
		'time_end',
		'hour_end',
		'minutes_end',
		'status_id'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}
}

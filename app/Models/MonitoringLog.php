<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MonitoringLog
 * 
 * @property int $id
 * @property int|null $admin_user_id
 * @property int|null $partner_id
 * @property int|null $room_id
 * @property Carbon|null $created_at
 *
 * @package App\Models
 */
class MonitoringLog extends Model
{
	protected $table = 'monitoring_logs';
	public $timestamps = false;

	protected $casts = [
		'admin_user_id' => 'int',
		'partner_id' => 'int',
		'room_id' => 'int'
	];

	protected $fillable = [
		'admin_user_id',
		'partner_id',
		'room_id'
	];
}

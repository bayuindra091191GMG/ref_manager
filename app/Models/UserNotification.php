<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserNotification
 * 
 * @property int $id
 * @property int|null $user_id
 * @property string|null $message_id
 * @property string|null $notif_type
 * @property string|null $model
 * @property string|null $title
 * @property string|null $body
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class UserNotification extends Model
{
	protected $table = 'user_notifications';

	protected $casts = [
		'user_id' => 'int',
		'status_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'message_id',
		'notif_type',
		'model',
		'title',
		'body',
		'status_id'
	];
}

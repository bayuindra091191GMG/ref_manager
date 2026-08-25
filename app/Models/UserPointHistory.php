<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserPointHistory
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $user_name
 * @property string|null $user_email
 * @property string|null $user_phone
 * @property string|null $type
 * @property int|null $amount_add
 * @property int|null $amount_sub
 * @property int|null $amount_result
 * @property string|null $reference_1
 * @property string|null $reference_2
 * @property string|null $reference_3
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property User|null $user
 *
 * @package App\Models
 */
class UserPointHistory extends Model
{
	protected $table = 'user_point_histories';

	protected $casts = [
		'user_id' => 'int',
		'amount_add' => 'int',
		'amount_sub' => 'int',
		'amount_result' => 'int'
	];

	protected $fillable = [
		'user_id',
		'user_name',
		'user_email',
		'user_phone',
		'type',
		'amount_add',
		'amount_sub',
		'amount_result',
		'reference_1',
		'reference_2',
		'reference_3'
	];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

	public function user()
	{
		return $this->belongsTo(User::class)->withTrashed();
	}
}

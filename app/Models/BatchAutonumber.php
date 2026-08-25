<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BatchAutonumber
 * 
 * @property int $id
 * @property string|null $number
 * @property int|null $next_no
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class BatchAutonumber extends Model
{
	protected $table = 'batch_autonumbers';

	protected $casts = [
		'next_no' => 'int'
	];

	protected $fillable = [
		'number',
		'next_no'
	];
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Locker
 *
 * @property int $id
 * @property string|null $number
 * @property string|null $code
 * @property string|null $name
 * @property string|null $description
 * @property int|null $status_id
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property int|null $updated_by
 * @property Carbon|null $updated_at
 * @property int|null $branch_id
 *
 * @property Branch|null $branch
 * @property Status|null $status
 *
 * @package App\Models
 */
class Locker extends Model
{
	protected $table = 'lockers';

	protected $casts = [
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int',
		'branch_id' => 'int'
	];

	protected $fillable = [
		'code',
		'name',
		'number',
		'description',
		'status_id',
		'created_by',
		'updated_by',
		'branch_id'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Commision
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $name
 * @property float|null $percentage_amount
 * @property float|null $amount
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property int|null $updated_by
 * @property Carbon|null $updated_at
 * 
 * @property Branch|null $branch
 *
 * @package App\Models
 */
class Commision extends Model
{
	protected $table = 'commisions';

	protected $casts = [
		'branch_id' => 'int',
		'percentage_amount' => 'float',
		'amount' => 'float',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'name',
		'percentage_amount',
		'amount',
		'created_by',
		'updated_by'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Membership
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $code
 * @property float|null $price
 * @property int|null $is_infinite
 * @property int|null $duration
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property Branch|null $branch
 * @property Collection|Member[] $members
 * @property Collection|TransactionDetail[] $transaction_details
 *
 * @package App\Models
 */
class Membership extends Model
{
	protected $table = 'memberships';

	protected $casts = [
		'branch_id' => 'int',
		'price' => 'float',
		'is_infinite' => 'int',
		'duration' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'code',
		'price',
		'is_infinite',
		'duration',
		'created_by',
		'updated_by'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function members()
	{
		return $this->hasMany(Member::class);
	}

	public function transaction_details()
	{
		return $this->hasMany(TransactionDetail::class);
	}
}

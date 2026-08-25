<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MassagingOption
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $key
 * @property string|null $name
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property int|null $updated_by
 * @property Carbon|null $updated_at
 * 
 * @property Branch|null $branch
 *
 * @package App\Models
 */
class MassagingOption extends Model
{
	protected $table = 'massaging_options';

	protected $casts = [
		'branch_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'key',
		'name',
		'created_by',
		'updated_by'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}
}

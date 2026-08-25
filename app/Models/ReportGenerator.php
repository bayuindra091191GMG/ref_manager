<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ReportGenerator
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $type
 * @property Carbon|null $date
 * @property string|null $file_name
 * @property string|null $filesystem_name
 * @property string|null $emailed_to
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Branch|null $branch
 *
 * @package App\Models
 */
class ReportGenerator extends Model
{
	protected $table = 'report_generators';

	protected $casts = [
		'branch_id' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'branch_id',
		'type',
		'date',
		'file_name',
		'filesystem_name',
		'emailed_to'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RevisionLog
 * 
 * @property string|null $code
 * @property string|null $caused_by
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class RevisionLog extends Model
{
	protected $table = 'revision_logs';
	public $incrementing = false;

	protected $fillable = [
		'code',
		'caused_by',
		'description'
	];
}

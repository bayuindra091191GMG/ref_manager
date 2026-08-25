<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class JournalType
 * 
 * @property int $id
 * @property string|null $code
 * @property string|null $name
 * @property int|null $status_id
 *
 * @package App\Models
 */
class JournalType extends Model
{
	protected $table = 'journal_types';
	public $timestamps = false;

	protected $casts = [
		'status_id' => 'int'
	];

	protected $fillable = [
		'code',
		'name',
		'status_id'
	];
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Configuration
 * 
 * @property int $id
 * @property string|null $key
 * @property string|null $value
 * @property float|null $value_int
 * @property int|null $branch_id
 *
 * @package App\Models
 */
class Configuration extends Model
{
	protected $table = 'configurations';
	public $timestamps = false;

	protected $casts = [
		'value_int' => 'float',
		'branch_id' => 'int'
	];

	protected $fillable = [
		'key',
		'value',
		'value_int',
		'branch_id'
	];
}

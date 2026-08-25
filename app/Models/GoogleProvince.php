<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GoogleProvince
 * 
 * @property int $id
 * @property string|null $code
 * @property string|null $name
 * 
 * @property Collection|GoogleCity[] $google_cities
 *
 * @package App\Models
 */
class GoogleProvince extends Model
{
	protected $table = 'google_provinces';
	public $timestamps = false;

	protected $fillable = [
		'code',
		'name'
	];

	public function google_cities()
	{
		return $this->hasMany(GoogleCity::class, 'province_id');
	}
}

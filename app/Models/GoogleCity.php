<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GoogleCity
 * 
 * @property int $id
 * @property int|null $province_id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $postal_code
 * 
 * @property GoogleProvince|null $google_province
 * @property Collection|GoogleDistrict[] $google_districts
 *
 * @package App\Models
 */
class GoogleCity extends Model
{
	protected $table = 'google_cities';
	public $timestamps = false;

	protected $casts = [
		'province_id' => 'int'
	];

	protected $fillable = [
		'province_id',
		'code',
		'name',
		'postal_code'
	];

	public function google_province()
	{
		return $this->belongsTo(GoogleProvince::class, 'province_id');
	}

	public function google_districts()
	{
		return $this->hasMany(GoogleDistrict::class, 'city_id');
	}
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GoogleDistrict
 *
 * @property int $id
 * @property int|null $city_id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $postal_code
 *
 * @property GoogleCity|null $google_city
 *
 * @package App\Models
 */
class GoogleDistrict extends Model
{
	protected $table = 'google_districts';
	public $timestamps = false;

	protected $casts = [
		'city_id' => 'int'
	];

	protected $fillable = [
		'city_id',
		'code',
		'name',
		'postal_code'
	];

	public function google_city()
	{
		return $this->belongsTo(GoogleCity::class, 'city_id');
	}
}

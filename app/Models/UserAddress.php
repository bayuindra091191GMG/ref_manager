<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserAddress
 * 
 * @property int $id
 * @property int $user_id
 * @property string|null $name
 * @property string|null $receiver_name
 * @property string|null $receiver_phone
 * @property string|null $postal_code
 * @property int|null $country_id
 * @property string|null $country_name
 * @property int|null $province_id
 * @property string|null $province_name
 * @property int|null $city_id
 * @property string|null $city_name
 * @property int|null $district_id
 * @property string|null $district_name
 * @property string|null $full_address
 * @property string|null $google_street_address
 * @property float|null $latitude
 * @property float|null $longitude
 * @property bool|null $is_primary
 * @property string|null $udf_01
 * @property string|null $udf_02
 * @property string|null $udf_03
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property User $user
 *
 * @package App\Models
 */
class UserAddress extends Model
{
	protected $table = 'user_addresses';

	protected $casts = [
		'user_id' => 'int',
		'country_id' => 'int',
		'province_id' => 'int',
		'city_id' => 'int',
		'district_id' => 'int',
		'latitude' => 'float',
		'longitude' => 'float',
		'is_primary' => 'bool',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'user_id',
		'name',
		'receiver_name',
		'receiver_phone',
		'postal_code',
		'country_id',
		'country_name',
		'province_id',
		'province_name',
		'city_id',
		'city_name',
		'district_id',
		'district_name',
		'full_address',
		'google_street_address',
		'latitude',
		'longitude',
		'is_primary',
		'udf_01',
		'udf_02',
		'udf_03',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function user()
	{
		return $this->belongsTo(User::class)->withTrashed();
	}
}

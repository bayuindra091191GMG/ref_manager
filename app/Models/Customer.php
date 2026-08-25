<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Customer
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $phone
 * @property string|null $npwp
 * @property string|null $address1
 * @property string|null $address2
 * @property string|null $province
 * @property string|null $district
 * @property string|null $city
 * @property string|null $zip_code
 * @property int|null $top
 * @property int|null $coa_id
 * @property string|null $coa_code
 * @property string|null $coa_name
 * @property string|null $deleted_at
 * @property int|null $deleted_by
 * @property int|null $delete_status
 *
 * @package App\Models
 */
class Customer extends Model
{
	use SoftDeletes;
	protected $table = 'customers';
	public $timestamps = false;

	protected $casts = [
		'branch_id' => 'int',
		'top' => 'int',
		'coa_id' => 'int',
		'deleted_by' => 'int',
		'delete_status' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'code',
		'name',
		'phone',
		'npwp',
		'address1',
		'address2',
		'province',
		'district',
		'city',
		'zip_code',
		'top',
		'coa_id',
		'coa_code',
		'coa_name',
		'deleted_by',
		'delete_status'
	];
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PosPaymentType
 *
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $code
 * @property string|null $description
 * @property float|null $charge
 * @property int|null $status_id
 *
 * @package App\Models
 */
class PosPaymentType extends Model
{
	protected $table = 'pos_payment_types';
	public $timestamps = false;

	protected $casts = [
		'branch_id' => 'int',
		'charge' => 'float',
		'status_id' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'code',
		'description',
		'charge',
		'status_id'
	];
}

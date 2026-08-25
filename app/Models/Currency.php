<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Currency
 * 
 * @property int $id
 * @property string|null $code
 * @property string|null $name
 * @property int|null $rate
 * @property string|null $description
 * @property int|null $status_id
 * 
 * @property Collection|BankCashoutHeader[] $bank_cashout_headers
 *
 * @package App\Models
 */
class Currency extends Model
{
	protected $table = 'currencies';
	public $timestamps = false;

	protected $casts = [
		'rate' => 'int',
		'status_id' => 'int'
	];

	protected $fillable = [
		'code',
		'name',
		'rate',
		'description',
		'status_id'
	];

	public function bank_cashout_headers()
	{
		return $this->hasMany(BankCashoutHeader::class);
	}
}

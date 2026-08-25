<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BankCashoutDetail
 * 
 * @property int $id
 * @property int|null $header_id
 * @property string|null $header_code
 * @property int|null $coa_id
 * @property string|null $coa_code
 * @property string|null $coa_name
 * @property string|null $debit_kredit
 * @property string|null $description
 * @property float|null $amount
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property Coa|null $coa
 * @property BankCashoutHeader|null $bank_cashout_header
 *
 * @package App\Models
 */
class BankCashoutDetail extends Model
{
	protected $table = 'bank_cashout_details';

	protected $casts = [
		'header_id' => 'int',
		'coa_id' => 'int',
		'amount' => 'float',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'header_id',
		'header_code',
		'coa_id',
		'coa_code',
		'coa_name',
		'debit_kredit',
		'description',
		'amount',
		'created_by',
		'updated_by'
	];

	public function coa()
	{
		return $this->belongsTo(Coa::class);
	}

	public function bank_cashout_header()
	{
		return $this->belongsTo(BankCashoutHeader::class, 'header_id');
	}
}

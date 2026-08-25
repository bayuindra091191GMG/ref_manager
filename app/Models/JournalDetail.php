<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class JournalDetail
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string $transaction_code
 * @property string|null $reference_code
 * @property string|null $account_id
 * @property string|null $account_code
 * @property string|null $account_name
 * @property string|null $account_type_id
 * @property string|null $account_type_name
 * @property float|null $debit
 * @property float|null $credit
 * @property float|null $rate
 * @property string|null $transaction_type
 * @property float|null $original_amount
 * @property string|null $notes
 * @property int|null $header_id
 * 
 * @property JournalHeader|null $journal_header
 *
 * @package App\Models
 */
class JournalDetail extends Model
{
	protected $table = 'journal_details';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id' => 'int',
		'branch_id' => 'int',
		'debit' => 'float',
		'credit' => 'float',
		'rate' => 'float',
		'original_amount' => 'float',
		'header_id' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'transaction_code',
		'reference_code',
		'account_id',
		'account_code',
		'account_name',
		'account_type_id',
		'account_type_name',
		'debit',
		'credit',
		'rate',
		'transaction_type',
		'original_amount',
		'notes',
		'header_id'
	];

	public function journal_header()
	{
		return $this->belongsTo(JournalHeader::class, 'header_id');
	}
}

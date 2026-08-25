<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class JournalHeader
 *
 * @property int $id
 * @property string $transaction_code
 * @property Carbon|null $transaction_date
 * @property string|null $transaction_type
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * @property int|null $branch_id
 * @property int|null $status_id
 * @property int|null $is_approve
 * @property int|null $reference_id
 *
 * @property Branch|null $branch
 * @property Collection|JournalDetail[] $journal_details
 *
 * @package App\Models
 */
class JournalHeader extends Model
{
	protected $table = 'journal_headers';

	protected $casts = [
		'created_by' => 'int',
		'updated_by' => 'int',
		'branch_id' => 'int',
        'reference_id' => 'int'
	];

	protected $dates = [
		'transaction_date'
	];

	protected $fillable = [
		'transaction_code',
		'transaction_date',
		'transaction_type',
		'notes',
		'created_by',
		'updated_by',
		'branch_id',
		'status_id',
		'is_approve',
        'reference_id'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function journal_details()
	{
		return $this->hasMany(JournalDetail::class, 'header_id');
	}
}

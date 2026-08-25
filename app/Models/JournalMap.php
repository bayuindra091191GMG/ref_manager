<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class JournalMap
 *
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $coa_id
 * @property string|null $coa_name
 * @property string|null $coa_code
 * @property string|null $journal_type
 * @property string|null $transaction_type
 * @property Carbon|null $created_at
 * @property string|null $room_type_id
 * @property int|null $category_2_id
 *
 * @package App\Models
 */
class JournalMap extends Model
{
	protected $table = 'journal_maps';
	public $timestamps = false;

	protected $casts = [
		'branch_id' => 'int',
		'coa_id' => 'int',
		'category_2_id' => 'int',
	];

	protected $fillable = [
		'branch_id',
		'coa_id',
		'coa_name',
		'coa_code',
		'journal_type',
		'transaction_type',
		'room_type_id',
		'category_2_id',
	];
}

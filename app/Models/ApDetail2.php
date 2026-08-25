<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ApDetail2
 * 
 * @property int $id
 * @property int|null $header_id
 * @property string|null $header_code
 * @property int|null $coa_id
 * @property string|null $coa_code
 * @property float|null $debit
 * @property float|null $kredit
 * @property string|null $description
 * 
 * @property Coa|null $coa
 * @property ApHeader|null $ap_header
 *
 * @package App\Models
 */
class ApDetail2 extends Model
{
	protected $table = 'ap_detail2s';
	public $timestamps = false;

	protected $casts = [
		'header_id' => 'int',
		'coa_id' => 'int',
		'debit' => 'float',
		'kredit' => 'float'
	];

	protected $fillable = [
		'header_id',
		'header_code',
		'coa_id',
		'coa_code',
		'debit',
		'kredit',
		'description'
	];

	public function coa()
	{
		return $this->belongsTo(Coa::class);
	}

	public function ap_header()
	{
		return $this->belongsTo(ApHeader::class, 'header_id');
	}
}

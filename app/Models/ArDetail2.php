<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ArDetail2
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
 * @property ArHeader|null $ar_header
 *
 * @package App\Models
 */
class ArDetail2 extends Model
{
	protected $table = 'ar_detail2s';
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

	public function ar_header()
	{
		return $this->belongsTo(ArHeader::class, 'header_id');
	}
}

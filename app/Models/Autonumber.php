<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Autonumber
 *
 * @property int $id
 * @property string|null $doc_code
 * @property int|null $next_no
 * @property int|null $current_year
 * @property int|null $current_month
 *
 * @package App\Models
 */
class Autonumber extends Model
{
	protected $table = 'autonumbers';
	public $timestamps = false;

	protected $casts = [
		'next_no' => 'int',
        'current_month' => 'int',
        'current_year' => 'int',
	];

	protected $fillable = [
		'doc_code',
		'next_no',
        'current_year',
        'current_month'
	];
}

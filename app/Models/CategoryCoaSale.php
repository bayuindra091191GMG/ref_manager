<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CategoryCoaSale
 * 
 * @property int $id
 * @property int|null $category_id
 * @property int|null $coa_id
 *
 * @package App\Models
 */
class CategoryCoaSale extends Model
{
	protected $table = 'category_coa_sales';
	public $timestamps = false;

	protected $casts = [
		'category_id' => 'int',
		'coa_id' => 'int'
	];

	protected $fillable = [
		'category_id',
		'coa_id'
	];
}

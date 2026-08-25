<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RoomType
 * 
 * @property int $id
 * @property string|null $name
 * @property string|null $display
 * @property int|null $branch_id
 * 
 * @property Collection|Package[] $packages
 *
 * @package App\Models
 */
class RoomType extends Model
{
	protected $table = 'room_types';
	public $timestamps = false;

	protected $casts = [
		'branch_id' => 'int'
	];

	protected $fillable = [
		'name',
		'display',
		'branch_id'
	];

	public function packages()
	{
		return $this->hasMany(Package::class);
	}
}

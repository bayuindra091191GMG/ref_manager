<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Status
 * 
 * @property int $id
 * @property string|null $description
 * @property string|null $display
 * 
 * @property Collection|AdminUser[] $admin_users
 * @property Collection|Locker[] $lockers
 * @property Collection|Room[] $rooms
 *
 * @package App\Models
 */
class Status extends Model
{
	protected $table = 'statuses';
	public $timestamps = false;

	protected $fillable = [
		'description',
		'display'
	];

	public function admin_users()
	{
		return $this->hasMany(AdminUser::class);
	}

	public function lockers()
	{
		return $this->hasMany(Locker::class);
	}

	public function rooms()
	{
		return $this->hasMany(Room::class);
	}
}

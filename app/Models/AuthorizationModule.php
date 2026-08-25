<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AuthorizationModule
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $menu_level_1_name
 * @property string|null $menu_level_2_name
 * @property string|null $menu_level_3_name
 * @property string|null $action
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Branch|null $branch
 * @property Collection|AuthorizationPosition[] $authorization_positions
 *
 * @package App\Models
 */
class AuthorizationModule extends Model
{
	protected $table = 'authorization_modules';

	protected $casts = [
		'branch_id' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'menu_level_1_name',
		'menu_level_2_name',
		'menu_level_3_name',
		'action'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function authorization_positions()
	{
		return $this->hasMany(AuthorizationPosition::class, 'auth_module_id');
	}
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PartnerLevel
 *
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $code
 * @property string|null $name
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 *
 * @property Branch|null $branch
 * @property Collection|Partner[] $partners
 *
 * @package App\Models
 */
class PartnerLevel extends Model
{
	protected $table = 'partner_levels';

	protected $casts = [
		'branch_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'code',
		'name',
		'created_by',
		'updated_by'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function partners()
	{
		return $this->hasMany(Partner::class, 'level_id');
	}

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}

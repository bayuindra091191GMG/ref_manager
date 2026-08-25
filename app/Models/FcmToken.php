<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Menu
 *
 * @property int $id
 * @property int|null $sequence
 * @property string|null $category
 * @property string|null $code
 * @property string|null $name
 * @property string|null $actions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class FcmToken extends Model
{
    protected $table = 'fcm_tokens';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}

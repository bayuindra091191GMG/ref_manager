<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PasswordReset
 *
 * @property string $email
 * @property string $token
 * @property Carbon|null $created_at
 *
 * @package App\Models
 */
class PkwtHistory extends Model
{
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}

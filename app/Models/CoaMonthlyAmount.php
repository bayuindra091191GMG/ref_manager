<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;


class CoaMonthlyAmount extends Model
{
    protected $table = 'coa_monthly_amounts';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}

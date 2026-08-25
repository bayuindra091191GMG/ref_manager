<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingCart extends Model
{
    protected $table = 'booking_carts';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }
}

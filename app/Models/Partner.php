<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
	protected $table = 'partners';

	protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function partner_level()
	{
		return $this->belongsTo(PartnerLevel::class, 'level_id');
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function booking_notes()
	{
		return $this->hasMany(BookingNote::class);
	}

	public function packages()
	{
		return $this->belongsToMany(Package::class, 'commission_partner_packages')
					->withPivot('id', 'branch_id', 'template_id', 'commission_percentage', 'commission_flat', 'extend_percentage', 'extend_float', 'udf_01', 'udf_02', 'udf_03', 'status_id', 'created_by', 'updated_by')
					->withTimestamps();
	}

	public function transaction_details()
	{
		return $this->belongsToMany(TransactionDetail::class, 'transaction_detail_partners')
					->withPivot('id', 'is_main', 'is_extend', 'is_checkout', 'book_name');
	}

    public function contract()
    {
        return $this->hasOne(PartnerContract::class);
    }
}

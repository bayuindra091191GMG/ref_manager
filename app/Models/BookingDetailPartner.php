<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BookingDetailPartner
 *
 * @property int $id
 * @property int|null $detail_id
 * @property int|null $partner_id
 * @property string|null $partner_name
 * @property string|null $partner_code
 * @property string|null $partner_alias
 * @property int|null $partner_number
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property BookingDetail|null $booking_detail
 * @property Partner|null $partner
 *
 * @package App\Models
 */
class BookingDetailPartner extends Model
{
	protected $table = 'booking_detail_partners';

	protected $casts = [
		'detail_id' => 'int',
		'partner_id' => 'int',
		'partner_number' => 'int',
		'status_id' => 'int'
	];

	protected $fillable = [
		'detail_id',
		'partner_id',
		'partner_name',
		'partner_code',
		'partner_alias',
		'partner_number',
		'status_id'
	];

	public function booking_detail()
	{
		return $this->belongsTo(BookingDetail::class, 'detail_id');
	}

	public function partner()
	{
		return $this->belongsTo(Partner::class, 'partner_id');
	}
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TransactionDetail
 *
 * @property int $id
 * @property int|null $header_id
 * @property int|null $package_id
 * @property int|null $room_id
 * @property int|null $status_id
 * @property int|null $qty
 * @property float|null $price
 * @property float|null $subtotal
 * @property Carbon|null $start_time
 * @property Carbon|null $end_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $item_id
 * @property int|null $is_fnb
 * @property int|null $is_extended
 * @property int|null $is_called
 * @property float|null $extension_amount
 * @property int|null $extension_times
 * @property float|null $additional_person_amount
 * @property int|null $additional_person_qty
 * @property float|null $additional_partner_amount
 * @property int|null $additional_partner_qty
 * @property int|null $discount_percentage
 * @property float|null $discount_amount
 * @property int|null $membership_id
 * @property int|null $is_facility_only
 * @property int|null $is_cut
 * @property float|null $subtotal_after_commission
 * @property int|null $member_id
 * @property int|null $new_order_qty
 * @property int|null $is_new_order
 * @property string|null $customer_name
 * @property bool|null $is_ignore_monitor
 * @property int|null $partner_id
 * @property string|null $partner_level
 * @property int|null $free_partners
 *
 * @property TransactionHeader|null $transaction_header
 * @property Item|null $item
 * @property Membership|null $membership
 * @property Package|null $package
 * @property Room|null $room
 * @property Collection|Partner[] $partners
 *
 * @package App\Models
 */
class TransactionDetail extends Model
{
	protected $table = 'transaction_details';

	protected $casts = [
		'header_id' => 'int',
		'package_id' => 'int',
		'room_id' => 'int',
		'status_id' => 'int',
		'qty' => 'int',
		'price' => 'float',
		'subtotal' => 'float',
		'start_time' => 'datetime',
		'end_time' => 'datetime',
		'item_id' => 'int',
		'is_fnb' => 'int',
		'is_extended' => 'int',
		'is_called' => 'int',
		'extension_amount' => 'float',
		'extension_times' => 'int',
		'additional_person_amount' => 'float',
		'additional_person_qty' => 'int',
		'additional_partner_amount' => 'float',
		'additional_partner_qty' => 'int',
		'discount_percentage' => 'int',
		'discount_amount' => 'float',
		'membership_id' => 'int',
		'is_facility_only' => 'int',
		'is_cut' => 'int',
		'subtotal_after_commission' => 'float',
		'member_id' => 'int',
		'new_order_qty' => 'int',
		'is_new_order' => 'int',
		'is_ignore_monitor' => 'bool',
        'partner_id' => 'int',
        'free_partners' => 'int'
	];

    protected $guarded = [
        'created_at',
        'updated_at',
    ];

	public function transaction_header()
	{
		return $this->belongsTo(TransactionHeader::class, 'header_id');
	}

    public function transaction_detail_partner()
    {
        return $this->hasOne(TransactionDetailPartner::class, 'transaction_detail_id');
    }

	public function item()
	{
		return $this->belongsTo(Item::class);
	}

    public function coupon()
    {
        return $this->belongsTo(UserVoucher::class, 'coupon_id');
    }

	public function membership()
	{
		return $this->belongsTo(Membership::class);
	}

	public function package()
	{
		return $this->belongsTo(Package::class);
	}

	public function room()
	{
		return $this->belongsTo(Room::class);
	}

    public function user_voucher()
    {
        return $this->belongsTo(UserVoucher::class, 'coupon_id');
    }

	public function partners()
	{
		return $this->belongsToMany(Partner::class, 'transaction_detail_partners')
					->withPivot('id', 'is_main', 'is_extend', 'is_checkout', 'book_name');
	}
}

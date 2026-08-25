<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserReview
 *
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $user_id
 * @property string|null $user_phone
 * @property string|null $user_name
 * @property int|null $booking_id
 * @property int|null $booking_detail_id
 * @property int|null $package_id
 * @property string|null $package_name
 * @property int|null $partner_id
 * @property string|null $partner_number
 * @property string|null $partner_alias
 * @property string|null $partner_name
 * @property float|null $star
 * @property string|null $text
 * @property bool|null $is_image_uploaded
 * @property Carbon|null $reviewed_at
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property BookingHeader|null $booking_header
 * @property Branch|null $branch
 * @property Collection|UserReviewImage[] $user_review_images
 *
 * @package App\Models
 */
class UserVoucherCode extends Model
{
    protected $table = 'user_voucher_codes';

    protected $casts = [
        'header_id' => 'int',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function user_voucher()
	{
		return $this->belongsTo(UserVoucher::class, 'header_id', 'id');
	}
}

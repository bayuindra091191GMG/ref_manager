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
class UserReview extends Model
{
	protected $table = 'user_reviews';

	protected $casts = [
		'branch_id' => 'int',
		'user_id' => 'int',
		'booking_id' => 'int',
        'booking_detail_id' => 'int',
		'package_id' => 'int',
        'item_id' => 'int',
        'pranikah_id' => 'int',
        'pranikah_header_id' => 'int',
		'partner_id' => 'int',
		'star' => 'float',
		'status_id' => 'int',
        'is_image_uploaded' => 'bool'
	];

	protected $fillable = [
		'branch_id',
		'user_id',
		'user_phone',
		'user_name',
		'booking_id',
		'booking_detail_id',
		'package_id',
		'package_name',
        'item_id',
        'item_name',
        'pranikah_id',
        'pranikah_header_id',
        'pranikah_name',
		'partner_id',
		'partner_number',
		'partner_alias',
		'partner_name',
		'star',
		'text',
        'is_image_uploaded',
        'reviewed_at',
		'status_id'
	];

	public function booking_header()
	{
		return $this->belongsTo(BookingHeader::class, 'booking_id');
	}

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function user_review_images()
	{
		return $this->hasMany(UserReviewImage::class, 'review_id');
	}
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserReviewImage
 * 
 * @property int $id
 * @property int|null $review_id
 * @property string|null $image_filename
 * @property bool|null $is_main
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property UserReview|null $user_review
 *
 * @package App\Models
 */
class UserReviewImage extends Model
{
	protected $table = 'user_review_images';

	protected $casts = [
		'review_id' => 'int',
		'is_main' => 'bool'
	];

	protected $fillable = [
		'review_id',
		'image_filename',
		'is_main'
	];

	public function user_review()
	{
		return $this->belongsTo(UserReview::class, 'review_id');
	}
}

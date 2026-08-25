<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BookingNote
 *
 * @property int $id
 * @property string|null $book_by
 * @property int|null $partner_id
 * @property Carbon|null $book_date
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * @property int|null $branch_id
 * @property string|null $status
 *
 * @property Branch|null $branch
 * @property AdminUser|null $updatedBy
 * @property AdminUser|null $createdBy
 * @property Partner|null $partner
 *
 * @package App\Models
 */
class BookingNote extends Model
{
	protected $table = 'booking_notes';

	protected $casts = [
		'partner_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int',
		'branch_id' => 'int'
	];

	protected $dates = [
		'book_date'
	];

	protected $fillable = [
		'book_by',
		'partner_id',
		'book_date',
		'created_by',
		'updated_by',
		'branch_id',
		'status'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function updatedBy()
	{
		return $this->belongsTo(AdminUser::class, 'updated_by');
	}

    public function createdBy()
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

	public function partner()
	{
		return $this->belongsTo(Partner::class);
	}
}

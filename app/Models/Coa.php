<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Coa
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $code
 * @property string|null $name
 * @property int|null $type_id
 * @property string|null $type
 * @property string|null $description
 * @property string|null $level
 * @property int|null $is_header
 * @property int|null $parent_id
 * @property string|null $parent_code
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property Branch|null $branch
 * @property Status|null $status
 * @property Collection|ItemCategory[] $item_categories
 * @property Collection|PurchaseInvoiceDetail2[] $purchase_invoice_detail2s
 *
 * @package App\Models
 */
class Coa extends Model
{
	protected $table = 'coas';

	protected $casts = [
		'branch_id' => 'int',
		'type_id' => 'int',
		'is_header' => 'int',
		'parent_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'branch_id',
		'code',
		'name',
		'type_id',
		'type',
		'description',
		'level',
		'is_header',
		'parent_id',
		'parent_code',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function item_categories()
	{
		return $this->hasMany(ItemCategory::class, 'coa_stock');
	}

	public function purchase_invoice_detail2s()
	{
		return $this->hasMany(PurchaseInvoiceDetail2::class);
	}
}

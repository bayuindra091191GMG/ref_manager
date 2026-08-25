<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PurchaseOrderHeader
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $is_approve
 * @property string|null $code
 * @property int|null $reference_id
 * @property string|null $reference_code
 * @property int|null $supplier_id
 * @property int|null $warehouse_id
 * @property Carbon|null $transaction_date
 * @property int|null $top
 * @property float|null $subtotal
 * @property int|null $disc1_percent
 * @property float|null $disc1_value
 * @property int|null $disc2_percent
 * @property float|null $disc2_value
 * @property int|null $ppn_percent
 * @property float|null $ppn_value
 * @property float|null $grandtotal
 * @property string|null $description
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * @property int|null $is_gr
 * 
 * @property Branch|null $branch
 * @property PurchaseRequestHeader|null $purchase_request_header
 * @property Status|null $status
 * @property Supplier|null $supplier
 * @property Warehouse|null $warehouse
 * @property Collection|PurchaseOrderDetail[] $purchase_order_details
 *
 * @package App\Models
 */
class PurchaseOrderHeader extends Model
{
	protected $table = 'purchase_order_headers';

	protected $casts = [
		'branch_id' => 'int',
		'is_approve' => 'int',
		'reference_id' => 'int',
		'supplier_id' => 'int',
		'warehouse_id' => 'int',
		'top' => 'int',
		'subtotal' => 'float',
		'disc1_percent' => 'int',
		'disc1_value' => 'float',
		'disc2_percent' => 'int',
		'disc2_value' => 'float',
		'ppn_percent' => 'int',
		'ppn_value' => 'float',
		'grandtotal' => 'float',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int',
		'is_gr' => 'int'
	];

	protected $dates = [
		'transaction_date'
	];

	protected $fillable = [
		'branch_id',
		'is_approve',
		'code',
		'reference_id',
		'reference_code',
		'supplier_id',
		'warehouse_id',
		'transaction_date',
		'top',
		'subtotal',
		'disc1_percent',
		'disc1_value',
		'disc2_percent',
		'disc2_value',
		'ppn_percent',
		'ppn_value',
		'grandtotal',
		'description',
		'status_id',
		'created_by',
		'updated_by',
		'is_gr'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function purchase_request_header()
	{
		return $this->belongsTo(PurchaseRequestHeader::class, 'reference_id');
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function supplier()
	{
		return $this->belongsTo(Supplier::class);
	}

	public function warehouse()
	{
		return $this->belongsTo(Warehouse::class);
	}

	public function purchase_order_details()
	{
		return $this->hasMany(PurchaseOrderDetail::class, 'header_id');
	}
}

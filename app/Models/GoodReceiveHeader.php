<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GoodReceiveHeader
 *
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $is_approve
 * @property string|null $code
 * @property Carbon|null $date
 * @property int|null $po_id
 * @property string|null $po_code
 * @property int|null $pr_id
 * @property string|null $pr_code
 * @property int|null $supplier_id
 * @property int|null $warehouse_id
 * @property int|null $receiver_id
 * @property string|null $receiver_name
 * @property string|null $description
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * @property string|null $deleted_at
 * @property int|null $deleted_by
 * @property int|null $delete_status
 * @property int|null $is_close
 * @property string|null $batch_number
 *
 * @property Branch|null $branch
 * @property PurchaseOrderHeader|null $purchase_order_header
 * @property PurchaseRequestHeader|null $purchase_request_header
 * @property Status|null $status
 * @property Supplier|null $supplier
 * @property Warehouse|null $warehouse
 * @property Collection|GoodReceiveDetail[] $good_receive_details
 * @property Collection|SupplierInvoiceDetail1[] $supplier_invoice_detail1s
 *
 * @package App\Models
 */
class GoodReceiveHeader extends Model
{
	// use SoftDeletes;
	protected $table = 'good_receive_headers';

	protected $casts = [
		'branch_id' => 'int',
		'is_approve' => 'int',
		'po_id' => 'int',
		'pr_id' => 'int',
		'supplier_id' => 'int',
		'warehouse_id' => 'int',
		'receiver_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int',
		'deleted_by' => 'int',
		'delete_status' => 'int',
		'is_close' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'branch_id',
		'is_approve',
		'code',
		'date',
		'po_id',
		'po_code',
		'pr_id',
		'pr_code',
		'supplier_id',
		'warehouse_id',
		'receiver_id',
		'receiver_name',
		'description',
		'status_id',
		'created_by',
		'updated_by',
		'deleted_by',
		'delete_status',
		'is_close',
		'batch_number'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function purchase_order_header()
	{
		return $this->belongsTo(PurchaseOrderHeader::class, 'po_id');
	}

	public function purchase_request_header()
	{
		return $this->belongsTo(PurchaseRequestHeader::class, 'pr_id');
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

	public function good_receive_details()
	{
		return $this->hasMany(GoodReceiveDetail::class, 'header_id');
	}

	public function supplier_invoice_detail1s()
	{
		return $this->hasMany(SupplierInvoiceDetail1::class, 'gr_id');
	}
}

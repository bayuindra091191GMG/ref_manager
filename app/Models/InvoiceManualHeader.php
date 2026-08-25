<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class InvoiceManualHeader
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $is_approve
 * @property string|null $code
 * @property Carbon|null $transaction_date
 * @property int|null $warehouse_id
 * @property string|null $warehouse_name
 * @property int|null $customer_id
 * @property string|null $customer_name
 * @property int|null $coa_id
 * @property string|null $coa_code
 * @property string|null $purchase_type
 * @property float|null $subtotal
 * @property int|null $disc_percent
 * @property float|null $disc_value
 * @property int|null $ppn_percent
 * @property float|null $ppn_value
 * @property float|null $grandtotal
 * @property string|null $description
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * 
 * @property Branch|null $branch
 * @property Coa|null $coa
 * @property Customer|null $customer
 * @property Status|null $status
 * @property Warehouse|null $warehouse
 * @property Collection|InvoiceManualDetail[] $invoice_manual_details
 *
 * @package App\Models
 */
class InvoiceManualHeader extends Model
{
	protected $table = 'invoice_manual_headers';

	protected $casts = [
		'branch_id' => 'int',
		'is_approve' => 'int',
		'warehouse_id' => 'int',
		'customer_id' => 'int',
		'coa_id' => 'int',
		'subtotal' => 'float',
		'disc_percent' => 'int',
		'disc_value' => 'float',
		'ppn_percent' => 'int',
		'ppn_value' => 'float',
		'grandtotal' => 'float',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'transaction_date'
	];

	protected $fillable = [
		'branch_id',
		'is_approve',
		'code',
		'transaction_date',
		'warehouse_id',
		'warehouse_name',
		'customer_id',
		'customer_name',
		'coa_id',
		'coa_code',
		'purchase_type',
		'subtotal',
		'disc_percent',
		'disc_value',
		'ppn_percent',
		'ppn_value',
		'grandtotal',
		'description',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function coa()
	{
		return $this->belongsTo(Coa::class);
	}

	public function customer()
	{
		return $this->belongsTo(Customer::class);
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function warehouse()
	{
		return $this->belongsTo(Warehouse::class);
	}

	public function invoice_manual_details()
	{
		return $this->hasMany(InvoiceManualDetail::class, 'header_id');
	}
}

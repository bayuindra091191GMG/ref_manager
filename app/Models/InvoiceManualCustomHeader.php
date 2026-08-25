<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class InvoiceManualCustomHeader
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $is_approve
 * @property string|null $code
 * @property Carbon|null $transaction_date
 * @property int|null $warehouse_id
 * @property string|null $warehouse_name
 * @property string|null $customer_name
 * @property string|null $customer_type
 * @property int|null $employee_id
 * @property string|null $employee_name
 * @property int|null $partner_id
 * @property string|null $partner_number
 * @property string|null $partner_alias
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
 * @property Status|null $status
 * @property Collection|ArDetail1[] $ar_detail1s
 * @property Collection|InvoiceManualCustomDetail[] $invoice_manual_custom_details
 *
 * @package App\Models
 */
class InvoiceManualCustomHeader extends Model
{
	protected $table = 'invoice_manual_custom_headers';

	protected $casts = [
		'branch_id' => 'int',
		'is_approve' => 'int',
		'warehouse_id' => 'int',
		'employee_id' => 'int',
		'partner_id' => 'int',
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
		'customer_name',
		'customer_type',
		'employee_id',
		'employee_name',
		'partner_id',
		'partner_number',
		'partner_alias',
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

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function ar_detail1s()
	{
		return $this->hasMany(ArDetail1::class, 'invoice_id');
	}

	public function invoice_manual_custom_details()
	{
		return $this->hasMany(InvoiceManualCustomDetail::class, 'header_id');
	}
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SupplierInvoiceHeader
 * 
 * @property int $id
 * @property int|null $branch_id
 * @property int|null $is_approve
 * @property string|null $type
 * @property string|null $code
 * @property int|null $reference_id
 * @property string|null $reference_code
 * @property int|null $coa_id
 * @property string|null $coa_code
 * @property string|null $image_path
 * @property int|null $supplier_id
 * @property Carbon|null $date
 * @property int|null $top
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
 * @property Supplier|null $supplier
 * @property Collection|SupplierInvoiceDetail1[] $supplier_invoice_detail1s
 * @property Collection|SupplierInvoiceDetail2[] $supplier_invoice_detail2s
 *
 * @package App\Models
 */
class SupplierInvoiceHeader extends Model
{
	protected $table = 'supplier_invoice_headers';

	protected $casts = [
		'branch_id' => 'int',
		'is_approve' => 'int',
		'reference_id' => 'int',
		'coa_id' => 'int',
		'supplier_id' => 'int',
		'top' => 'int',
		'grandtotal' => 'float',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'branch_id',
		'is_approve',
		'type',
		'code',
		'reference_id',
		'reference_code',
		'coa_id',
		'coa_code',
		'image_path',
		'supplier_id',
		'date',
		'top',
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

	public function supplier()
	{
		return $this->belongsTo(Supplier::class);
	}

	public function supplier_invoice_detail1s()
	{
		return $this->hasMany(SupplierInvoiceDetail1::class, 'header_id');
	}

	public function supplier_invoice_detail2s()
	{
		return $this->hasMany(SupplierInvoiceDetail2::class, 'header_id');
	}
}

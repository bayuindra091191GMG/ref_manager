<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ApDetail1
 * 
 * @property int $id
 * @property int|null $header_id
 * @property string|null $header_code
 * @property int|null $supplier_invoice_id
 * @property string|null $supplier_invoice_code
 * @property float|null $debit
 * @property float|null $kredit
 * @property string|null $description
 * 
 * @property ApHeader|null $ap_header
 * @property SupplierInvoiceHeader|null $supplier_invoice_header
 *
 * @package App\Models
 */
class ApDetail1 extends Model
{
	protected $table = 'ap_detail1s';
	public $timestamps = false;

	protected $casts = [
		'header_id' => 'int',
		'supplier_invoice_id' => 'int',
		'debit' => 'float',
		'kredit' => 'float'
	];

	protected $fillable = [
		'header_id',
		'header_code',
		'supplier_invoice_id',
		'supplier_invoice_code',
		'debit',
		'kredit',
		'description'
	];

	public function ap_header()
	{
		return $this->belongsTo(ApHeader::class, 'header_id');
	}

	public function supplier_invoice_header()
	{
		return $this->belongsTo(SupplierInvoiceHeader::class, 'supplier_invoice_id');
	}
}

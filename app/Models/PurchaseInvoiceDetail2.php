<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PurchaseInvoiceDetail2
 * 
 * @property int $id
 * @property int|null $header_id
 * @property string|null $header_code
 * @property int|null $coa_id
 * @property string|null $coa_code
 * @property float|null $total
 * @property string|null $description
 * 
 * @property Coa|null $coa
 * @property PurchaseInvoiceHeader|null $purchase_invoice_header
 *
 * @package App\Models
 */
class PurchaseInvoiceDetail2 extends Model
{
	protected $table = 'purchase_invoice_detail2s';
	public $timestamps = false;

	protected $casts = [
		'header_id' => 'int',
		'coa_id' => 'int',
		'total' => 'float'
	];

	protected $fillable = [
		'header_id',
		'header_code',
		'coa_id',
		'coa_code',
		'total',
		'description'
	];

	public function coa()
	{
		return $this->belongsTo(Coa::class);
	}

	public function purchase_invoice_header()
	{
		return $this->belongsTo(PurchaseInvoiceHeader::class, 'header_id');
	}
}

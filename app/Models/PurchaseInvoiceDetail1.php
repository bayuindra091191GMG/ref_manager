<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PurchaseInvoiceDetail1
 * 
 * @property int $id
 * @property int|null $header_id
 * @property string|null $header_code
 * @property int|null $gr_id
 * @property string|null $gr_code
 * @property float|null $total
 * @property string|null $no_invoice
 * @property string|null $description
 * 
 * @property GoodReceiveHeader|null $good_receive_header
 * @property PurchaseInvoiceHeader|null $purchase_invoice_header
 *
 * @package App\Models
 */
class PurchaseInvoiceDetail1 extends Model
{
	protected $table = 'purchase_invoice_detail1s';
	public $timestamps = false;

	protected $casts = [
		'header_id' => 'int',
		'gr_id' => 'int',
		'total' => 'float'
	];

	protected $fillable = [
		'header_id',
		'header_code',
		'gr_id',
		'gr_code',
		'total',
		'no_invoice',
		'description'
	];

	public function good_receive_header()
	{
		return $this->belongsTo(GoodReceiveHeader::class, 'gr_id');
	}

	public function purchase_invoice_header()
	{
		return $this->belongsTo(PurchaseInvoiceHeader::class, 'header_id');
	}
}

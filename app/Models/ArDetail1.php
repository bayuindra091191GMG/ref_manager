<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ArDetail1
 * 
 * @property int $id
 * @property int|null $header_id
 * @property string|null $header_code
 * @property int|null $invoice_id
 * @property string|null $invoice_code
 * @property float|null $nominal_invoice
 * @property float|null $debit
 * @property float|null $kredit
 * @property string|null $description
 * 
 * @property ArHeader|null $ar_header
 * @property InvoiceManualCustomHeader|null $invoice_manual_custom_header
 *
 * @package App\Models
 */
class ArDetail1 extends Model
{
	protected $table = 'ar_detail1s';
	public $timestamps = false;

	protected $casts = [
		'header_id' => 'int',
		'invoice_id' => 'int',
		'nominal_invoice' => 'float',
		'debit' => 'float',
		'kredit' => 'float'
	];

	protected $fillable = [
		'header_id',
		'header_code',
		'invoice_id',
		'invoice_code',
		'nominal_invoice',
		'debit',
		'kredit',
		'description'
	];

	public function ar_header()
	{
		return $this->belongsTo(ArHeader::class, 'header_id');
	}

	public function invoice_manual_custom_header()
	{
		return $this->belongsTo(InvoiceManualCustomHeader::class, 'invoice_id');
	}
}

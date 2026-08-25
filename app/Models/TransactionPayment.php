<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TransactionPayment
 *
 * @property int $id
 * @property int|null $header_id
 * @property int|null $payment_type_id
 * @property string|null $payment_type_code
 * @property float|null $amount
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property int|null $branch_id
 * @property Carbon|null $update_at
 *
 * @property AdminUser|null $admin_user
 * @property TransactionHeader|null $transaction_header
 * @property PosPaymentType|null $pos_payment_type
 *
 * @package App\Models
 */
class TransactionPayment extends Model
{
	protected $table = 'transaction_payments';

	protected $casts = [
		'header_id' => 'int',
		'payment_type_id' => 'int',
		'amount' => 'float',
		'created_by' => 'int',
		'branch_id' => 'int'
	];

	protected $dates = [
		'update_at'
	];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

	public function admin_user()
	{
		return $this->belongsTo(AdminUser::class, 'created_by');
	}

    public function transaction_invoice()
    {
        return $this->belongsTo(TransactionInvoice::class);
    }

	public function pos_payment_type()
	{
		return $this->belongsTo(PosPaymentType::class, 'payment_type_id');
	}
}

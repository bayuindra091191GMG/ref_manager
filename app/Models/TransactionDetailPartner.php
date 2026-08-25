<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TransactionDetailPartner
 *
 * @property int $id
 * @property int|null $partner_id
 * @property int|null $transaction_detail_id
 * @property int|null $is_main
 * @property int|null $is_extend
 * @property int|null $is_checkout
 * @property string|null $book_name
 *
 * @property TransactionDetail|null $transaction_detail
 * @property Partner|null $partner
 *
 * @package App\Models
 */
class TransactionDetailPartner extends Model
{
	protected $table = 'transaction_detail_partners';
	public $timestamps = false;

	protected $casts = [
		'partner_id' => 'int',
		'transaction_detail_id' => 'int',
		'is_main' => 'int',
		'is_extend' => 'int',
		'is_checkout' => 'int'
	];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

	public function transaction_detail()
	{
		return $this->belongsTo(TransactionDetail::class);
	}

	public function partner()
	{
		return $this->belongsTo(Partner::class);
	}
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Item
 *
 * @property int $id
 * @property int|null $branch_id
 * @property string|null $barcode
 * @property string|null $code
 * @property string|null $name
 * @property string|null $image_path
 * @property int|null $have_bom
 * @property int|null $category_id
 * @property int|null $category_2_id
 * @property int|null $status_id
 * @property Carbon|null $created_at
 * @property int|null $created_by
 * @property Carbon|null $updated_at
 * @property int|null $updated_by
 * @property float|null $price
 * @property string|null $img_path
 * @property int|null $is_unlimited_stock
 * @property float|null $cost_price
 * @property float|null $manual_commission_amount
 * @property int|null $default_uom_id
 * @property float|null $manual_partner_commission_amount
 * @property bool|null $is_addon
 * @property bool|null $is_addon_partner_commission
 *
 * @property Branch|null $branch
 * @property ItemCategory|null $item_category
 * @property Status|null $status
 * @property Collection|BookingDetail[] $booking_details
 * @property Collection|Employee[] $employees
 * @property Collection|CommissionItemMonthlyDetail[] $commission_item_monthly_details
 * @property Collection|CommissionPartnerLevelItem[] $commission_partner_level_items
 * @property Collection|CommissionPositionItem[] $commission_position_items
 * @property Collection|GoodReceiveDetail[] $good_receive_details
 * @property Collection|InternalRequestDetail[] $internal_request_details
 * @property Collection|Inventory[] $inventories
 * @property Collection|InventoryHistory[] $inventory_histories
 * @property Collection|InventoryInDetail[] $inventory_in_details
 * @property Collection|InventoryOutDetail[] $inventory_out_details
 * @property Collection|InvoiceManualDetail[] $invoice_manual_details
 * @property Collection|ItemBom[] $item_boms
 * @property Collection|ItemTransferDetail[] $item_transfer_details
 * @property Collection|ItemUnit[] $item_units
 * @property Collection|PurchaseOrderDetail[] $purchase_order_details
 * @property Collection|PurchaseRequestDetail[] $purchase_request_details
 * @property Collection|StockInDetail[] $stock_in_details
 * @property Collection|TransactionDetail[] $transaction_details
 *
 * @package App\Models
 */
class Item extends Model
{
	protected $table = 'items';

	protected $casts = [
		'branch_id' => 'int',
		'have_bom' => 'int',
		'category_id' => 'int',
		'category_2_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int',
		'price' => 'float',
		'is_unlimited_stock' => 'int',
		'cost_price' => 'float',
		'manual_commission_amount' => 'float',
		'default_uom_id' => 'int',
		'manual_partner_commission_amount' => 'float',
        'is_addon' => 'bool',
        'is_addon_partner_commission' => 'bool'
	];

	protected $guarded = [
		'id',
		'created_at',
		'updated_at',
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}

	public function item_category()
	{
		return $this->belongsTo(ItemCategory::class, 'category_id');
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function booking_details()
	{
		return $this->hasMany(BookingDetail::class);
	}

	public function employees()
	{
		return $this->belongsToMany(Employee::class, 'commission_employee_items')
					->withPivot('id', 'branch_id', 'template_id', 'item_unit_id', 'commission_percentage', 'commission_flat', 'udf_01', 'udf_02', 'udf_03', 'status_id', 'created_by', 'updated_by')
					->withTimestamps();
	}

	public function commission_item_monthly_details()
	{
		return $this->hasMany(CommissionItemMonthlyDetail::class);
	}

	public function commission_partner_level_items()
	{
		return $this->hasMany(CommissionPartnerLevelItem::class);
	}

	public function commission_position_items()
	{
		return $this->hasMany(CommissionPositionItem::class);
	}

	public function good_receive_details()
	{
		return $this->hasMany(GoodReceiveDetail::class);
	}

	public function internal_request_details()
	{
		return $this->hasMany(InternalRequestDetail::class);
	}

	public function inventories()
	{
		return $this->hasMany(Inventory::class);
	}

	public function inventory_histories()
	{
		return $this->hasMany(InventoryHistory::class);
	}

	public function inventory_in_details()
	{
		return $this->hasMany(InventoryInDetail::class);
	}

	public function inventory_out_details()
	{
		return $this->hasMany(InventoryOutDetail::class);
	}

	public function invoice_manual_details()
	{
		return $this->hasMany(InvoiceManualDetail::class);
	}

	public function item_boms()
	{
		return $this->hasMany(ItemBom::class, 'material_id');
	}

	public function item_transfer_details()
	{
		return $this->hasMany(ItemTransferDetail::class);
	}

	public function item_units()
	{
		return $this->hasMany(ItemUnit::class);
	}

	public function purchase_order_details()
	{
		return $this->hasMany(PurchaseOrderDetail::class);
	}

	public function purchase_request_details()
	{
		return $this->hasMany(PurchaseRequestDetail::class);
	}

	public function stock_in_details()
	{
		return $this->hasMany(StockInDetail::class);
	}

	public function transaction_details()
	{
		return $this->hasMany(TransactionDetail::class);
	}
}

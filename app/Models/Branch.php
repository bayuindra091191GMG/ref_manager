<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Branch
 *
 * @property int $id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $description
 * @property string|null $address
 * @property string|null $item_unit_mode
 * @property string|null $image_filename
 * @property string|null $email_from
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property int|null $updated_by
 * @property int|null $is_oura
 * @property Carbon|null $updated_at
 * @property string|null $phone
 * @property string|null $extra_text
 * @property string|null $voucher_code
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int|null $home_service_fee
 * @property int|null $status_id
 *
 * @property Collection|AccountReceivable[] $account_receivables
 * @property Collection|AdminUser[] $admin_users
 * @property Collection|AuthorizationModule[] $authorization_modules
 * @property Collection|AuthorizationPosition[] $authorization_positions
 * @property Collection|BankCashinHeader[] $bank_cashin_headers
 * @property Collection|BankCashoutHeader[] $bank_cashout_headers
 * @property Collection|BookingNote[] $booking_notes
 * @property Collection|Coa[] $coas
 * @property Collection|Commision[] $commisions
 * @property Collection|CommissionEmployeeHistory[] $commission_employee_histories
 * @property Collection|CommissionEmployeeItem[] $commission_employee_items
 * @property Collection|CommissionItemMonthlyHeader[] $commission_item_monthly_headers
 * @property Collection|CommissionPartnerHistory[] $commission_partner_histories
 * @property Collection|CommissionPartnerLevelItem[] $commission_partner_level_items
 * @property Collection|CommissionPartnerLevelPackage[] $commission_partner_level_packages
 * @property Collection|CommissionPartnerPackage[] $commission_partner_packages
 * @property Collection|CommissionPositionItem[] $commission_position_items
 * @property Collection|CommissionTemplateHeader[] $commission_template_headers
 * @property Collection|CommissionTemplate[] $commission_templates
 * @property Collection|Coupon[] $coupons
 * @property Collection|Department[] $departments
 * @property Collection|EmployeePosition[] $employee_positions
 * @property Collection|Employee[] $employees
 * @property Collection|GoodReceiveHeader[] $good_receive_headers
 * @property Collection|InternalRequestHeader[] $internal_request_headers
 * @property Collection|Inventory[] $inventories
 * @property Collection|InventoryHistory[] $inventory_histories
 * @property Collection|InventoryInHeader[] $inventory_in_headers
 * @property Collection|InventoryOutHeader[] $inventory_out_headers
 * @property Collection|InvoiceManualCustomHeader[] $invoice_manual_custom_headers
 * @property Collection|InvoiceManualHeader[] $invoice_manual_headers
 * @property Collection|ItemBom[] $item_boms
 * @property Collection|ItemCategory[] $item_categories
 * @property Collection|ItemTransferHeader[] $item_transfer_headers
 * @property Collection|Item[] $items
 * @property Collection|Locker[] $lockers
 * @property Collection|Membership[] $memberships
 * @property Collection|Package[] $packages
 * @property Collection|PartnerLevel[] $partner_levels
 * @property Collection|Partner[] $partners
 * @property Collection|PurchaseOrderHeader[] $purchase_order_headers
 * @property Collection|PurchaseRequestHeader[] $purchase_request_headers
 * @property Collection|ReportGenerator[] $report_generators
 * @property Collection|Room[] $rooms
 * @property Collection|StockIn[] $stock_ins
 * @property Collection|SupplierInvoiceHeader[] $supplier_invoice_headers
 * @property Collection|Supplier[] $suppliers
 * @property Collection|TransactionHeader[] $transaction_headers
 * @property Collection|UnitMaterial[] $unit_materials
 * @property Collection|User[] $users
 * @property Collection|Warehouse[] $warehouses
 *
 * @package App\Models
 */
class Branch extends Model
{
	protected $table = 'branches';

	protected $casts = [
		'created_by' => 'int',
		'updated_by' => 'int',
		'is_oura' => 'int',
		'latitude' => 'float',
		'longitude' => 'float',
		'home_service_fee' => 'int',
        'status_id' => 'int'
	];

	protected $fillable = [
		'code',
		'name',
		'description',
		'address',
		'item_unit_mode',
		'image_filename',
		'email_from',
		'created_by',
		'updated_by',
		'phone',
		'extra_text',
		'is_oura',
		'latitude',
		'longitude',
		'voucher_code',
        'home_service_fee',
        'status_id'
	];

	public function account_receivables()
	{
		return $this->hasMany(AccountReceivable::class);
	}

	public function admin_users()
	{
		return $this->hasMany(AdminUser::class);
	}

	public function authorization_modules()
	{
		return $this->hasMany(AuthorizationModule::class);
	}

	public function authorization_positions()
	{
		return $this->hasMany(AuthorizationPosition::class);
	}

	public function bank_cashin_headers()
	{
		return $this->hasMany(BankCashinHeader::class);
	}

	public function bank_cashout_headers()
	{
		return $this->hasMany(BankCashoutHeader::class);
	}

	public function booking_notes()
	{
		return $this->hasMany(BookingNote::class);
	}

	public function coas()
	{
		return $this->hasMany(Coa::class);
	}

	public function commisions()
	{
		return $this->hasMany(Commision::class);
	}

	public function commission_employee_histories()
	{
		return $this->hasMany(CommissionEmployeeHistory::class);
	}

	public function commission_employee_items()
	{
		return $this->hasMany(CommissionEmployeeItem::class);
	}

	public function commission_item_monthly_headers()
	{
		return $this->hasMany(CommissionItemMonthlyHeader::class);
	}

	public function commission_partner_histories()
	{
		return $this->hasMany(CommissionPartnerHistory::class);
	}

	public function commission_partner_level_items()
	{
		return $this->hasMany(CommissionPartnerLevelItem::class);
	}

	public function commission_partner_level_packages()
	{
		return $this->hasMany(CommissionPartnerLevelPackage::class);
	}

	public function commission_partner_packages()
	{
		return $this->hasMany(CommissionPartnerPackage::class);
	}

	public function commission_position_items()
	{
		return $this->hasMany(CommissionPositionItem::class);
	}

	public function commission_template_headers()
	{
		return $this->hasMany(CommissionTemplateHeader::class);
	}

	public function commission_templates()
	{
		return $this->hasMany(CommissionTemplate::class);
	}

	public function coupons()
	{
		return $this->hasMany(Coupon::class);
	}

	public function departments()
	{
		return $this->hasMany(Department::class);
	}

	public function employee_positions()
	{
		return $this->hasMany(EmployeePosition::class);
	}

	public function employees()
	{
		return $this->hasMany(Employee::class);
	}

	public function good_receive_headers()
	{
		return $this->hasMany(GoodReceiveHeader::class);
	}

	public function internal_request_headers()
	{
		return $this->hasMany(InternalRequestHeader::class);
	}

	public function inventories()
	{
		return $this->hasMany(Inventory::class);
	}

	public function inventory_histories()
	{
		return $this->hasMany(InventoryHistory::class);
	}

	public function inventory_in_headers()
	{
		return $this->hasMany(InventoryInHeader::class);
	}

	public function inventory_out_headers()
	{
		return $this->hasMany(InventoryOutHeader::class);
	}

	public function invoice_manual_custom_headers()
	{
		return $this->hasMany(InvoiceManualCustomHeader::class);
	}

	public function invoice_manual_headers()
	{
		return $this->hasMany(InvoiceManualHeader::class);
	}

	public function item_boms()
	{
		return $this->hasMany(ItemBom::class);
	}

	public function item_categories()
	{
		return $this->hasMany(ItemCategory::class);
	}

	public function item_transfer_headers()
	{
		return $this->hasMany(ItemTransferHeader::class);
	}

	public function items()
	{
		return $this->hasMany(Item::class);
	}

	public function lockers()
	{
		return $this->hasMany(Locker::class);
	}

	public function memberships()
	{
		return $this->hasMany(Membership::class);
	}

	public function packages()
	{
		return $this->hasMany(Package::class);
	}

	public function partner_levels()
	{
		return $this->hasMany(PartnerLevel::class);
	}

	public function partners()
	{
		return $this->hasMany(Partner::class);
	}

	public function purchase_order_headers()
	{
		return $this->hasMany(PurchaseOrderHeader::class);
	}

	public function purchase_request_headers()
	{
		return $this->hasMany(PurchaseRequestHeader::class);
	}

	public function report_generators()
	{
		return $this->hasMany(ReportGenerator::class);
	}

	public function rooms()
	{
		return $this->hasMany(Room::class);
	}

	public function stock_ins()
	{
		return $this->hasMany(StockIn::class);
	}

	public function supplier_invoice_headers()
	{
		return $this->hasMany(SupplierInvoiceHeader::class);
	}

	public function suppliers()
	{
		return $this->hasMany(Supplier::class);
	}

	public function transaction_headers()
	{
		return $this->hasMany(TransactionHeader::class);
	}

	public function unit_materials()
	{
		return $this->hasMany(UnitMaterial::class);
	}

	public function users()
	{
		return $this->hasMany(User::class);
	}

	public function warehouses()
	{
		return $this->hasMany(Warehouse::class);
	}
}

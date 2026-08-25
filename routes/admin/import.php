<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ImportController;
use Illuminate\Support\Facades\Route;

Route::group( ['prefix' => 'import' ],function(){
    Route::get('employee/form', [ImportController::class, 'showEmployeeForm'])->name('admin.import.employee.form');
    Route::post('employee/upload', [ImportController::class, 'employeeImportSubmit'])->name('admin.import.employee.submit');
    Route::get('item/form', [ImportController::class, 'showItemForm'])->name('admin.import.item.form');
    Route::post('item/upload', [ImportController::class, 'itemImportSubmit'])->name('admin.import.item.submit');

    Route::get('item_adjust/form', [ImportController::class, 'showItemAdjustForm'])->name('admin.import.item-adjust.form');
    Route::post('item_adjust/upload', [ImportController::class, 'itemImportAdjustSubmit'])->name('admin.import.item-adjust.submit');

    Route::get('inventory/form', [ImportController::class, 'showInventoryForm'])->name('admin.import.inventory.form');
    Route::post('inventory/upload', [ImportController::class, 'inventoryImportSubmit'])->name('admin.import.inventory.submit');
    Route::get('basic_salary/form', [ImportController::class, 'showBasicSalaryForm'])->name('admin.import.basic_salary.form');
    Route::post('basic_salary/upload', [ImportController::class, 'basicSalaryImportSubmit'])->name('admin.import.basic_salary.submit');
    Route::get('item_buy_price/form', [ImportController::class, 'showItemBuyPriceForm'])->name('admin.import.item_buy_price.form');
    Route::post('item_buy_price/upload', [ImportController::class, 'itemBuyPriceSubmit'])->name('admin.import.item_buy_price.submit');
    Route::get('manyar_stock/form', [ImportController::class, 'StockManyarForm'])->name('admin.import.manyar_stock.form');
    Route::post('manyar_stock/upload', [ImportController::class, 'StockManyarSubmit'])->name('admin.import.manyar_stock.submit');
    Route::get('coa/form', [ImportController::class, 'coaForm'])->name('admin.import.coa.form');
    Route::post('coa/upload', [ImportController::class, 'coaImportSubmit'])->name('admin.import.coa.submit');

    Route::get('customers/form', function () {
        return view('admin.form_import_customer');
    })->name('admin.customer.import.form');

    Route::post('customers/form', [CustomerController::class, 'importCustomerProcess'])->name('admin.import.customers.process');
});

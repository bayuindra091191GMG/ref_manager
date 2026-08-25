<?php

use App\Http\Controllers\Admin\ApController;
use App\Http\Controllers\Admin\ArController;
use App\Http\Controllers\Admin\BankCashInController;
use App\Http\Controllers\Admin\BankCashOutController;
use App\Http\Controllers\Admin\CoaController;
use App\Http\Controllers\Admin\InvoiceManualController;
use App\Http\Controllers\Admin\InvoiceManualCustomController;
use App\Http\Controllers\Admin\JournalController;
use App\Http\Controllers\Admin\SupplierInvoiceController;
use Illuminate\Support\Facades\Route;



Route::prefix('bank_cash_out')->group(function(){
    Route::group(['middleware' => ['permission:bank_cash_out.list,admin']], function () {
        Route::get('/index', [BankCashOutController::class, 'index'])->name('admin.bank_cash_out.index');
        Route::get('/datatable', [BankCashOutController::class, 'getIndex'])->name('admin.bank_cash_out.datatable');
    });

    Route::group(['middleware' => ['permission:bank_cash_out.add,admin']], function () {
        Route::get('/create', [BankCashOutController::class, 'create'])->name('admin.bank_cash_out.create');
        Route::post('/store', [BankCashOutController::class, 'store'])->name('admin.bank_cash_out.store');
    });

    Route::group(['middleware' => ['permission:bank_cash_out.edit,admin']], function () {
        Route::get('/edit/{id}', [BankCashOutController::class, 'edit'])->name('admin.bank_cash_out.edit');
        Route::post('/update', [BankCashOutController::class, 'update'])->name('admin.bank_cash_out.update');
    });

    Route::group(['middleware' => ['permission:bank_cash_out.show,admin']], function () {
        Route::get('/show/{id}', [BankCashOutController::class, 'show'])->name('admin.bank_cash_out.show');
    });

    Route::group(['middleware' => ['permission:bank_cash_out.delete,admin']], function () {
        Route::post('/delete', [BankCashOutController::class, 'delete'])->name('admin.bank_cash_out.delete');
    });

    Route::get('/select', [BankCashOutController::class, 'select'])->name('admin.bank_cash_out.select');

    Route::group(['middleware' => ['permission:bank_cash_out.approve,admin']], function () {
        Route::post('/approve', [BankCashOutController::class, 'approve'])->name('admin.bank_cash_out.approve');
    });
    Route::post('/unapprove', [BankCashOutController::class, 'unapprove'])->name('admin.bank_cash_out.unapprove');
});

Route::prefix('bank_cash_in')->group(function(){
    Route::group(['middleware' => ['permission:bank_cash_in.list,admin']], function () {
        Route::get('/index', [BankCashInController::class, 'index'])->name('admin.bank_cash_in.index');
        Route::get('/datatable', [BankCashInController::class, 'getIndex'])->name('admin.bank_cash_in.datatable');
    });

    Route::group(['middleware' => ['permission:bank_cash_in.add,admin']], function () {
        Route::get('/create', [BankCashInController::class, 'create'])->name('admin.bank_cash_in.create');
        Route::post('/store', [BankCashInController::class, 'store'])->name('admin.bank_cash_in.store');
    });

    Route::group(['middleware' => ['permission:bank_cash_in.edit,admin']], function () {
        Route::get('/edit/{id}', [BankCashInController::class, 'edit'])->name('admin.bank_cash_in.edit');
        Route::post('/update', [BankCashInController::class, 'update'])->name('admin.bank_cash_in.update');
    });

    Route::group(['middleware' => ['permission:bank_cash_in.show,admin']], function () {
        Route::get('/show/{id}', [BankCashInController::class, 'show'])->name('admin.bank_cash_in.show');
    });

    Route::group(['middleware' => ['permission:bank_cash_in.delete,admin']], function () {
        Route::post('/delete', [BankCashInController::class, 'delete'])->name('admin.bank_cash_in.delete');
    });

    Route::get('/select', [BankCashInController::class, 'select'])->name('admin.bank_cash_in.select');

    Route::group(['middleware' => ['permission:bank_cash_in.approve,admin']], function () {
        Route::post('/approve', [BankCashInController::class, 'approve'])->name('admin.bank_cash_in.approve');
    });
    Route::post('/unapprove', [BankCashInController::class, 'unapprove'])->name('admin.bank_cash_in.unapprove');
});

Route::prefix('supplier_invoice')->group(function(){
    Route::group(['middleware' => ['permission:supplier_invoice.list,admin']], function () {
        Route::get('/index', [SupplierInvoiceController::class, 'index'])->name('admin.supplier_invoice.index');
        Route::get('/datatable', [SupplierInvoiceController::class, 'getIndex'])->name('admin.supplier_invoice.datatable');
    });

    Route::group(['middleware' => ['permission:supplier_invoice.add,admin']], function () {
        Route::get('/create', [SupplierInvoiceController::class, 'create'])->name('admin.supplier_invoice.create');
        Route::post('/store', [SupplierInvoiceController::class, 'store'])->name('admin.supplier_invoice.store');
    });

    Route::group(['middleware' => ['permission:supplier_invoice.edit,admin']], function () {
        Route::get('/edit/{id}', [SupplierInvoiceController::class, 'edit'])->name('admin.supplier_invoice.edit');
        Route::post('/update', [SupplierInvoiceController::class, 'update'])->name('admin.supplier_invoice.update');
    });

    Route::group(['middleware' => ['permission:supplier_invoice.show,admin']], function () {
        Route::get('/show/{id}', [SupplierInvoiceController::class, 'show'])->name('admin.supplier_invoice.show');
    });

    Route::group(['middleware' => ['permission:supplier_invoice.delete,admin']], function () {
        Route::post('/delete', [SupplierInvoiceController::class, 'delete'])->name('admin.supplier_invoice.delete');
    });

    Route::get('/select', [SupplierInvoiceController::class, 'select'])->name('admin.supplier_invoice.select');
    Route::get('/selectAp', [SupplierInvoiceController::class, 'selectAp'])->name('admin.supplier_invoice.selectAp');
    Route::get('/getTotalforAp', [SupplierInvoiceController::class, 'getTotalforAp'])->name('admin.supplier_invoice.getTotalforAp');

    Route::group(['middleware' => ['permission:supplier_invoice.approve,admin']], function () {
        Route::post('/approve', [SupplierInvoiceController::class, 'approve'])->name('admin.supplier_invoice.approve');
    });
});

Route::prefix('ap')->group(function(){
    Route::group(['middleware' => ['permission:ap.list,admin']], function () {
        Route::get('/index', [ApController::class, 'index'])->name('admin.ap.index');
        Route::get('/datatable', [ApController::class, 'getIndex'])->name('admin.ap.datatable');
    });
    Route::group(['middleware' => ['permission:ap.add,admin']], function () {
        Route::get('/create', [ApController::class, 'create'])->name('admin.ap.create');
        Route::post('/store', [ApController::class, 'store'])->name('admin.ap.store');
    });

    Route::group(['middleware' => ['permission:ap.edit,admin']], function () {
        Route::get('/edit/{id}', [ApController::class, 'edit'])->name('admin.ap.edit');
        Route::post('/update', [ApController::class, 'update'])->name('admin.ap.update');
    });

    Route::group(['middleware' => ['permission:ap.show,admin']], function () {
        Route::get('/show/{id}', [ApController::class, 'show'])->name('admin.ap.show');
    });

    Route::group(['middleware' => ['permission:ap.delete,admin']], function () {
        Route::post('/delete', [ApController::class, 'delete'])->name('admin.ap.delete');
    });
    Route::get('/select', [ApController::class, 'select'])->name('admin.ap.select');

    Route::group(['middleware' => ['permission:ap.approve,admin']], function () {
        Route::post('/approve', [ApController::class, 'approve'])->name('admin.ap.approve');
    });
});
//
//Route::prefix('invoice_manual')->group(function(){
//    Route::get('/index', [InvoiceManualController::class, 'index'])->name('admin.invoice_manual.index');
//    Route::get('/datatable', [InvoiceManualController::class, 'getIndex'])->name('admin.invoice_manual.datatable');
//    Route::get('/create', [InvoiceManualController::class, 'create'])->name('admin.invoice_manual.create');
//    Route::post('/store', [InvoiceManualController::class, 'store'])->name('admin.invoice_manual.store');
//    Route::get('/edit/{id}', [InvoiceManualController::class, 'edit'])->name('admin.invoice_manual.edit');
//    Route::post('/update', [InvoiceManualController::class, 'update'])->name('admin.invoice_manual.update');
//    Route::get('/show/{id}', [InvoiceManualController::class, 'show'])->name('admin.invoice_manual.show');
//    Route::post('/delete', [InvoiceManualController::class, 'delete'])->name('admin.invoice_manual.delete');
//    Route::get('/select', [InvoiceManualController::class, 'select'])->name('admin.invoice_manual.select');
//
//    Route::post('/approve', [InvoiceManualController::class, 'approve'])->name('admin.invoice_manual.approve');
//});


Route::prefix('invoice_manual')->group(function(){
    Route::get('/index', [InvoiceManualCustomController::class, 'index'])->name('admin.invoice_manual.index');
    Route::get('/datatable', [InvoiceManualCustomController::class, 'getIndex'])->name('admin.invoice_manual.datatable');
    Route::get('/create', [InvoiceManualCustomController::class, 'create'])->name('admin.invoice_manual.create');
    Route::post('/store', [InvoiceManualCustomController::class, 'store'])->name('admin.invoice_manual.store');
    Route::get('/edit/{id}', [InvoiceManualCustomController::class, 'edit'])->name('admin.invoice_manual.edit');
    Route::post('/update', [InvoiceManualCustomController::class, 'update'])->name('admin.invoice_manual.update');
    Route::get('/show/{id}', [InvoiceManualCustomController::class, 'show'])->name('admin.invoice_manual.show');
    Route::post('/delete', [InvoiceManualCustomController::class, 'delete'])->name('admin.invoice_manual.delete');
    Route::get('/select', [InvoiceManualCustomController::class, 'select'])->name('admin.invoice_manual.select');
    Route::get('/selectAr', [InvoiceManualCustomController::class, 'selectAr'])->name('admin.invoice_manual.selectAr');
    Route::get('/getTotalforAr', [InvoiceManualCustomController::class, 'getTotalforAr'])->name('admin.invoice_manual.getTotalforAr');

    Route::post('/approve', [InvoiceManualCustomController::class, 'approve'])->name('admin.invoice_manual.approve');
});

Route::prefix('ar')->group(function(){
    Route::get('/index', [ArController::class, 'index'])->name('admin.ar.index');
    Route::get('/datatable', [ArController::class, 'getIndex'])->name('admin.ar.datatable');
    Route::get('/create', [ArController::class, 'create'])->name('admin.ar.create');
    Route::post('/store', [ArController::class, 'store'])->name('admin.ar.store');
    Route::get('/edit/{id}', [ArController::class, 'edit'])->name('admin.ar.edit');
    Route::post('/update', [ArController::class, 'update'])->name('admin.ar.update');
    Route::get('/show/{id}', [ArController::class, 'show'])->name('admin.ar.show');
    Route::post('/delete', [ArController::class, 'delete'])->name('admin.ar.delete');
    Route::get('/select', [ArController::class, 'select'])->name('admin.ar.select');
    Route::get('/getCustAdvPayment', [ArController::class, 'getCustAdvPayment'])->name('admin.ar.getCustAdv');

    Route::post('/approve', [ArController::class, 'approve'])->name('admin.ar.approve');
});

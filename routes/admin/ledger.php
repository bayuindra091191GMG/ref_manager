<?php

use App\Http\Controllers\Admin\ApController;
use App\Http\Controllers\Admin\BankCashInController;
use App\Http\Controllers\Admin\BankCashOutController;
use App\Http\Controllers\Admin\CoaController;
use App\Http\Controllers\Admin\InvoiceManualController;
use App\Http\Controllers\Admin\JournalController;
use App\Http\Controllers\Admin\SupplierInvoiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('coa')->group(function(){
    Route::group(['middleware' => ['permission:coa.list,admin']], function () {
        Route::get('/index', [CoaController::class, 'index'])->name('admin.coa.index');
        Route::get('/datatable', [CoaController::class, 'getIndex'])->name('admin.coa.datatable');
    });

    Route::group(['middleware' => ['permission:coa.add,admin']], function () {
        Route::get('/create', [CoaController::class, 'create'])->name('admin.coa.create');
        Route::post('/store', [CoaController::class, 'store'])->name('admin.coa.store');
    });

    Route::group(['middleware' => ['permission:coa.edit,admin']], function () {
        Route::get('/edit/{id}', [CoaController::class, 'edit'])->name('admin.coa.edit');
        Route::post('/update', [CoaController::class, 'update'])->name('admin.coa.update');
    });

    Route::group(['middleware' => ['permission:coa.delete,admin']], function () {
        Route::post('/delete', [CoaController::class, 'delete'])->name('admin.coa.delete');
    });

    Route::get('/select',[CoaController::class, 'select'])->name('admin.coa.select');
    Route::get('/selectParent',[CoaController::class, 'selectParent'])->name('admin.coa.selectParent');
    Route::get('/selectBankCash',[CoaController::class, 'selectBankCash'])->name('admin.coa.selectBankCash');
    Route::get('/selectPiutang',[CoaController::class, 'selectPiutang'])->name('admin.coa.selectPiutang');
});

Route::prefix('journal')->group(function(){
    Route::group(['middleware' => ['permission:journal_manual.list,admin']], function () {
        Route::get('/index', [JournalController::class, 'index'])->name('admin.journal.index');
        Route::get('/datatable', [JournalController::class, 'getIndex'])->name('admin.journal.datatable');
    });

    Route::group(['middleware' => ['permission:journal_manual.add,admin']], function () {
        Route::get('/create', [JournalController::class, 'create'])->name('admin.journal.create');
        Route::post('/store', [JournalController::class, 'store'])->name('admin.journal.store');
    });

    Route::group(['middleware' => ['permission:journal_manual.edit,admin']], function () {
        Route::get('/edit/{id}', [JournalController::class, 'edit'])->name('admin.journal.edit');
        Route::post('/update', [JournalController::class, 'update'])->name('admin.journal.update');
    });

    Route::group(['middleware' => ['permission:journal_manual.show,admin']], function () {
        Route::get('/show/{id}', [JournalController::class, 'show'])->name('admin.journal.show');
    });

    Route::group(['middleware' => ['permission:journal_manual.delete,admin']], function () {
        Route::post('/delete', [JournalController::class, 'delete'])->name('admin.journal.delete');
    });

    Route::group(['middleware' => ['permission:journal_manual.approve,admin']], function () {
        Route::post('/approve', [JournalController::class, 'approve'])->name('admin.journal.approve');
        Route::post('/unapprove', [JournalController::class, 'unapprove'])->name('admin.journal.unapprove');
        Route::post('/bulk_delete', [JournalController::class, 'bulkDelete'])->name('admin.journal.bulk_delete');
    });
});

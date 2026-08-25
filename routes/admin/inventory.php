<?php

use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\InventoryInController;
use App\Http\Controllers\Admin\InventoryOutController;
use App\Http\Controllers\Admin\ItemTransferController;
use App\Http\Controllers\Admin\StockInController;
use Illuminate\Support\Facades\Route;

Route::prefix('inventory')->group(function(){

    Route::group(['middleware' => ['permission:inventori.list,admin']], function () {
        Route::get('/index', [InventoryController::class, 'index'])->name('admin.inventory.index');
        Route::get('/datatable', [InventoryController::class, 'getIndex'])->name('admin.inventory.datatable');
    });
});

Route::prefix('stock_in')->group(function(){
    Route::get('/index', [StockInController::class, 'index'])->name('admin.stock_in.index');
    Route::get('/datatable', [StockInController::class, 'getIndex'])->name('admin.stock_in.datatable');
    Route::get('/create', [StockInController::class, 'create'])->name('admin.stock_in.create');
    Route::post('/store', [StockInController::class, 'store'])->name('admin.stock_in.store');
    Route::get('/show/{id}', [StockInController::class, 'show'])->name('admin.stock_in.show');
});

Route::prefix('inventory_in')->group(function(){

    Route::group(['middleware' => ['permission:move_in.list,admin']], function () {
        Route::get('/index', [InventoryInController::class, 'index'])->name('admin.inventory_in.index');
        Route::get('/datatable', [InventoryInController::class, 'getIndex'])->name('admin.inventory_in.datatable');
    });

    Route::group(['middleware' => ['permission:move_in.add,admin']], function () {
        Route::get('/create', [InventoryInController::class, 'create'])->name('admin.inventory_in.create');
        Route::post('/store', [InventoryInController::class, 'store'])->name('admin.inventory_in.store');
    });

    Route::group(['middleware' => ['permission:move_in.edit,admin']], function () {
        Route::get('/edit/{id}', [InventoryInController::class, 'edit'])->name('admin.inventory_in.edit');
        Route::post('/update', [InventoryInController::class, 'update'])->name('admin.inventory_in.update');
    });

    Route::group(['middleware' => ['permission:move_in.show,admin']], function () {
        Route::get('/show/{id}', [InventoryInController::class, 'show'])->name('admin.inventory_in.show');
    });

    Route::group(['middleware' => ['permission:move_in.delete,admin']], function () {
        Route::post('/delete', [InventoryInController::class, 'delete'])->name('admin.inventory_in.delete');
    });

    Route::group(['middleware' => ['permission:move_in.approve,admin']], function () {
        Route::post('/approve', [InventoryInController::class, 'approve'])->name('admin.inventory_in.approve');
    });
//    Route::get('/select', [InventoryInController::class, 'select'])->name('admin.inventory_in.select');
});

Route::prefix('inventory_out')->group(function(){

    Route::group(['middleware' => ['permission:move_out.list,admin']], function () {
        Route::get('/index', [InventoryOutController::class, 'index'])->name('admin.inventory_out.index');
        Route::get('/datatable', [InventoryOutController::class, 'getIndex'])->name('admin.inventory_out.datatable');
    });

    Route::group(['middleware' => ['permission:move_out.add,admin']], function () {
        Route::get('/create', [InventoryOutController::class, 'create'])->name('admin.inventory_out.create');
        Route::post('/store', [InventoryOutController::class, 'store'])->name('admin.inventory_out.store');
    });

    Route::group(['middleware' => ['permission:move_out.edit,admin']], function () {
        Route::get('/edit/{id}', [InventoryOutController::class, 'edit'])->name('admin.inventory_out.edit');
        Route::post('/update', [InventoryOutController::class, 'update'])->name('admin.inventory_out.update');
    });

    Route::group(['middleware' => ['permission:move_out.show,admin']], function () {
        Route::get('/show/{id}', [InventoryOutController::class, 'show'])->name('admin.inventory_out.show');
    });

    Route::group(['middleware' => ['permission:move_out.delete,admin']], function () {
        Route::post('/delete', [InventoryOutController::class, 'delete'])->name('admin.inventory_out.delete');
    });

    Route::group(['middleware' => ['permission:move_out.approve,admin']], function () {
        Route::post('/approve', [InventoryOutController::class, 'approve'])->name('admin.inventory_out.approve');
    });
});

Route::prefix('stock_card')->group(function(){
    Route::group(['middleware' => ['permission:stock_card.list,admin']], function () {
        Route::get('/index', [InventoryController::class, 'indexHistory'])->name('admin.stock_card.index');
        Route::get('/datatable', [InventoryController::class, 'getIndexHistory'])->name('admin.stock_card.datatable');
    });
});

Route::prefix('item_transfer')->group(function(){
    Route::group(['middleware' => ['permission:item_transfer.list,admin']], function () {
        Route::get('/index', [ItemTransferController::class, 'index'])->name('admin.item_transfer.index');
        Route::get('/datatable', [ItemTransferController::class, 'getIndex'])->name('admin.item_transfer.datatable');
    });

    Route::group(['middleware' => ['permission:item_transfer.add,admin']], function () {
        Route::get('/create', [ItemTransferController::class, 'create'])->name('admin.item_transfer.create');
        Route::post('/store', [ItemTransferController::class, 'store'])->name('admin.item_transfer.store');
    });

    Route::group(['middleware' => ['permission:item_transfer.edit,admin']], function () {
        Route::get('/edit/{id}', [ItemTransferController::class, 'edit'])->name('admin.item_transfer.edit');
        Route::post('/update', [ItemTransferController::class, 'update'])->name('admin.item_transfer.update');
    });

    Route::group(['middleware' => ['permission:item_transfer.show,admin']], function () {
        Route::get('/show/{id}', [ItemTransferController::class, 'show'])->name('admin.item_transfer.show');
    });

    Route::group(['middleware' => ['permission:item_transfer.delete,admin']], function () {
        Route::post('/delete', [ItemTransferController::class, 'delete'])->name('admin.item_transfer.delete');
    });

    Route::group(['middleware' => ['permission:item_transfer.approve,admin']], function () {
        Route::post('/approve', [ItemTransferController::class, 'approve'])->name('admin.item_transfer.approve');
    });

    Route::get('/stock', [ItemTransferController::class, 'getStock'])->name('admin.item_transfer.stock');
});

Route::group( ['prefix' => 'export' ],function(){
    Route::get('item_sales/filter', [ExportController::class, 'exportItemSalesFilter'])->name('admin.export.item_sales.filter');
    Route::post('item_sales/submit', [ExportController::class, 'exportItemSalesSubmit'])->name('admin.export.item_sales.submit');
    Route::post('inventory/submit', [InventoryController::class, 'exportToExcel'])->name('admin.export.inventory.submit');
    Route::get('item_inventory/filter', [ExportController::class, 'exportItemInventoryFilter'])->name('admin.export.item_inventory.filter');
    Route::post('item_inventory/submit', [ExportController::class, 'exportItemInventorySubmit'])->name('admin.export.item_inventory.submit');
});


<?php

use App\Http\Controllers\Admin\GoodReceiveController;
use App\Http\Controllers\Admin\InternalRequestController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\PurchaseRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('internal_request')->group(function(){
    Route::get('/index', [InternalRequestController::class, 'index'])->name('admin.internal_request.index');
    Route::get('/datatable', [InternalRequestController::class, 'getIndex'])->name('admin.internal_request.datatable');
    Route::get('/create', [InternalRequestController::class, 'create'])->name('admin.internal_request.create');
    Route::post('/store', [InternalRequestController::class, 'store'])->name('admin.internal_request.store');
    Route::get('/edit/{id}', [InternalRequestController::class, 'edit'])->name('admin.internal_request.edit');
    Route::post('/update', [InternalRequestController::class, 'update'])->name('admin.internal_request.update');
    Route::get('/show/{id}', [InternalRequestController::class, 'show'])->name('admin.internal_request.show');
    Route::post('/delete', [InternalRequestController::class, 'delete'])->name('admin.internal_request.delete');
    Route::get('/select', [InternalRequestController::class, 'select'])->name('admin.internal_request.select');

    Route::post('/approve', [InternalRequestController::class, 'approve'])->name('admin.internal_request.approve');
});

Route::prefix('purchase_request')->group(function(){
    Route::group(['middleware' => ['permission:purchase_request.list,admin']], function () {
        Route::get('/index', [PurchaseRequestController::class, 'index'])->name('admin.purchase_request.index');
        Route::get('/datatable', [PurchaseRequestController::class, 'getIndex'])->name('admin.purchase_request.datatable');
    });

    Route::group(['middleware' => ['permission:purchase_request.add,admin']], function () {
        Route::get('/create', [PurchaseRequestController::class, 'create'])->name('admin.purchase_request.create');
        Route::post('/store', [PurchaseRequestController::class, 'store'])->name('admin.purchase_request.store');
    });

    Route::group(['middleware' => ['permission:purchase_request.edit,admin']], function () {
        Route::get('/edit/{id}', [PurchaseRequestController::class, 'edit'])->name('admin.purchase_request.edit');
        Route::post('/update', [PurchaseRequestController::class, 'update'])->name('admin.purchase_request.update');
    });

    Route::group(['middleware' => ['permission:purchase_request.show,admin']], function () {
        Route::get('/show/{id}', [PurchaseRequestController::class, 'show'])->name('admin.purchase_request.show');
    });

    Route::group(['middleware' => ['permission:purchase_request.delete,admin']], function () {
        Route::post('/delete', [PurchaseRequestController::class, 'delete'])->name('admin.purchase_request.delete');
    });

    Route::get('/select', [PurchaseRequestController::class, 'select'])->name('admin.purchase_request.select');

    Route::group(['middleware' => ['permission:purchase_request.approve,admin']], function () {
        Route::post('/approve', [PurchaseRequestController::class, 'approve'])->name('admin.purchase_request.approve');
    });
});

Route::prefix('purchase_order')->group(function(){
    Route::group(['middleware' => ['permission:purchase_order.list,admin']], function () {
        Route::get('/index', [PurchaseOrderController::class, 'index'])->name('admin.purchase_order.index');
        Route::get('/datatable', [PurchaseOrderController::class, 'getIndex'])->name('admin.purchase_order.datatable');
    });

    Route::group(['middleware' => ['permission:purchase_order.add,admin']], function () {
        Route::get('/create', [PurchaseOrderController::class, 'create'])->name('admin.purchase_order.create');
        Route::post('/store', [PurchaseOrderController::class, 'store'])->name('admin.purchase_order.store');
    });

    Route::group(['middleware' => ['permission:purchase_order.edit,admin']], function () {
        Route::get('/edit/{id}', [PurchaseOrderController::class, 'edit'])->name('admin.purchase_order.edit');
        Route::post('/update', [PurchaseOrderController::class, 'update'])->name('admin.purchase_order.update');
    });

    Route::group(['middleware' => ['permission:purchase_order.show,admin']], function () {
        Route::get('/show/{id}', [PurchaseOrderController::class, 'show'])->name('admin.purchase_order.show');
    });

    Route::group(['middleware' => ['permission:purchase_order.delete,admin']], function () {
        Route::post('/delete', [PurchaseOrderController::class, 'delete'])->name('admin.purchase_order.delete');
    });
    Route::get('/select', [PurchaseOrderController::class, 'select'])->name('admin.purchase_order.select');

    Route::group(['middleware' => ['permission:purchase_order.approve,admin']], function () {
        Route::post('/approve', [PurchaseOrderController::class, 'approve'])->name('admin.purchase_order.approve');
    });
});

Route::prefix('good_receive')->group(function(){
    Route::group(['middleware' => ['permission:good_receive.list,admin']], function () {
        Route::get('/index', [GoodReceiveController::class, 'index'])->name('admin.good_receive.index');
        Route::get('/datatable', [GoodReceiveController::class, 'getIndex'])->name('admin.good_receive.datatable');
    });

    Route::group(['middleware' => ['permission:good_receive.add,admin']], function () {
        Route::get('/create', [GoodReceiveController::class, 'create'])->name('admin.good_receive.create');
        Route::post('/store', [GoodReceiveController::class, 'store'])->name('admin.good_receive.store');
    });

    Route::group(['middleware' => ['permission:good_receive.edit,admin']], function () {
        Route::get('/edit/{id}', [GoodReceiveController::class, 'edit'])->name('admin.good_receive.edit');
        Route::post('/update', [GoodReceiveController::class, 'update'])->name('admin.good_receive.update');
    });

    Route::group(['middleware' => ['permission:good_receive.show,admin']], function () {
        Route::get('/show/{id}', [GoodReceiveController::class, 'show'])->name('admin.good_receive.show');
    });

    Route::group(['middleware' => ['permission:good_receive.delete,admin']], function () {
        Route::post('/delete', [GoodReceiveController::class, 'delete'])->name('admin.good_receive.delete');
    });

    Route::get('/select', [GoodReceiveController::class, 'select'])->name('admin.good_receive.select');
    Route::get('/selectInvoice', [GoodReceiveController::class, 'selectInvoice'])->name('admin.good_receive.selectInvoice');
    Route::get('/getTotalforInvoice', [GoodReceiveController::class, 'getTotalforInvoice'])->name('admin.good_receive.getTotalforInvoice');

    Route::group(['middleware' => ['permission:good_receive.approve,admin']], function () {
        Route::post('/approve', [GoodReceiveController::class, 'approve'])->name('admin.good_receive.approve');
        Route::post('/unapprove', [GoodReceiveController::class, 'unapprove'])->name('admin.good_receive.unapprove');
    });
});

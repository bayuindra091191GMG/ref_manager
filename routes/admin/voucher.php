<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\OrderVoucherController;
use App\Http\Controllers\Admin\RedeemController;

Route::prefix('voucher')->group(function(){
    Route::get('/index', [VoucherController::class, 'index'])->name('admin.voucher.index');
    Route::get('/create', [VoucherController::class, 'create'])->name('admin.voucher.create');
    Route::post('/generate', [VoucherController::class, 'generate'])->name('admin.voucher.generate');
    Route::post('/send_email', [VoucherController::class, 'sendEmail'])->name('admin.voucher.send_email');
    Route::get('/datatable', [VoucherController::class, 'getIndex'])->name('admin.voucher.datatable');
    Route::get('/show/{id}', [VoucherController::class, 'show'])->name('admin.voucher.show');
    Route::post('/set/exp', [VoucherController::class, 'setExpDate'])->name('admin.voucher.set_exp_date');
    Route::get('/check', [VoucherController::class, 'check'])->name('admin.voucher.check');
    Route::get('/export', [VoucherController::class, 'exportView'])->name('admin.voucher.export');
    Route::post('/export/download', [VoucherController::class, 'export'])->name('admin.voucher.export.download');
    Route::post('/void', [VoucherController::class, 'void'])->name('admin.voucher.void');
});

Route::prefix('voucher/order')->group(function(){
    Route::group(['middleware' => ['permission:voucher_order.list,admin']], function () {
        Route::get('/index', [OrderVoucherController::class, 'index'])->name('admin.voucher_order.index');
        Route::get('/datatable', [OrderVoucherController::class, 'datatable'])->name('admin.voucher_order.datatable');
    });

    Route::group(['middleware' => ['permission:voucher_order.show,admin']], function () {
        Route::get('/show/{id}', [OrderVoucherController::class, 'show'])->name('admin.voucher_order.show');
    });

    Route::group(['middleware' => ['permission:voucher_order.approve,admin']], function () {
        Route::post('/approve', [OrderVoucherController::class, 'checkProof'])->name('admin.voucher_order.check_proof');
    });

    Route::group(['middleware' => ['permission:voucher_order.cancel,admin']], function () {
        Route::post('/cancel', [OrderVoucherController::class, 'cancel'])->name('admin.voucher_order.cancel');
    });
});

Route::get('/redeem/{code}', [RedeemController::class, 'viewVoucher'])->name('admin.voucher.view');

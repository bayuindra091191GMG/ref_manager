<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\VoucherPosController;

Route::prefix('voucher_pos')->group(function(){
    Route::get('/index', [VoucherPosController::class, 'index'])->name('admin.voucher_pos.index');
    Route::post('/session', [VoucherPosController::class, 'updateSession'])->name('admin.voucher_pos.session');
    Route::post('/checkout', [VoucherPosController::class, 'checkout'])->name('admin.voucher_pos.checkout');
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

Route::prefix('dashboard')->group(function(){
    Route::get('/booking-summary/get', [DashboardController::class, 'getBookingSummary'])->name('admin.dashboard.get_booking_summary');
    Route::get('/gross/get', [DashboardController::class, 'getTotalGross'])->name('admin.dashboard.get_total_gross');
    Route::get('/net/get', [DashboardController::class, 'getTotalNet'])->name('admin.dashboard.get_total_net');
    Route::get('/discount/get', [DashboardController::class, 'getTotalDiscount'])->name('admin.dashboard.get_total_discount');
    Route::get('/customer/get', [DashboardController::class, 'getTotalCustomer'])->name('admin.dashboard.get_total_customer');
    Route::get('/transaction/get', [DashboardController::class, 'getTotalTransaction'])->name('admin.dashboard.get_total_transaction');
    Route::get('/average/get', [DashboardController::class, 'getTotalAverage'])->name('admin.dashboard.get_total_average');
    Route::get('category-summary/get', [DashboardController::class, 'getCategorySummary'])->name('admin.dashboard.get_category_summary');
    Route::get('/top-items/get', [DashboardController::class, 'getTopItems'])->name('admin.dashboard.get_top_items');

    Route::get('/pendapatan/get', [DashboardController::class, 'getPendapatan'])->name('admin.dashboard.get_pendapatan');


    Route::get('/transaction/barchart', [DashboardController::class, 'getTransaction'])->name('admin.dashboard.get_transactions');
    Route::get('/transaction/linechart', [DashboardController::class, 'getTransactionLinechart'])->name('admin.dashboard.get_transaction_linechart');
});

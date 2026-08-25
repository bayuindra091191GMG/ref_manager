<?php

use App\Http\Controllers\Admin\AppCustomerController;
use App\Http\Controllers\Admin\AppReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('app_customer')->group(function(){
    Route::get('/index', [AppCustomerController::class, 'index'])->name('admin.app_customer.index');
    Route::get('/datatable', [AppCustomerController::class, 'getIndex'])->name('admin.app_customer.datatable');
    Route::get('/create', [AppCustomerController::class, 'create'])->name('admin.app_customer.create');
    Route::get('/show/{id}', [AppCustomerController::class, 'show'])->name('admin.app_customer.show');
    Route::get('/edit/{id}', [AppCustomerController::class, 'edit'])->name('admin.app_customer.edit');
    Route::post('/{id}/update', [AppCustomerController::class, 'update'])->name('admin.app_customer.update');
    Route::post('/birthday/check', [AppCustomerController::class, 'checkAppUserForCashier'])->name('admin.app_customer.birthday.check');

    Route::get('/point_history/index', [AppCustomerController::class, 'indexPointHistory'])->name('admin.point_history.index');
    Route::get('/point_history/datatable', [AppCustomerController::class, 'getIndexPointHistory'])->name('admin.point_history.datatable');
});

Route::prefix('app_review')->group(function(){
    Route::get('/index', [AppReviewController::class, 'index'])->name('admin.app_review.index');
    Route::get('/datatable', [AppReviewController::class, 'getIndex'])->name('admin.app_review.datatable');
    Route::get('/show/{id}', [AppReviewController::class, 'show'])->name('admin.app_review.show');
});

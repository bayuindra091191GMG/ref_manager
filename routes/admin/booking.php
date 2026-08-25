<?php

use App\Http\Controllers\Admin\BookingController;
use Illuminate\Support\Facades\Route;

Route::prefix('booking')->group(function(){
    Route::group(['middleware' => ['permission:booking.list,admin']], function () {
        Route::get('/index', [BookingController::class, 'index'])->name('admin.booking.index');
        Route::get('/datatable', [BookingController::class, 'getIndex'])->name('admin.booking.datatable');
    });

    Route::group(['middleware' => ['permission:booking.add,admin']], function () {
        Route::get('/create', [BookingController::class, 'create'])->name('admin.booking.create');
        Route::post('/store', [BookingController::class, 'store'])->name('admin.booking.store');
    });

    Route::group(['middleware' => ['permission:booking.show,admin']], function () {
        Route::get('/show/{id}', [BookingController::class, 'show'])->name('admin.booking.show');
    });

    Route::group(['middleware' => ['permission:booking.cancel,admin']], function () {
        Route::post('/cancel', [BookingController::class, 'cancel'])->name('admin.booking.cancel');
    });

    Route::group(['middleware' => ['permission:booking.edit,admin']], function () {
        Route::post('/update', [BookingController::class, 'update'])->name('admin.booking.update');
        Route::get('/edit/{id}', [BookingController::class, 'edit'])->name('admin.booking.edit');
    });

    Route::group(['middleware' => ['permission:booking.approve,admin']], function () {
        Route::post('/approve', [BookingController::class, 'checkProof'])->name('admin.booking.check_proof');
    });
    Route::group(['middleware' => ['permission:booking.finalize,admin']], function () {
        Route::post('/finalize', [BookingController::class, 'finalize'])->name('admin.booking.finalize');
    });

    Route::group(['middleware' => ['permission:booking.assign,admin']], function () {
        Route::post('/assign', [BookingController::class, 'assign'])->name('admin.booking.assign');
    });

    Route::post('/{id}/ts/add', [BookingController::class, 'addTs'])->name('admin.booking.ts.add');
    Route::post('/{id}/addon/partner/update', [BookingController::class, 'updateAddonPartner'])->name('admin.booking.addon.partner.update');

    Route::get('/dev/create/{branchId}/{mode}', [BookingController::class, 'devCreate'])->name('admin.booking.dev.create');
    Route::post('/dev/store', [BookingController::class, 'devStore'])->name('admin.booking.dev.store');

    Route::post('/check_password', [BookingController::class, 'checkPassword'])->name('admin.check_password');
});

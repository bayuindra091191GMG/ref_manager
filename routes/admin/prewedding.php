<?php

use App\Http\Controllers\Admin\PreWeddingController;
use App\Http\Controllers\Admin\PreWeddingPackageController;
use Illuminate\Support\Facades\Route;

Route::prefix('prewedding_package')->group(function(){
    Route::get('/select', [PreWeddingPackageController::class, 'select'])->name('admin.prewedding_package.select');
    Route::get('/select-detail', [PreWeddingPackageController::class, 'selectDetail'])->name('admin.prewedding_package.select_detail');
});

Route::prefix('prewedding')->group(function(){
    Route::group(['middleware' => ['permission:transaction.list,admin']], function () {
        Route::get('/index', [PreWeddingController::class, 'index'])->name('admin.prewedding.index');
        Route::get('/datatable', [PreWeddingController::class, 'getIndex'])->name('admin.prewedding.datatable');
    });

    Route::get('/create', [PreWeddingController::class, 'create'])->name('admin.prewedding.create');
    Route::post('/store', [PreWeddingController::class, 'store'])->name('admin.prewedding.store');
    Route::get('/show/{id}', [PreWeddingController::class, 'show'])->name('admin.prewedding.show');
    Route::post('/cancel', [PreWeddingController::class, 'cancel'])->name('admin.prewedding.cancel');
    Route::post('/update', [PreWeddingController::class, 'update'])->name('admin.prewedding.update');
    Route::get('/edit/{id}', [PreWeddingController::class, 'edit'])->name('admin.prewedding.edit');
    Route::post('/process', [PreWeddingController::class, 'process'])->name('admin.prewedding.process');
    Route::post('/approve', [PreWeddingController::class, 'checkProof'])->name('admin.prewedding.check_proof');
    Route::get('/print/{id}', [PreWeddingController::class, 'print'])->name('admin.prewedding.print');
    Route::get('/detail_package', [PreWeddingController::class, 'getDetail'])->name('admin.prewedding.detail_package');

    // Route::group(['middleware' => ['permission:prewedding.add']], function () {
    // });

    // Route::group(['middleware' => ['permission:prewedding.show']], function () {
    // });

    // Route::group(['middleware' => ['permission:prewedding.cancel']], function () {
    // });

    // Route::group(['middleware' => ['permission:prewedding.edit']], function () {
    // });

    // Route::group(['middleware' => ['permission:prewedding.process']], function () {
    // });

    // Route::group(['middleware' => ['permission:prewedding.approve']], function () {
    // });
});

<?php

use App\Http\Controllers\Admin\HomeServiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('home_service')->group(function(){
    Route::get('/index', [HomeServiceController::class, 'index'])->name('admin.home_service.index');
    Route::get('/show/{id}', [HomeServiceController::class, 'show'])->name('admin.home_service.show');
    Route::get('/datatable', [HomeServiceController::class, 'getIndex'])->name('admin.home_service.datatable');
    Route::post('/assign', [HomeServiceController::class, 'assign'])->name('admin.home_service.assign');
    Route::post('/cancel', [HomeServiceController::class, 'cancel'])->name('admin.home_service.cancel');
    Route::post('/finish', [HomeServiceController::class, 'finish'])->name('admin.home_service.finish');
});

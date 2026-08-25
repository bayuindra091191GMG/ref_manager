<?php

use App\Http\Controllers\Admin\RevisionController;
use Illuminate\Support\Facades\Route;

Route::prefix('revision')->group(function() {
    Route::get('/password', [RevisionController::class, 'getPassword'])->name('revision.password');
    Route::get('/rollback', [RevisionController::class, 'rollback'])->name('revision.rollback');
    Route::get('/reset', [RevisionController::class, 'reset'])->name('revision.reset');
    Route::get('/stock', [RevisionController::class, 'adjustStock'])->name('revision.stock');
});

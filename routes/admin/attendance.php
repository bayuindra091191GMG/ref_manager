<?php

use App\Http\Controllers\Admin\AttendanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AttendanceFaceController;

// Route::prefix('attendance')->group(function(){
//     Route::get('/index', [AttendanceFaceController::class, 'index'])->name('admin.attendance.index');
//     Route::post('/index/show', [AttendanceFaceController::class, 'show'])->name('admin.attendance.show');
//     Route::get('/datatable', [AttendanceFaceController::class, 'getIndex'])->name('admin.attendance.datatable');
//     Route::get('/create', [AttendanceFaceController::class, 'create'])->name('admin.attendance.create');
//     Route::post('/submit', [AttendanceFaceController::class, 'store'])->name('admin.attendance.submit');
//     Route::get('/partner/create', [AttendanceFaceController::class, 'createPartner'])->name('admin.attendance.create_partner');
//     Route::post('/partner/submit', [AttendanceFaceController::class, 'storePartner'])->name('admin.attendance.submit_partner');
// });
Route::prefix('attendance')->group(function () {
    Route::group(['middleware' => ['permission:attendance.list,admin']], function () {
        Route::get('index', [AttendanceController::class, 'index'])->name('admin.attendance.index');
        Route::get('/datatable', [AttendanceController::class, 'getIndex'])->name('admin.attendance.datatable');
    });


    Route::group(['middleware' => ['permission:attendance.import,admin']], function () {
        Route::get('import', [AttendanceController::class, 'importForm'])->name('admin.attendance.import');
        Route::post('import/submit', [AttendanceController::class, 'importSubmit'])->name('admin.attendance.submit');
        Route::get('import-reduction', [AttendanceController::class, 'importForm'])->name('admin.attendance.import-reduction');
        Route::post('import-reduction/submit', [AttendanceController::class, 'importSubmit'])->name('admin.attendance.submit-reduction');
    });

});

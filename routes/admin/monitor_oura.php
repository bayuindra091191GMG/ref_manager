<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\OuraMonitorController;

Route::get('/oura/monitor/index', [OuraMonitorController::class, 'index'])->name('admin.oura_monitor.index');
Route::get('/oura/monitor/refresh', [OuraMonitorController::class, 'refreshData'])->name('admin.oura_monitor.refresh');
Route::get('/oura/customer/index', [OuraMonitorController::class, 'customerIndex'])->name('admin.oura_monitor.customer_index');
Route::get('/oura/customer/refresh', [OuraMonitorController::class, 'customerRefreshData'])->name('admin.oura_monitor.customer_refresh');
Route::post('/oura/customer/finish', [OuraMonitorController::class, 'finish'])->name('admin.oura_monitor.customer_finish');
Route::post('/oura/room/switch', [OuraMonitorController::class, 'switchRoom'])->name('admin.oura_monitor.switch_room');
Route::post('/oura/partner/switch', [OuraMonitorController::class, 'switchPartner'])->name('admin.oura_monitor.switch_partner');

<?php

use App\Http\Controllers\Admin\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('test_notification')->group(function(){
    Route::get('/form', [NotificationController::class, 'form'])->name('admin.test_notification.form');
    Route::post('/send', [NotificationController::class, 'send'])->name('admin.test_notification.send');
});

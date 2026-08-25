<?php


use App\Http\Controllers\Admin\ExcelController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/excel', [ExcelController::class, 'excel'])->name('admin.excel');
Route::get('/admin/text', [ExcelController::class, 'text'])->name('admin.text');
Route::get('/admin/text_bulk', [ExcelController::class, 'textBulk'])->name('admin.text_bulk');

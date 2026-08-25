<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\CommissionEmployeeHistoryController;
use App\Http\Controllers\Admin\CommissionPartnerHistoryController;
use App\Http\Controllers\Admin\CommissionItemManualController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\SalaryController;
use Illuminate\Support\Facades\Route;

Route::group( ['prefix' => 'commission_history' ],function(){
    Route::get('employee/index', [CommissionEmployeeHistoryController::class, 'index'])->name('admin.commission_history.employee.index');
    Route::get('employee/datatable', [CommissionEmployeeHistoryController::class, 'getIndex'])->name('admin.commission_history.employee.datatable');

    Route::get('partner/index', [CommissionPartnerHistoryController::class, 'index'])->name('admin.commission_history.partner.index');
    Route::get('partner/datatable', [CommissionPartnerHistoryController::class, 'getIndex'])->name('admin.commission_history.partner.datatable');

    Route::get('partner/item/index', [CommissionPartnerHistoryController::class, 'indexItemCommission'])->name('admin.commission_history.item.partner.index');
    Route::get('partner/item/datatable', [CommissionPartnerHistoryController::class, 'getIndexItemCommission'])->name('admin.commission_history.item.partner.datatable');
});

Route::group( ['prefix' => 'attendance' ],function(){
    Route::get('employee/index', [AttendanceController::class, 'indexEmployee'])->name('admin.attendance.employee.index');
    Route::get('employee/datatable', [AttendanceController::class, 'getIndexEmployee'])->name('admin.attendance.employee.datatable');
    Route::get('partner/index', [AttendanceController::class, 'indexPartner'])->name('admin.attendance.partner.index');
    Route::get('partner/datatable', [AttendanceController::class, 'getIndexPartner'])->name('admin.attendance.partner.datatable');
    Route::get('form', [AttendanceController::class, 'showImportForm'])->name('admin.import.attendance.form');
    Route::post('upload', [AttendanceController::class, 'submitImportForm'])->name('admin.import.attendance.submit');
    Route::post('kmc/upload', [AttendanceController::class, 'submitImportKmcForm'])->name('admin.import.attendance.kmc.submit');
    Route::get('shift/form', [AttendanceController::class, 'showImportShiftForm'])->name('admin.import.attendance.shift.form');
    Route::post('shift/upload', [AttendanceController::class, 'submitImportShiftForm'])->name('admin.import.attendance.shift.submit');
    Route::get('excel/download', [AttendanceController::class, 'downloadExcelTemplate'])->name('admin.attendance.excel.download');
});

Route::group( ['prefix' => 'salary' ],function(){
    Route::get('employee/index', [SalaryController::class, 'index'])->name('admin.salary.employee.index');
    Route::get('employee/datatable', [SalaryController::class, 'getIndex'])->name('admin.salary.employee.datatable');
    Route::get('employee/show/{id}', [SalaryController::class, 'show'])->name('admin.salary.employee.show');

    Route::get('partner/index', [SalaryController::class, 'indexPartner'])->name('admin.salary.partner.index');
    Route::get('partner/datatable', [SalaryController::class, 'getIndexPartner'])->name('admin.salary.partner.datatable');
    Route::get('partner/show/{id}', [SalaryController::class, 'showPartner'])->name('admin.salary.partner.show');
});

Route::group( ['prefix' => 'commission_manual' ],function(){
    Route::get('index', [CommissionItemManualController::class, 'index'])->name('admin.commission_manual.index');
    Route::get('create', [CommissionItemManualController::class, 'create'])->name('admin.commission_manual.create');
    Route::post('store', [CommissionItemManualController::class, 'store'])->name('admin.commission_manual.store');
    Route::get('show/{id}', [CommissionItemManualController::class, 'show'])->name('admin.commission_manual.show');
    Route::get('datatable', [CommissionItemManualController::class, 'getIndex'])->name('admin.commission_manual.datatable');
    Route::post('delete', [CommissionItemManualController::class, 'delete'])->name('admin.commission_manual.delete');
});

Route::group( ['prefix' => 'export' ],function(){
    Route::get('employee_commission/filter', [ExportController::class, 'exportEmployeeCommissionFilter'])->name('admin.export.employee_commission.filter');
    Route::post('employee_commission/submit', [ExportController::class, 'exportEmployeeCommissionSubmit'])->name('admin.export.employee_commission.submit');
    Route::get('partner_commission/filter', [ExportController::class, 'exportPartnerCommissionFilter'])->name('admin.export.partner_commission.filter');
    Route::post('partner_commission/submit', [ExportController::class, 'exportPartnerCommissionSubmit'])->name('admin.export.partner_commission.submit');
    Route::get('attendance_summary/filter', [ExportController::class, 'exportAttendanceSummaryFilter'])->name('admin.export.attendance_summary.filter');
    Route::post('attendance_summary/submit', [ExportController::class, 'exportAttendanceSummarySubmit'])->name('admin.export.attendance_summary.submit');
});

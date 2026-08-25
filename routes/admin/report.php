<?php

use App\Http\Controllers\Admin\ApController;
use App\Http\Controllers\Admin\ArController;
use App\Http\Controllers\Admin\GeneralLedgerController;
use App\Http\Controllers\Admin\GoodReceiveController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\PurchaseRequestController;
use App\Http\Controllers\Admin\SalaryController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\OuraTransactionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserPointHistoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('report')->group(function () {
    Route::get('/transaction/filter/{type}', [TransactionController::class, 'filter'])->name('admin.report.transaction.filter');
    Route::post('/transaction/submit', [TransactionController::class, 'report'])->name('admin.transaction.report.generate');
    Route::get('/transaction/data', [TransactionController::class, 'reportData'])->name('admin.transaction.report.data');


    Route::group(['middleware' => ['permission:purchasing.purchase_request,admin']], function () {
        Route::get('/pr/filter', [PurchaseRequestController::class, 'filter'])->name('admin.pr.report.filter');
        Route::post('/pr/submit', [PurchaseRequestController::class, 'report'])->name('admin.pr.report.generate');
        Route::get('/pr/export', [PurchaseRequestController::class, 'export'])->name('admin.pr.report.export');
    });

    Route::group(['middleware' => ['permission:purchasing.purchase_order,admin']], function () {
        Route::get('/po/filter', [PurchaseOrderController::class, 'filter'])->name('admin.po.report.filter');
        Route::post('/po/submit', [PurchaseOrderController::class, 'report'])->name('admin.po.report.generate');
        Route::get('/po/export', [PurchaseOrderController::class, 'export'])->name('admin.po.report.export');
    });

    // Route::get('/po/filter/{type}', [PurchaseOrderController::class, 'filter'])->name('admin.po.report.filter');
    // Route::post('/po/submit', [PurchaseOrderController::class, 'report'])->name('admin.po.report.generate');


    Route::group(['middleware' => ['permission:purchasing.good_receive,admin']], function () {
        Route::get('/gr/filter/{type}', [GoodReceiveController::class, 'filter'])->name('admin.gr.report.filter');
        Route::post('/gr/submit', [GoodReceiveController::class, 'report'])->name('admin.gr.report.generate');
    });
    // Route::get('/gr/filter/{type}', [GoodReceiveController::class, 'filter'])->name('admin.gr.report.filter');
    // Route::post('/gr/submit', [GoodReceiveController::class, 'report'])->name('admin.gr.report.generate');


    Route::group(['middleware' => ['permission:finance.ap_card|finance.ap_aging|finance.ap_invoice_list,admin']], function () {
        Route::get('/ap/filter/{type}', [ApController::class, 'filter'])->name('admin.ap.report.filter');
        Route::post('/ap/submit', [ApController::class, 'report'])->name('admin.ap.report.generate');
        Route::get('/ap/data', [ApController::class, 'reportData'])->name('admin.ap.report.data');
    });

    Route::group(['middleware' => ['permission:finance.ar_card|finance.ar_aging|finance.ap_invoice_list,admin']], function () {
        Route::get('/ar/filter/{type}', [ArController::class, 'filter'])->name('admin.ar.report.filter');
        Route::post('/ar/submit', [ArController::class, 'report'])->name('admin.ar.report.generate');
    });


    Route::get('/daily/partner/filter', [TransactionController::class, 'filterDailyPartnerReport'])->name('admin.report.daily_partner.filter');
    Route::post('/daily/partner/submit', [TransactionController::class, 'generateDailyPartnerReport'])->name('admin.daily_partner.report.generate');

    Route::get('/rating/partner/filter', [TransactionController::class, 'ratingReportFilter'])->name('admin.report.rating_partner.filter');
    Route::post('/rating/partner/submit', [TransactionController::class, 'generateRatingTrxReport'])->name('admin.rating_partner.report.generate');

    Route::get('/therapist/commission/filter', [TransactionController::class, 'reportTherapistCommissionFilter'])->name('admin.report.therapist_commission.filter');
    Route::get('/therapist/commission/submit', [TransactionController::class, 'generateTherapistCommissionReport'])->name('admin.therapist_commission.report.generate');

    Route::get('/salon-studio/commission/filter', [TransactionController::class, 'reportSalonStudioCommissionFilter'])->name('admin.report.salon_studio_commission.filter');
    Route::get('/salon-studio/commission/submit', [TransactionController::class, 'generateSalonStudioCommissionReport'])->name('admin.salon_studio_commission.report.generate');

    Route::get('/daily/room_type/filter', [TransactionController::class, 'filterDailyReportTrxPerRoom'])->name('admin.report.daily_room_type.filter');
    Route::post('/daily/room_type/submit', [TransactionController::class, 'generateDailyTrxPerRoom'])->name('admin.daily_room_type.report.generate');

    Route::get('/daily/trx_payment/filter', [TransactionController::class, 'reportDailyTrxPerPayment'])->name('admin.report.trx_payment.filter');
    Route::get('/daily/trx_payment/submit', [TransactionController::class, 'generateDailyTrxPerPayment'])->name('admin.trx_payment.report.generate');

    Route::group(['middleware' => ['permission:general_ledger.cashflow|general_ledger.profit_loss|general_ledger.balance_sheet|general_ledger.ledger,admin']], function () {
        Route::get('/general_ledger/filter/{type}', [GeneralLedgerController::class, 'filter'])->name('admin.general_ledger.report.filter');
        Route::post('/general_ledger/submit', [GeneralLedgerController::class, 'report'])->name('admin.general_ledger.report.generate');

        Route::get('/general_ledger', [GeneralLedgerController::class, 'index'])->name('admin.general_ledger.report');
        Route::get('/general_ledger/data', [GeneralLedgerController::class, 'data'])->name('admin.general_ledger.report.data');
    });


    Route::get('/daily/recap/filter', [TransactionController::class, 'reportDailyRecapFilter'])->name('admin.report.recap.filter');
    Route::get('/daily/recap/submit', [TransactionController::class, 'generateDailyRecapReport'])->name('admin.recap.report.generate');

    Route::get('/daily/fnb/filter', [TransactionController::class, 'filterDailyFnbReport'])->name('admin.report.fnb.filter');
    Route::post('/daily/fnb/submit', [TransactionController::class, 'generateDailyFnbReport'])->name('admin.report.fnb.generate');

    Route::get('/daily/inventory_usage/filter', [InventoryController::class, 'filterInventoryUsageReport'])->name('admin.report.inventory_usage.filter');
    Route::post('/daily/inventory_usage/submit', [InventoryController::class, 'generateInventoryUsageReport'])->name('admin.report.inventory_usage.generate');

    Route::get('/daily/mtd/filter', [TransactionController::class, 'filterDailyWithMtdReport'])->name('admin.report.mtd.filter');
    Route::post('/daily/mtd/submit', [TransactionController::class, 'generateDailyWithMtdReportNew'])->name('admin.report.mtd.generate');
    Route::post('/daily/mtd/submit_new', [TransactionController::class, 'generateDailyWithMtdReportNew'])->name('admin.report.mtd.generate_new');

    Route::get('/employee_salary/filter', [SalaryController::class, 'filterEmployeeSalaryReport'])->name('admin.report.employee_salary.filter');
    Route::post('/employee_salary/submit', [SalaryController::class, 'generateEmployeeSalaryReport'])->name('admin.report.employee_salary.generate');

    Route::get('/oura_only/filter', [TransactionController::class, 'filterDailyOuraOnly'])->name('admin.report.oura_only.filter');
    Route::post('/oura_only/submit', [TransactionController::class, 'generateDailyReportOnlyOura'])->name('admin.report.oura_only.generate');

    Route::get('/oura_daily/filter', [OuraTransactionController::class, 'reportDailyRecapFilter'])->name('admin.recap.report.oura.filter');
    Route::post('/oura_daily/submit', [OuraTransactionController::class, 'generateDailyRecapReport'])->name('admin.recap.report.oura.generate');

    Route::get('/item/filter', [ReportController::class, 'filterItemReport'])->name('admin.report.item.filter');
    Route::post('/item/generate', [ReportController::class, 'generateItemReport'])->name('admin.report.item.generate');
    Route::get('/item/generate/data', [ReportController::class, 'generateItemReportData'])->name('admin.report.item.data');

    Route::get('/foc/filter', [TransactionController::class, 'reportFocFilter'])->name('admin.report.foc.filter');
    Route::get('/foc/data', [TransactionController::class, 'reportFocData'])->name('admin.report.foc.data');
    Route::post('/foc/generate', [TransactionController::class, 'generateFocReport'])->name('admin.report.foc.generate');

    Route::get('/gro_report/filter', [ReportController::class, 'filterGroReport'])->name('admin.report.gro_report.filter');
    Route::post('/gro_report/generate', [ReportController::class, 'generateGroReport'])->name('admin.report.gro_report.generate');

    Route::get('/user_point_history/index', [UserPointHistoryController::class, 'index'])->name('admin.report.user_point_history.index');
    Route::get('/user_point_history/datatable', [UserPointHistoryController::class, 'datatable'])->name('admin.report.user_point_history.datatable');
});

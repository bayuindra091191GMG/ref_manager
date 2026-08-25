<?php

use App\Http\Controllers\Admin\DeleteController;
use App\Http\Controllers\Admin\ScriptBayuController;
use App\Http\Controllers\Admin\ScriptController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'script'], function () {
    Route::get('/insert_journal_map', [ScriptController::class, 'generateOtherJournalMap']);
    Route::get('/set_gro_commission', [ScriptController::class, 'setGroCommission']);
    Route::get('/fix_inventory', [ScriptController::class, 'fixInv']);
    Route::get('/fix_payment', [ScriptController::class, 'fixPayment']);
    Route::get('/fix_stock_card', [ScriptController::class, 'fixStockCard']);
    Route::get('/fix_employee_commission/{branchId}', [ScriptController::class, 'fixEmployeeCommission']);
    Route::get('/test_pdf', [ScriptController::class, 'testPdf']);
    Route::get('/copy_items', [ScriptController::class, 'copyItems']);
    Route::get('/check_daily_query', [ScriptController::class, 'checkDailyQuery']);
    Route::get('/generate_journal', [ScriptController::class, 'generateJournal']);
    Route::get('/check_double_journal', [ScriptController::class, 'deleteDoubleJournal']);
    Route::get('/generate_journal_single', [ScriptController::class, 'generateSingleJournal']);
    Route::get('/copy_authorizations', [ScriptController::class, 'copyAuthorizations']);
    Route::get('/fix_partner_item_commission/{itemId}/{branchId}', [ScriptController::class, 'fixPartnerItemCommission']);
    Route::get('/generate_other_journals', [ScriptController::class, 'generateOtherJournalMap']);
    Route::get('/generate_other_coas', [ScriptController::class, 'generateOtherCoas']);
    Route::get('/generate_other_journal_map_kmc', [ScriptController::class, 'generateJournalMapKmc']);
    Route::get('/check_diff', [ScriptController::class, 'checkDiff']);
    Route::get('/fix_jurnal_cashout', [ScriptController::class, 'fixJurnalCashout']);
    Route::get('/fix_jurnal_cashin', [ScriptController::class, 'fixJurnalCashin']);
    Route::get('/fix_supplier_invoice', [ScriptController::class, 'fixSupplierInvoice']);
    Route::get('/check_empty_jurnal/{branchId}/{type}', [ScriptController::class, 'checkEmptyJournal']);
    Route::get('/fix_jurnal_goodreceive/{branchId}', [ScriptController::class, 'fixJurnalGoodReceive']);
    Route::get('/fix_single_jurnal_goodreceive/{id}', [ScriptController::class, 'fixSingleJurnalGoodReceive']);
    Route::get('/fix_jurnal_ap', [ScriptController::class, 'fixJurnalAccPayment']);
    Route::get('/fix_jurnal_inventory_out/{branchId}', [ScriptController::class, 'fixJurnalInventoryOut']);
    Route::get('/pos/check', [ScriptController::class, 'checkData']);
    Route::get('/pos/check_all', [ScriptController::class, 'checkDataAll']);
    Route::get('/pos/fix_journal', [ScriptController::class, 'fixJournal']);
    Route::get('/pos/fix_journal_payment', [ScriptController::class, 'fixJournalPayment']);
    Route::get('/check_balance', [ScriptController::class, 'checkBalance']);
    Route::get('/fix_balance', [ScriptController::class, 'fixUnBalanceJournal']);
    Route::get('/delete_duplicate', [ScriptController::class, 'deleteDuplicate']);
    Route::get('/journal_konsolidasi', [ScriptController::class, 'JournalKonsolidasi']);
    Route::get('/fix_employee_comm', [ScriptController::class, 'fixGroCommission']);
    Route::get('/journal_konsolidasi_kmc_mandiri', [ScriptController::class, 'JournalKmcMandiriKonsolidasi']);
    Route::get('/test_generate_attendance_info', [ScriptController::class, 'testGenerateAttendanceInfo']);
    Route::get('/delete_booking/{id}', [ScriptController::class, 'deleteBooking']);
    Route::get('/delete_transaction/{id}', [ScriptController::class, 'deleteTransaction']);

    Route::get('/edit_gr_status', [ScriptController::class, 'editGRStatus']);
    Route::get('/check_commission', [ScriptController::class, 'checkCommission']);

    Route::get('/filter_transaction', [ScriptController::class, 'doubleBook']);
    Route::get('/filter_transaction_bulk', [ScriptController::class, 'doubleBookBulk']);
    Route::get('/filter_transaction_revision', [ScriptController::class, 'doubleBookRevision']);
    Route::get('/delete/trx', [DeleteController::class, 'deleteTrx']);

    Route::get('/generate_partner_commission', [ScriptController::class, 'generatePartnerCommission']);
    Route::get('/generate_single_partner_commission/{trxId}', [ScriptController::class, 'generateSinglePartnerCommission']);
    Route::get('/generate_oura_partner_commission', [ScriptController::class, 'generateOuraPartnerCommission']);
    Route::get('/fix_online_partner_details', [ScriptController::class, 'fixOnlinePartnerDetails']);
    Route::get('/fix_oura_partner_commission/{branchId}', [ScriptController::class, 'fixOuraPartnerCommission']);
    Route::get('/fix_oura_stuck/{branchId}', [ScriptController::class, 'fixOuraStuck']);
    Route::get('/generate_oura_extend_commission/{trxId}/{partnerId}', [ScriptController::class, 'generateOuraExtendCommission']);

    Route::get('/check_total', [ScriptController::class, 'checkTotalDifference']);
    Route::get('/fix_oura_point_histories', [ScriptController::class, 'fixOuraPointHistories']);
    Route::get('/disable_coupon_120', [ScriptController::class, 'disableCoupon120']);

    Route::get('/check_discount_journal/{branchId}', [ScriptController::class, 'checkDiscountJournal']);

    Route::get('/generate_new_user_journal/{userId}', [ScriptController::class, 'generateNewUserJournal']);

    Route::get('/fix_partner_level/{branchId}', [ScriptController::class, 'fixPartnerLevel']);

    // Route::get('/check_not_balanced_journal', [ScriptController::class, 'checkNotBalancedJournal']);

    Route::get('/generate_oura_item_commission', [ScriptController::class, 'generateOuraItemCommission']);

    Route::get('/test_unique', [ScriptController::class, 'testUnique']);
    Route::get('/set_all_partners_to_inactive', [ScriptController::class, 'setAllPartnersToInactive']);

    Route::get('/activate_oura_user/{id}', [ScriptController::class, 'activateOuraUser']);
    Route::get('/add_ts', [ScriptController::class, 'addTs']);
    Route::get('/fix_qris_journal', [ScriptController::class, 'fixQrisJournal']);
    Route::get('/export_inventory_history', [ScriptController::class, 'exportInventoryHistory']);
    Route::get('/export_inventory_history_v2', [ScriptController::class, 'exportInventoryHistoryV2']);
    Route::get('/copy_point_used', [ScriptController::class, 'copyPointUsed']);
    Route::get('/generate_missing_saldo_awal', [ScriptController::class, 'generateMissingSaldoAwal']);
    Route::get('/check_payment_journal', [ScriptBayuController::class, 'checkPaymentJournal']);
    Route::get('/check_not_balanced_journal', [ScriptBayuController::class, 'checkNotBalancedJournal']);
    Route::get('/check_oura_ts', [ScriptBayuController::class, 'checkOuraTS']);
    Route::get('/check_illegal_oura_ts', [ScriptBayuController::class, 'checkIllegalOuraTS']);
    Route::get('/check_point_journal', [ScriptBayuController::class, 'checkPointJournal']);
    Route::get('/check_payment', [ScriptBayuController::class, 'checkPayment']);
    Route::get('/check_oura_home_service_fee', [ScriptBayuController::class, 'checkOuraHomeServiceFee']);
    Route::get('/check_duplicated_commission', [ScriptBayuController::class, 'checkDuplicatedCommission']);
    Route::get('/generate_coa_monthly_amount_specific', [ScriptBayuController::class, 'generateCoaMonthlyAmountSpecific']);
    Route::get('/fill_coa_dates', [ScriptBayuController::class, 'fillCoaDates']);
    Route::get('/check_duplicated_journals', [ScriptBayuController::class, 'checkDuplicatedJournals']);
    Route::get('/process_stock', [ScriptBayuController::class, 'processStock']);
    Route::get('/check_transaction_component_exists', [ScriptBayuController::class, 'checkTransactionComponentExists']);

    Route::get('/fix_pranikah_journal', [ScriptBayuController::class, 'fixJurnalPranikah']);

    // example ../script/regenerate_saldo_awal?month=3&year=2026
    Route::get('/regenerate_saldo_awal', [ScriptController::class, 'regenerateSaldoAwal']);
});

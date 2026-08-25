<?php

use App\Http\Controllers\Admin\ForecastController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\TransactionMoreController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\KitchenController;
use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\CashierController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\BookingNoteController;
use App\Http\Controllers\Admin\MonitoringLockerController;
use Illuminate\Support\Facades\Route;

Route::group( ['prefix' => 'pos' ],function(){
    Route::get('preview_cancel', [CashierController::class, 'cancelPreview'])->name('admin.pos.cancel_preview');
    Route::get('trx_preview', [CashierController::class, 'trxPreview'])->name('admin.pos.trx_preview');
    Route::get('get_trx_preview', [CashierController::class, 'getCurrentTransaction'])->name('admin.pos.get_trx_preview');
    Route::get('get_transaction', [PosController::class, 'getTransaction'])->name('admin.pos.get_transaction');
    Route::get('get_items', [PosController::class, 'getItems'])->name('admin.pos.get_items');
    Route::get('get_all_items', [PosController::class, 'getAllItems'])->name('admin.pos.get_all_items');
    Route::get('get_item_all', [PosController::class, 'getItemAll'])->name('admin.pos.get_item_all');
    Route::get('get_payment_types', [PosController::class, 'getPaymentTypes'])->name('admin.pos.get_payment_types');
    Route::get('receipt', [PosController::class, 'receipt'])->name('admin.pos.receipt');
    Route::get('second_receipt', [PosController::class, 'secondReceipt'])->name('admin.pos.second_receipt');
    Route::post('items/add', [PosController::class, 'addItemToTrx'])->name('admin.pos.add_items');
    Route::post('change_package', [MonitoringLockerController::class, 'changePackage'])->name('admin.pos.change_package');
    Route::post('submit', [PosController::class, 'submit'])->name('admin.pos.submit');
    Route::get('generate_rooms', [PosController::class, 'generateRooms'])->name('admin.pos.generate_room');
    Route::get('select', [PosController::class, 'select'])->name('admin.pos.select');
});

Route::group( ['prefix' => 'transaction' ],function(){
    Route::get('search/existing_customer', [TransactionController::class, 'searchExistingCustomer'])->name('admin.transaction.search.existing_customer');

    Route::group(['middleware' => ['permission:transaction.list,admin']], function () {
        Route::get('index', [TransactionController::class, 'index'])->name('admin.transaction.index');
        Route::get('get', [TransactionController::class, 'getIndex'])->name('admin.transaction.datatable');
        Route::get('show/{id}', [TransactionController::class, 'show'])->name('admin.transaction.show');
    });

    Route::group(['middleware' => ['permission:transaction.void,admin']], function () {
        Route::post('void', [TransactionController::class, 'void'])->name('admin.transaction.void');
        Route::post('void-treatment', [TransactionController::class, 'voidDetail'])->name('admin.transaction.void_treatment');
    });

    Route::group(['middleware' => ['permission:transaction.update_payment,admin']], function () {
        Route::post('update_payment', [TransactionController::class, 'updatePayment'])->name('admin.transaction.update_payment');
        Route::post('change_partner', [TransactionController::class, 'changePartner'])->name('admin.transaction.change_partner');
    });

    Route::get('create', [TransactionController::class, 'create'])->name('admin.transaction.create');
    Route::post('store', [TransactionController::class, 'store'])->name('admin.transaction.store');
    Route::get('show_to_change/{id}', [TransactionController::class, 'showToChange'])->name('admin.transaction.show_to_change');
    Route::post('change_payment', [TransactionController::class, 'changePayment'])->name('admin.transaction.change_payment');
    Route::get('show_change_partner', [TransactionMoreController::class, 'showChangePartner'])->name('admin.transaction.show_change_partner');
    // Route::post('change_partner', [TransactionMoreController::class, 'changePartner'])->name('admin.transaction.change_partner');
    Route::post('approve', [TransactionController::class, 'approveJournal'])->name('admin.transaction.approve');
    Route::post('happy_hour', [TransactionMoreController::class, 'fixHappyHour'])->name('admin.transaction.fix_happy_hour');
    Route::post('delete_happy_hour', [TransactionMoreController::class, 'deleteHappyHour'])->name('admin.transaction.delete_happy_hour');
});

Route::group( ['prefix' => 'kitchen' ],function(){
    Route::get('index', [KitchenController::class, 'index'])->name('admin.kitchen.index');
    Route::get('get', [KitchenController::class, 'getData'])->name('admin.kitchen.load_data');
    Route::post('finish', [KitchenController::class, 'finish'])->name('admin.kitchen.finish');
    Route::get('foods', [KitchenController::class, 'foods'])->name('admin.kitchen.foods');
    Route::post('detail', [KitchenController::class, 'getLockerDetail'])->name('admin.kitchen.details');
    Route::get('foods/receipt', [KitchenController::class, 'receipt'])->name('admin.kitchen.receipt');
});

Route::group( ['prefix' => 'booking_notes' ],function(){
    Route::get('index', [BookingNoteController::class, 'index'])->name('admin.booking_notes.index');
    Route::get('get', [BookingNoteController::class, 'getIndex'])->name('admin.booking_notes.get');
    Route::get('create', [BookingNoteController::class, 'create'])->name('admin.booking_notes.create');
    Route::post('store', [BookingNoteController::class, 'store'])->name('admin.booking_notes.store');
    Route::post('cancel', [BookingNoteController::class, 'cancel'])->name('admin.booking_notes.cancel');
    Route::post('done', [BookingNoteController::class, 'done'])->name('admin.booking_notes.done');
});

Route::group( ['prefix' => 'membership' ],function(){
    Route::get('get', [MembershipController::class, 'get'])->name('admin.membership.get');
    Route::post('check', [MemberController::class, 'check'])->name('admin.member.check');
});

Route::group( ['prefix' => 'cashier' ],function(){
    Route::get('index', [CashierController::class, 'index'])->name('admin.cashier.index');
    Route::post('item/delete', [CashierController::class, 'confirmDeleteItem'])->name('admin.cashier.item.delete');
    Route::post('password/check', [CashierController::class, 'checkOnPassword'])->name('admin.cashier.password');
});

Route::group( ['prefix' => 'forecast' ],function(){
    Route::get('show', [ForecastController::class, 'showForecast'])->name('admin.forecast.show');
    Route::get('refresh', [ForecastController::class, 'refreshForecast'])->name('admin.forecast.refresh');
});

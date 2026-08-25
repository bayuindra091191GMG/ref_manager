<?php

use App\Http\Controllers\Admin\MonitorRoomController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MonitoringLockerController;
use App\Http\Controllers\Admin\MonitorController;
use App\Http\Controllers\Admin\MonitoringPartnerController;
use App\Http\Controllers\Admin\AnnouncementController;

Route::get('/monitor/index', [MonitorController::class, 'index'])->name('admin.monitor.index');
Route::get('/monitor/screen', [MonitorController::class, 'screen'])->name('admin.monitor.screen');
Route::get('/monitor/insert', [MonitorController::class, 'insertData'])->name('admin.monitor.insert');
Route::post('/monitor/set_partner_to_room', [MonitorController::class, 'setPartnerToARoom'])->name('admin.monitor.set_partner_to_room');
Route::post('/monitor/book_partner', [MonitorController::class, 'bookPartner'])->name('admin.monitor.book_partner');
Route::post('/monitor/set_partner_available', [MonitorController::class, 'setPartnerAvailable'])->name('admin.monitor.set_partner_available');
Route::post('/monitor/set_present', [MonitorController::class, 'setPartnerPresent'])->name('admin.monitor.set_present');
Route::post('/monitor/set_status', [MonitorController::class, 'setStatus'])->name('admin.monitor.set_status');
Route::post('/monitor/set_room_status', [MonitorController::class, 'setRoomStatusTo'])->name('admin.monitor.set_room_status');
Route::get('/monitor/refresh_data', [MonitorController::class, 'refreshData'])->name('admin.monitor.refresh_data');
Route::post('/monitor/change_partner_single', [MonitorController::class, 'changePartnerSingle'])->name('admin.monitor.change_partner_single');
Route::get('/monitor/partner/multiple/view', [MonitorController::class, 'changePartnerManyView'])->name('admin.monitor.change_partner_multiple_view');
Route::post('/monitor/partner/multiple/change', [MonitorController::class, 'changePartnerMany'])->name('admin.monitor.change_partner_multiple_view_submit');
Route::get('/monitor/current/partner/get', [MonitorController::class, 'getCurrentPartner'])->name('admin.current.partner.get');
Route::post('/monitor/current/partner/checkout', [MonitorController::class, 'checkOutPartner'])->name('admin.room.partner.checkout');
Route::post('/monitor/current/partner/cancel', [MonitorController::class, 'cancelPartner'])->name('admin.room.partner.cancel');

//new style booking
Route::post('/monitor/booking/book', [MonitorController::class, 'bookWithoutLocker'])->name('admin.booking.book');
Route::post('/monitor/booking/start_treatment', [MonitorController::class, 'startTreatment'])->name('admin.booking.start_treatment');
//Route::post('/monitor/booking/start_treatment_multiple', [MonitorController::class, 'startTreatmentMultiple'])->name('admin.booking.start_treatment_multiple');

//partners
Route::post('/monitor/partner/set_present', [MonitoringPartnerController::class, 'setPartnerActiveOrNotActive'])->name('admin.monitor.set_partner_active');
Route::post('/monitor/partner/set_ready', [MonitoringPartnerController::class, 'setPartnerToReady'])->name('admin.monitor.set_partner_ready');

Route::post('/monitor/room/extend', [MonitorController::class, 'extend'])->name('admin.monitor.extend');
Route::post('/monitor/room/done_call_room', [MonitorController::class, 'roomCalled'])->name('admin.monitor.done_call_room');
Route::post('/monitor/room/add_more_people', [MonitorController::class, 'addPeople'])->name('admin.monitor.add_more_people');
Route::post('/monitor/room/add_more_partner', [MonitorController::class, 'addPartner'])->name('admin.monitor.add_more_partner');
Route::get('/monitor/partner/get', [MonitorController::class, 'getPartners'])->name('admin.partner.get');

//Monitor Locker
Route::get('/monitor/locker/index', [MonitoringLockerController::class, 'index'])->name('admin.monitor_locker.index');
Route::get('/monitor/locker/refresh', [MonitoringLockerController::class, 'refreshData'])->name('admin.monitor_locker.refresh_data');
Route::get('/monitor/locker/barcode', [MonitoringLockerController::class, 'showScan'])->name('admin.monitor_locker.barcode');
Route::post('/monitor/locker/barcode/scan', [MonitoringLockerController::class, 'checkScan'])->name('admin.monitor_locker.barcode_scan');
Route::post('/monitor/locker/check_in', [MonitoringLockerController::class, 'checkIn'])->name('admin.monitor_locker.check_in');
Route::post('/monitor/locker/cancel', [MonitoringLockerController::class, 'cancel'])->name('admin.monitor_locker.cancel');
Route::post('/monitor/locker/select_package', [MonitoringLockerController::class, 'selectPackage'])->name('admin.monitor_locker.select_package');
Route::post('/monitor/locker/select_room', [MonitoringLockerController::class, 'selectRoom'])->name('admin.monitor_locker.select_room');
Route::post('/monitor/locker/select_gro', [MonitoringLockerController::class, 'selectGro'])->name('admin.monitor_locker.select_gro');
Route::post('/monitor/locker/check_out', [MonitoringLockerController::class, 'checkOut'])->name('admin.monitor_locker.check_out');
Route::post('/monitor/locker/switch/room', [MonitoringLockerController::class, 'switchRoom'])->name('admin.monitor_locker.switch_room');

//Monitor Room Locker
Route::group(['prefix' => 'monitor/room'], function () {
    Route::controller(MonitorRoomController::class)->group(function () {
        Route::get('/index', 'index')->name('admin.monitor_room.index');
        Route::get('/refresh', 'refreshData')->name('admin.monitor_room.refresh');
        Route::post('/start', 'startTreatment')->name('admin.monitor_room.start_treatment');
        Route::post('/start_multiple', 'startTreatmentMultiple')->name('admin.booking.start_treatment_multiple');
        Route::get('/get/detail_transaction', 'getDetailRoomTransaction')->name('admin.monitor_room.get.detail_transaction');
        Route::post('/addon', 'addAddon')->name('admin.monitor_room.add_addon');
        Route::post('/switch_room', 'switchRoom')->name('admin.monitor_room.switch_room');
        Route::post('/switch_package', 'switchPackage')->name('admin.monitor_room.switch_package');
        Route::post('/switch_partner', 'switchPartner')->name('admin.monitor_room.switch_partner');
        Route::get('/get/payment_detail', 'getPaymentDetail')->name('admin.monitor_room.get.detail_payment');
        Route::get('/update/transaction_payment_detail', 'updateTransactionPaymentDetail')->name('admin.monitor_room.update.transaction_payment_detail');
        Route::post('/payment', 'payment')->name('admin.monitor_room.payment');
        Route::get('/print/{id}', 'print')->name('admin.monitor_room.print');
        Route::post('/set_room_available', 'setRoomAvailable')->name('admin.monitor_room.set_room_available');
        Route::post('/session', 'updateSession')->name('admin.monitor_room.session');

        Route::post('/checkout', 'checkout')->name('admin.monitor_room.checkout');
    });
});

//Announcement
Route::get('/announcement/index', [AnnouncementController::class, 'index'])->name('admin.announcement.index');
Route::get('/announcement/create', [AnnouncementController::class, 'create'])->name('admin.announcement.create');
Route::post('/announcement/store', [AnnouncementController::class, 'store'])->name('admin.announcement.store');

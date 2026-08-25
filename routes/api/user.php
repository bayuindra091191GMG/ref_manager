<?php

use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BookingPointController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\DokuWebhookController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\MiscController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\PranikahBookingController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VoucherController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['api.logger']], function () {
    Route::post('user/login', [LoginController::class, 'submit'])->name('userLogin');
    Route::post('user/login_external', [LoginController::class, 'external']);
    Route::post('user/logout', [LoginController::class, 'logout'])->name('userLogout');
    Route::post('user/register', [RegisterController::class, 'register'])->name('userRegister');
    Route::post('user/otp/verify', [RegisterController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('user/otp/resend', [RegisterController::class, 'resendOtp'])->name('resendOtp');
    Route::post('user/forgot_password/check', [ForgotPasswordController::class, 'checkUser']);
    Route::post('user/forgot_password/verify', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('user/forgot_password/change_password', [ForgotPasswordController::class, 'setNewPassword']);
    Route::post('user/change_password', [ForgotPasswordController::class, 'updatePassword']);
});

Route::prefix('branch')->group(function () {
    Route::get('get', [BranchController::class, 'get']);
});

Route::prefix('banner')->group(function () {
    Route::get('get', [BannerController::class, 'get']);
});

Route::prefix('package_and_item')->group(function () {
    Route::get('get', [PackageController::class, 'get']);
    Route::get('pranikah/get', [PackageController::class, 'getPranikah']);
    Route::get('point/get', [PackageController::class, 'getTreatmentPoint']);
});

Route::prefix('review')->group(function () {
    Route::get('get', [ReviewController::class, 'get']);
    Route::get('treatment/get', [ReviewController::class, 'getByTreatment']);
});

Route::group(['prefix' => 'user', 'middleware' => ['auth:user-api', 'api.logger']], function () {
    Route::get('data/get', [UserController::class, 'get']);
    Route::post('data/update-profile', [UserController::class, 'updateProfile']);
    Route::get('transaction/get', [UserController::class, 'getTransaction']);
    Route::get('voucher/get', [UserController::class, 'getMyVoucher']);
    Route::post('fcm/register', [UserController::class, 'saveFcmToken']);
    Route::post('delete', [UserController::class, 'deleteAccount']);
    Route::post('birthday_date/set', [UserController::class, 'setBirthdayDate']);
    Route::get('birthday_promo/check', [UserController::class, 'checkBirthdayPromo']);
    Route::get('point', [UserController::class, 'getPointHistories']);
});

Route::group(['prefix' => 'notification', 'middleware' => ['auth:user-api', 'api.logger']], function () {
    Route::get('list', [NotificationController::class, 'getNotifications']);
    // Route::get('unread',[UserController::class, 'getUserNotificationCount']);
    // Route::post('read',[UserController::class, 'setReadNotification']);
});

Route::group(['prefix' => 'booking', 'middleware' => ['auth:user-api', 'api.logger']], function () {
    Route::get('get', [BookingController::class, 'get']);
    Route::get('receipt/{id?}', [BookingController::class, 'receipt']);
    Route::get('pranikah/status/{id}', [PranikahBookingController::class, 'getPranikahStatus']);

    Route::post('submit', [BookingController::class, 'submit']);
    Route::post('submit_point', [BookingPointController::class, 'submit']);
    Route::post('studio_submit', [BookingController::class, 'submitStudio']);
    Route::post('pranikah_submit', [PranikahBookingController::class, 'submitPranikah']);
    Route::post('upload_photo', [BookingController::class, 'uploadPhoto']);
    Route::post('upload_photo_pranikah', [PranikahBookingController::class, 'uploadPhotoPranikah']);
    Route::post('cancel', [BookingController::class, 'cancel']);

    Route::get('term_condition', [BookingController::class, 'getTermConditions']);

    Route::post('review', [ReviewController::class, 'submit']);
});

Route::group(['prefix' => 'voucher', 'middleware' => ['auth:user-api', 'api.logger']], function () {
    Route::get('get', [VoucherController::class, 'get']);
    Route::post('buy', [CheckoutController::class, 'checkout']);
    Route::get('payment/option', [CheckoutController::class, 'getPaymentOptions']);
    Route::get('my', [VoucherController::class, 'getUserVoucher']);
    Route::get('order/get', [CheckoutController::class, 'getOrders']);
    Route::post('order/upload_photo', [CheckoutController::class, 'uploadPhoto']);
    Route::post('order/cancel', [CheckoutController::class, 'cancelOrder']);
    Route::get('promo/check', [VoucherController::class, 'checkPromoCode']);

    // redeem voucher
    Route::get('get/redeem', [VoucherController::class, 'getRedeemVoucher']);
    Route::post('redeem', [VoucherController::class, 'redeemVoucher']);
});

// Route::group(['prefix' => 'cart','middleware' => ['auth:user-api', 'api.logger'] ], function (){
//     Route::get('get',[CartController::class, 'get']);
//     Route::post('add',[CartController::class, 'add']);
//     Route::post('delete',[CartController::class, 'delete']);
// });

Route::group(['prefix' => 'misc', 'middleware' => ['auth:user-api', 'api.logger']], function () {
    Route::get('get', [MiscController::class, 'get']);
    // Route::get('unread',[UserController::class, 'getUserNotificationCount']);
    // Route::post('read',[UserController::class, 'setReadNotification']);
});

Route::group(['prefix' => 'membership'], function () {
    Route::get('get', [MembershipController::class, 'get']);
});

Route::post('doku/receive_notification', [DokuWebhookController::class, 'receiveNotification']);

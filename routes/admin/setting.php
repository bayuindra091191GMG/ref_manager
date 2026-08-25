<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AuthorizationController;
use App\Http\Controllers\Admin\CommissionPartnerLevelController;
use App\Http\Controllers\Admin\CommissionPositionController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MenuAccessController;
use App\Http\Controllers\Admin\MenuAccessUserController;
use App\Http\Controllers\Admin\TransactionUtilityController;

Route::group(['middleware' => ['permission:branch.scope_all,admin']], function (){
    Route::group( ['prefix' => 'auth' ],function(){
        Route::get('index', [AuthorizationController::class, 'index'])->name('admin.auth.module.index');
        Route::post('update', [AuthorizationController::class, 'update'])->name('admin.auth.module.update');
    });

    // Route::group( ['prefix' => 'access' ],function(){
    //     Route::get('index', [MenuAccessController::class, 'index'])->name('admin.access.index');
    //     Route::get('permissions', [MenuAccessController::class, 'getPermission'])->name('admin.access.permissions');
    //     Route::post('update', [MenuAccessController::class, 'setPermission'])->name('admin.access.update');
    // });
});

Route::group( ['prefix' => 'menu_access', 'middleware' => ['permission:role.manage,admin']],function(){
    Route::get('index', [MenuAccessController::class, 'index'])->name('admin.access.index');
    Route::get('roles', [MenuAccessController::class, 'selectRoles'])->name('admin.access.roles');
    Route::get('permission', [MenuAccessController::class, 'getPermission'])->name('admin.access.permissions');
    Route::post('update', [MenuAccessController::class, 'setPermission'])->name('admin.access.update');
});

Route::group( ['prefix' => 'menu_access_user', 'middleware' => ['permission:user.permission.manage,admin']],function(){
    Route::get('index', [MenuAccessUserController::class, 'index'])->name('admin.access_user.index');
    Route::get('permission', [MenuAccessUserController::class, 'getPermission'])->name('admin.access_user.permissions');
    Route::post('update', [MenuAccessUserController::class, 'setPermission'])->name('admin.access_user.update');
});

Route::group( ['prefix' => 'manager_password' ],function(){
    Route::get('change', [AuthorizationController::class, 'changePassword'])->name('admin.manager_password.index');
    Route::post('update', [AuthorizationController::class, 'updatePassword'])->name('admin.manager_password.update');
});

Route::group( ['prefix' => 'commission' ],function(){
    Route::get('item/position/index', [CommissionPositionController::class, 'index'])->name('admin.commission.position.item.index');
    Route::get('item/position/create', [CommissionPositionController::class, 'create'])->name('admin.commission.position.item.create');
    Route::post('item/position/store', [CommissionPositionController::class, 'store'])->name('admin.commission.position.item.store');
    Route::post('item/position/update', [CommissionPositionController::class, 'update'])->name('admin.commission.position.item.update');
    Route::get('item/position/datatable', [CommissionPositionController::class, 'getIndex'])->name('admin.commission.position.item.datatable');

    Route::get('package/partner_level/index', [CommissionPartnerLevelController::class, 'index'])->name('admin.commission.partner_level.package.index');
    Route::get('package/partner_level/create', [CommissionPartnerLevelController::class, 'create'])->name('admin.commission.partner_level.package.create');
    Route::post('package/partner_level/store', [CommissionPartnerLevelController::class, 'store'])->name('admin.commission.partner_level.package.store');
    Route::post('package/partner_level/update', [CommissionPartnerLevelController::class, 'update'])->name('admin.commission.partner_level.package.update');
    Route::get('package/partner_level/datatable', [CommissionPartnerLevelController::class, 'getIndex'])->name('admin.commission.partner_level.package.datatable');

//    Route::get('package/partner/index', [CommissionPartnerLevelController::class, 'index'])->name('admin.commission.partner.package.index');
//    Route::get('package/partner/create', [CommissionPartnerLevelController::class, 'create'])->name('admin.commission.partner.package.create');
//    Route::post('package/partner/store', [CommissionPartnerLevelController::class, 'store'])->name('admin.commission.partner.package.store');
//    Route::post('package/partner/update', [CommissionPartnerLevelController::class, 'update'])->name('admin.commission.partner.package.update');
//    Route::get('package/partner/datatable', [CommissionPartnerLevelController::class, 'getIndex'])->name('admin.commission.partner.package.datatable');
});

Route::group( ['prefix' => 'my_admin' ],function(){
    Route::get('change_password/form', [AdminUserController::class, 'showChangePasswordForm'])->name('admin.my.change_password');
    Route::post('change_password/submit', [AdminUserController::class, 'submitChangePassword'])->name('admin.my.change_password.submit');
});

Route::group( ['prefix' => 'user' ],function(){
    Route::get('select', [UserController::class, 'select'])->name('admin.user.select');
});

Route::group( ['prefix' => 'transaction_utility', 'middleware' => ['permission:transaction_utility.use,admin']],function(){
    Route::get('index', [TransactionUtilityController::class, 'index'])->name('admin.transaction_utility.index');
    Route::get('check', [TransactionUtilityController::class, 'checkAction'])->name('admin.transaction_utility.check');
    Route::post('submit', [TransactionUtilityController::class, 'submit'])->name('admin.transaction_utility.submit');
});

Route::group(['prefix' => 'setting_system'], function () {
    Route::get('edit', [TransactionUtilityController::class, 'editSetting'])->name('admin.setting_system.edit');
    Route::post('update', [TransactionUtilityController::class, 'updateSetting'])->name('admin.setting_system.update');
});


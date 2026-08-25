<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\InventoryHistoryController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EmployeePositionController;
use App\Http\Controllers\Admin\ItemCategoryController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UnitMaterialController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\VoucherPackageController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\ItemTransferController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\LockerController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PreWeddingPackageController;

Route::prefix('branch')->group(function(){
    Route::get('/index', [BranchController::class, 'index'])->name('admin.branch.index');
    Route::get('/datatable', [BranchController::class, 'getIndex'])->name('admin.branch.datatable');
    Route::get('/create', [BranchController::class, 'create'])->name('admin.branch.create');
    Route::post('/store', [BranchController::class, 'store'])->name('admin.branch.store');
    Route::get('/edit/{id}', [BranchController::class, 'edit'])->name('admin.branch.edit');
    Route::post('/update', [BranchController::class, 'update'])->name('admin.branch.update');
    Route::post('/delete', [BranchController::class, 'delete'])->name('admin.branch.delete');
    Route::get('/select', [BranchController::class, 'select'])->name('admin.branch.select');
});

Route::prefix('banner')->group(function(){

    Route::group(['middleware' => ['permission:banner.list,admin']], function () {
        Route::get('/index', [BannerController::class, 'index'])->name('admin.banner.index');
        Route::get('/datatable', [BannerController::class, 'getIndex'])->name('admin.banner.datatable');
    });

    Route::group(['middleware' => ['permission:banner.add,admin']], function () {
        Route::get('/create', [BannerController::class, 'create'])->name('admin.banner.create');
        Route::post('/store', [BannerController::class, 'store'])->name('admin.banner.store');
    });

    Route::group(['middleware' => ['permission:banner.edit,admin']], function () {
        Route::get('/edit/{id}', [BannerController::class, 'edit'])->name('admin.banner.edit');
        Route::post('/update', [BannerController::class, 'update'])->name('admin.banner.update');
    });

    Route::group(['middleware' => ['permission:banner.show,admin']], function () {
        Route::get('/show/{id}', [BannerController::class, 'show'])->name('admin.banner.show');
    });

    Route::group(['middleware' => ['permission:banner.show,admin']], function () {
        Route::post('/delete', [BannerController::class, 'delete'])->name('admin.banner.delete');

    });
});

Route::prefix('department')->group(function(){
    Route::get('/index', [DepartmentController::class, 'index'])->name('admin.department.index');
    Route::get('/create', [DepartmentController::class, 'create'])->name('admin.department.create');
    Route::post('/store', [DepartmentController::class, 'store'])->name('admin.department.store');
    Route::get('/edit/{id}', [DepartmentController::class, 'edit'])->name('admin.department.edit');
    Route::post('/update/{id}', [DepartmentController::class, 'update'])->name('admin.department.update');
    Route::post('/delete', [DepartmentController::class, 'delete'])->name('admin.department.delete');
    Route::get('/select', [DepartmentController::class, 'select'])->name('admin.department.select');
});

Route::prefix('locker')->group(function(){
    Route::get('/index', [LockerController::class, 'index'])->name('admin.locker.index');
    Route::get('/datatable', [LockerController::class, 'getIndex'])->name('admin.locker.datatable');
    Route::get('/create', [LockerController::class, 'create'])->name('admin.locker.create');
    Route::post('/store', [LockerController::class, 'store'])->name('admin.locker.store');
    Route::get('/edit/{id}', [LockerController::class, 'edit'])->name('admin.locker.edit');
    Route::post('/update', [LockerController::class, 'update'])->name('admin.locker.update');
    Route::post('/delete', [LockerController::class, 'delete'])->name('admin.locker.delete');
    Route::get('/select', [LockerController::class, 'select'])->name('admin.locker.select');
});

Route::prefix('room')->group(function(){
    Route::group(['middleware' => ['permission:room.list,admin']], function () {
        Route::get('/index', [RoomController::class, 'index'])->name('admin.room.index');
        Route::get('/datatable', [RoomController::class, 'getIndex'])->name('admin.room.datatable');
    });
    Route::group(['middleware' => ['permission:room.add,admin']], function () {
        Route::get('/create', [RoomController::class, 'create'])->name('admin.room.create');
        Route::post('/store', [RoomController::class, 'store'])->name('admin.room.store');
    });
    Route::group(['middleware' => ['permission:room.edit,admin']], function () {
        Route::get('/edit/{id}', [RoomController::class, 'edit'])->name('admin.room.edit');
        Route::post('/update', [RoomController::class, 'update'])->name('admin.room.update');
    });
    Route::group(['middleware' => ['permission:room.delete,admin']], function () {
        Route::post('/delete', [RoomController::class, 'delete'])->name('admin.room.delete');
    });


    Route::get('/get', [RoomController::class, 'getRooms'])->name('admin.room.get');
    Route::get('/oura/get', [RoomController::class, 'getRoomsOura'])->name('admin.oura_room.get');
    Route::get('/select', [RoomController::class, 'select'])->name('admin.room.select');
    Route::get('/select_active', [RoomController::class, 'selectActive'])->name('admin.room.select_active');
    Route::get('/select_all', [RoomController::class, 'selectAll'])->name('admin.room.select_all');
    Route::get('oura/select', [RoomController::class, 'selectOura'])->name('admin.room.oura.select');
});
Route::prefix('package')->group(function () {

    Route::group(['middleware' => ['permission:treatment.list,admin']], function () {
        Route::get('/index', [PackageController::class, 'index'])->name('admin.package.index');
        Route::get('/datatable', [PackageController::class, 'getIndex'])->name('admin.package.datatable');
    });

    Route::group(['middleware' => ['permission:treatment.add,admin']], function () {
        Route::get('/create', [PackageController::class, 'create'])->name('admin.package.create');
        Route::post('/store', [PackageController::class, 'store'])->name('admin.package.store');
    });

    Route::group(['middleware' => ['permission:treatment.edit,admin']], function () {
        Route::get('/edit/{id}', [PackageController::class, 'edit'])->name('admin.package.edit');
        Route::post('/update', [PackageController::class, 'update'])->name('admin.package.update');
    });

    Route::group(['middleware' => ['permission:treatment.show,admin']], function () {
        Route::get('/show/{id}', [PackageController::class, 'show'])->name('admin.package.show');
    });

    Route::group(['middleware' => ['permission:treatment.delete,admin']], function () {
        Route::post('/delete', [PackageController::class, 'delete'])->name('admin.package.delete');
    });

    Route::get('/get', [PackageController::class, 'getPackages'])->name('admin.package.get');
    Route::get('/get/detail', [PackageController::class, 'getDetail'])->name('admin.package.get.detail');
    Route::get('/select', [PackageController::class, 'select'])->name('admin.package.select');
    Route::get('/select/get', [PackageController::class, 'get'])->name('admin.package.select_get');
});

Route::prefix('warehouse')->group(function(){

    Route::group(['middleware' => ['permission:warehouse.list,admin']], function () {
        Route::get('/index', [WarehouseController::class, 'index'])->name('admin.warehouse.index');
        Route::get('/datatable', [WarehouseController::class, 'getIndex'])->name('admin.warehouse.datatable');
    });

    Route::group(['middleware' => ['permission:warehouse.add,admin']], function () {
        Route::get('/create', [WarehouseController::class, 'create'])->name('admin.warehouse.create');
        Route::post('/store', [WarehouseController::class, 'store'])->name('admin.warehouse.store');
    });

    Route::group(['middleware' => ['permission:warehouse.edit,admin']], function () {
        Route::get('/edit/{id}', [WarehouseController::class, 'edit'])->name('admin.warehouse.edit');
        Route::post('/update', [WarehouseController::class, 'update'])->name('admin.warehouse.update');
    });

    Route::group(['middleware' => ['permission:warehouse.show,admin']], function () {

    });

    Route::group(['middleware' => ['permission:warehouse.delete,admin']], function () {
        Route::post('/delete', [WarehouseController::class, 'delete'])->name('admin.warehouse.delete');
    });

    Route::get('/select',[WarehouseController::class, 'select'])->name('admin.warehouse.select');
});

Route::prefix('supplier')->group(function(){
    Route::group(['middleware' => ['permission:supplier.list,admin']], function () {
        Route::get('/index', [SupplierController::class, 'index'])->name('admin.supplier.index');
        Route::get('/datatable', [SupplierController::class, 'getIndex'])->name('admin.supplier.datatable');
    });

    Route::group(['middleware' => ['permission:supplier.add,admin']], function () {
        Route::get('/create', [SupplierController::class, 'create'])->name('admin.supplier.create');
        Route::post('/store', [SupplierController::class, 'store'])->name('admin.supplier.store');
    });

    Route::group(['middleware' => ['permission:supplier.edit,admin']], function () {
        Route::get('/edit/{id}', [SupplierController::class, 'edit'])->name('admin.supplier.edit');
        Route::post('/update', [SupplierController::class, 'update'])->name('admin.supplier.update');
    });

    Route::group(['middleware' => ['permission:supplier.show,admin']], function () {

    });

    Route::group(['middleware' => ['permission:supplier.delete,admin']], function () {
        Route::post('/delete', [SupplierController::class, 'delete'])->name('admin.supplier.delete');
    });

    Route::get('/select',[SupplierController::class, 'select'])->name('admin.supplier.select');
});

Route::prefix('uom')->group(function(){

    Route::group(['middleware' => ['permission:unit_material.list,admin']], function () {
        Route::get('/index', [UnitMaterialController::class, 'index'])->name('admin.uom.index');
        Route::get('/datatable', [UnitMaterialController::class, 'getIndex'])->name('admin.uom.datatable');
    });

    Route::group(['middleware' => ['permission:unit_material.add,admin']], function () {
        Route::get('/create', [UnitMaterialController::class, 'create'])->name('admin.uom.create');
        Route::post('/store', [UnitMaterialController::class, 'store'])->name('admin.uom.store');
    });

    Route::group(['middleware' => ['permission:unit_material.edit,admin']], function () {
        Route::get('/edit/{id}', [UnitMaterialController::class, 'edit'])->name('admin.uom.edit');
        Route::post('/update', [UnitMaterialController::class, 'update'])->name('admin.uom.update');
    });

    Route::group(['middleware' => ['permission:unit_material.show,admin']], function () {

    });

    Route::group(['middleware' => ['permission:unit_material.delete,admin']], function () {
        Route::post('/delete', [UnitMaterialController::class, 'delete'])->name('admin.uom.delete');
    });

    Route::get('/select',[UnitMaterialController::class, 'select'])->name('admin.uom.select');
});

Route::prefix('item')->group(function(){
    Route::group(['middleware' => ['permission:item.list,admin']], function () {
        Route::get('/index', [ItemController::class, 'index'])->name('admin.item.index');
        Route::get('/datatable', [ItemController::class, 'getIndex'])->name('admin.item.datatable');
    });
    Route::group(['middleware' => ['permission:item.add,admin']], function () {
        Route::get('/create', [ItemController::class, 'create'])->name('admin.item.create');
        Route::post('/store', [ItemController::class, 'store'])->name('admin.item.store');
    });
    Route::group(['middleware' => ['permission:item.edit,admin']], function () {
        Route::get('/edit/{id}', [ItemController::class, 'edit'])->name('admin.item.edit');
        Route::post('/update/{id}', [ItemController::class, 'update'])->name('admin.item.update');
    });
    Route::group(['middleware' => ['permission:item.delete,admin']], function () {
        Route::post('/delete', [ItemController::class, 'delete'])->name('admin.item.delete');
    });
    Route::get('/select',[ItemController::class, 'select'])->name('admin.item.select');
    Route::get('/selectGroup',[ItemController::class, 'selectGroup'])->name('admin.item.selectGroup');
    Route::get('/selectWithoutFood',[ItemController::class, 'selectWithoutFood'])->name('admin.item.selectWithoutFood');
    Route::get('/get',[ItemController::class, 'get'])->name('admin.item.get');
    Route::get('/get/detail',[ItemController::class, 'getDetail'])->name('admin.item.get.detail');
    Route::get('/price',[ItemController::class, 'getPrice'])->name('admin.item.price');
    Route::get('/unit',[ItemController::class, 'selectUnit'])->name('admin.item.unit.select');
});


Route::prefix('item_category')->group(function(){
    Route::group(['middleware' => ['permission:item_category.list,admin']], function () {
        Route::get('/index', [ItemCategoryController::class, 'index'])->name('admin.item_category.index');
        Route::get('/datatable', [ItemCategoryController::class, 'getIndex'])->name('admin.item_category.datatable');
    });

    Route::group(['middleware' => ['permission:item_category.add,admin']], function () {
        Route::get('/create', [ItemCategoryController::class, 'create'])->name('admin.item_category.create');
        Route::post('/store', [ItemCategoryController::class, 'store'])->name('admin.item_category.store');
    });

    Route::group(['middleware' => ['permission:item_category.edit,admin']], function () {
        Route::get('/edit/{id}', [ItemCategoryController::class, 'edit'])->name('admin.item_category.edit');
        Route::post('/update', [ItemCategoryController::class, 'update'])->name('admin.item_category.update');
    });

    Route::group(['middleware' => ['permission:item_category.show,admin']], function () {

    });

    Route::group(['middleware' => ['permission:item_category.delete,admin']], function () {
        Route::post('/delete', [ItemCategoryController::class, 'delete'])->name('admin.item_category.delete');
    });

    Route::get('/select',[ItemCategoryController::class, 'select'])->name('admin.item_category.select');
});

Route::prefix('employee')->group(function () {
    Route::group(['middleware' => ['permission:employee.list,admin']], function () {
        Route::get('/index', [EmployeeController::class, 'index'])->name('admin.employee.index');
        Route::get('/datatable', [EmployeeController::class, 'getIndex'])->name('admin.employee.datatable');
    });

    Route::group(['middleware' => ['permission:employee.add,admin']], function () {
        Route::get('/create', [EmployeeController::class, 'create'])->name('admin.employee.create');
        Route::post('/store', [EmployeeController::class, 'store'])->name('admin.employee.store');
        Route::get('/contract/create/{id}', [EmployeeController::class, 'createContract'])->name('admin.employee.contract.create');
        Route::post('/contract/store', [EmployeeController::class, 'storeContract'])->name('admin.employee.contract.store');
    });

    Route::group(['middleware' => ['permission:employee.edit,admin']], function () {
        Route::get('/edit/{id}', [EmployeeController::class, 'edit'])->name('admin.employee.edit');
        Route::post('/update/{id}', [EmployeeController::class, 'update'])->name('admin.employee.update');
        Route::get('/contract/edit/{id}', [EmployeeController::class, 'editContract'])->name('admin.employee.contract.edit');
        Route::post('/contract/update', [EmployeeController::class, 'updateContract'])->name('admin.employee.contract.update');
    });

    Route::group(['middleware' => ['permission:employee.show,admin']], function () {
        Route::get('/show/{id}', [EmployeeController::class, 'show'])->name('admin.employee.show');
        Route::get('/contract/history/{id}', [EmployeeController::class, 'pkwtHistory'])->name('admin.employee.contract.history');
    });

    Route::group(['middleware' => ['permission:employee.delete,admin']], function () {
        Route::post('/delete', [EmployeeController::class, 'delete'])->name('admin.employee.delete');
        Route::post('/contract/delete', [EmployeeController::class, 'deleteContract'])->name('admin.employee.contract.delete');
    });

    Route::get('/select', [EmployeeController::class, 'select'])->name('admin.employee.select');
    Route::post('store/from_api', [EmployeeController::class, 'storeFromApi'])->name('admin.employee.store.from_api');
    Route::post('/status/update', [EmployeeController::class, 'updateStatus'])->name('admin.employee.status.update');
    Route::get('/gro/get', [EmployeeController::class, 'getGros'])->name('admin.gro.get');

});


Route::prefix('partner')->group(function(){

    Route::group(['middleware' => ['permission:partner.list,admin']], function () {
        Route::get('/index', [PartnerController::class, 'index'])->name('admin.partner.index');
        Route::get('/datatable', [PartnerController::class, 'getIndex'])->name('admin.partner.datatable');
    });

    Route::group(['middleware' => ['permission:partner.add,admin']], function () {
        Route::get('/create', [PartnerController::class, 'create'])->name('admin.partner.create');
        Route::post('/store', [PartnerController::class, 'store'])->name('admin.partner.store');
    });

    Route::group(['middleware' => ['permission:partner.edit,admin']], function () {
        Route::get('/edit/{id}', [PartnerController::class, 'edit'])->name('admin.partner.edit');
        Route::post('/update/{id}', [PartnerController::class, 'update'])->name('admin.partner.update');
    });

    Route::group(['middleware' => ['permission:partner.show,admin']], function () {
        Route::get('/show/{id}', [PartnerController::class, 'show'])->name('admin.partner.show');
    });

    Route::group(['middleware' => ['permission:partner.delete,admin']], function () {
        Route::post('/delete', [PartnerController::class, 'delete'])->name('admin.partner.delete');
    });
    Route::get('/select',[PartnerController::class, 'select'])->name('admin.partner.select');
    Route::get('/select-change',[PartnerController::class, 'selectForChange'])->name('admin.partner.select_change');
    Route::get('/oura/select',[PartnerController::class, 'selectOura'])->name('admin.partner.oura.select');
    Route::post('/is_present/toggle',[PartnerController::class, 'togglePresent'])->name('admin.partner.is_present.toggle');
    Route::post('/is_present/toggle/all/inactive',[PartnerController::class, 'toggleAllInactive'])->name('admin.partner.present.all.inactive');
    Route::get('/oura/get',[PartnerController::class, 'getPartnerOura'])->name('admin.oura_partner.get');
    Route::get('/get/addon', [PartnerController::class, 'getAddOnDetail'])->name('admin.partner.get.addon.detail');

    Route::get('/contract/create/{id}', [PartnerController::class, 'createContract'])->name('admin.partner.contract.create');
    Route::post('/contract/store', [PartnerController::class, 'storeContract'])->name('admin.partner.contract.store');
    Route::get('/contract/edit/{id}', [PartnerController::class, 'editContract'])->name('admin.partner.contract.edit');
    Route::post('/contract/update', [PartnerController::class, 'updateContract'])->name('admin.partner.contract.update');
    Route::post('/contract/delete', [PartnerController::class, 'deleteContract'])->name('admin.partner.contract.delete');
    Route::get('/contract/history/{id}', [PartnerController::class, 'pkwtHistory'])->name('admin.partner.contract.history');
});

Route::prefix('position')->group(function(){

    Route::group(['middleware' => ['permission:position.list,admin']], function () {
        Route::get('/index', [EmployeePositionController::class, 'index'])->name('admin.position.index');
        Route::get('/datatable', [EmployeePositionController::class, 'getIndex'])->name('admin.position.datatable');
    });

    Route::group(['middleware' => ['permission:position.add,admin']], function () {
        Route::get('/create', [EmployeePositionController::class, 'create'])->name('admin.position.create');
        Route::post('/store', [EmployeePositionController::class, 'store'])->name('admin.position.store');
    });

    Route::group(['middleware' => ['permission:position.edit,admin']], function () {
        Route::get('/edit/{id}', [EmployeePositionController::class, 'edit'])->name('admin.position.edit');
        Route::post('/update/{id}', [EmployeePositionController::class, 'update'])->name('admin.position.update');
    });

    Route::group(['middleware' => ['permission:position.show,admin']], function () {

    });

    Route::group(['middleware' => ['permission:position.delete,admin']], function () {
         Route::post('/delete', [EmployeePositionController::class, 'delete'])->name('admin.position.delete');
    });

    Route::get('/get', [EmployeePositionController::class, 'getPositions'])->name('admin.position.get');

    Route::get('/select', [EmployeePositionController::class, 'select'])->name('admin.position.select');
});

Route::prefix('currency')->group(function(){
    Route::get('/select',[ItemCategoryController::class, 'select'])->name('admin.currency.select');
});

Route::prefix('customer')->group(function(){
    Route::get('/select',[CustomerController::class, 'select'])->name('admin.customer.select');
});

Route::prefix('membership')->group(function(){
    Route::get('/index', [MembershipController::class, 'index'])->name('admin.membership.index');
});

Route::prefix('member')->group(function(){
    Route::get('/index', [MemberController::class, 'index'])->name('admin.member.index');
    Route::get('/datatable', [MemberController::class, 'getIndex'])->name('admin.member.datatable');
    Route::get('/search', [MemberController::class, 'search'])->name('admin.member.search');
});

Route::prefix('admin_user')->middleware('permission:admin_user.manage,admin')->group(function(){
    Route::get('/index', [AdminUserController::class, 'index'])->name('admin.admin_user.index');
    Route::get('/create', [AdminUserController::class, 'create'])->name('admin.admin_user.create');
    Route::post('/store', [AdminUserController::class, 'store'])->name('admin.admin_user.store');
    Route::get('/edit/{id}', [AdminUserController::class, 'edit'])->name('admin.admin_user.edit');
    Route::post('/update/{id}', [AdminUserController::class, 'update'])->name('admin.admin_user.update');
    Route::post('/delete', [AdminUserController::class, 'delete'])->name('admin.admin_user.delete');

    Route::get('/select', [AdminUserController::class, 'select'])->name('admin.admin_user.select');
});

Route::prefix('inv_testing')->group(function(){
    Route::get('/isi', [InventoryHistoryController::class, 'isi'])->name('admin.asdf');
});

Route::prefix('voucher_type')->group(function(){
    Route::get('/index', [CouponController::class, 'index'])->name('admin.coupon.index');
    Route::get('/datatable', [CouponController::class, 'getIndex'])->name('admin.coupon.datatable');
    Route::get('/create', [CouponController::class, 'create'])->name('admin.coupon.create');
    Route::get('/show/{id}', [CouponController::class, 'show'])->name('admin.coupon.show');
    Route::post('/store', [CouponController::class, 'store'])->name('admin.coupon.store');
    Route::get('/edit/{id}', [CouponController::class, 'edit'])->name('admin.coupon.edit');
    Route::post('/update', [CouponController::class, 'update'])->name('admin.coupon.update');
    Route::post('/delete', [CouponController::class, 'delete'])->name('admin.coupon.delete');
    Route::get('/select', [CouponController::class, 'select'])->name('admin.coupon.select');
});

Route::prefix('voucher')->group(function(){
    Route::get('/index', [VoucherController::class, 'index'])->name('admin.voucher.index');
    Route::get('/create', [VoucherController::class, 'create'])->name('admin.voucher.create');
    Route::post('/generate', [VoucherController::class, 'generate'])->name('admin.voucher.generate');
    Route::get('/datatable', [VoucherController::class, 'getIndex'])->name('admin.voucher.datatable');
    Route::post('/set/exp', [VoucherController::class, 'setExpDate'])->name('admin.voucher.set_exp_date');
//    Route::post('/check', [VoucherController::class, 'check'])->name('admin.voucher.check');
    //Route::get('/download', [VoucherController::class, 'export'])->name('admin.voucher.export');
});

Route::prefix('voucher_package')->group(function(){
    Route::get('/index', [VoucherPackageController::class, 'index'])->name('admin.voucher_package.index');
    Route::get('/datatable', [VoucherPackageController::class, 'datatable'])->name('admin.voucher_package.datatable');
    Route::get('/create', [VoucherPackageController::class, 'create'])->name('admin.voucher_package.create');
    Route::post('/store', [VoucherPackageController::class, 'store'])->name('admin.voucher_package.store');
    Route::get('/edit/{id}', [VoucherPackageController::class, 'edit'])->name('admin.voucher_package.edit');
    Route::post('/update', [VoucherPackageController::class, 'update'])->name('admin.voucher_package.update');
    Route::post('/delete', [VoucherPackageController::class, 'delete'])->name('admin.voucher_package.delete');
});

Route::prefix('prewedding_package')->group(function(){
    Route::group(['middleware' => ['permission:treatment.list,admin']], function () {
        Route::get('/index', [PreWeddingPackageController::class, 'index'])->name('admin.prewedding_package.index');
        Route::get('/datatable', [PreWeddingPackageController::class, 'datatable'])->name('admin.prewedding_package.datatable');
    });
    Route::group(['middleware' => ['permission:treatment.add,admin']], function () {
        Route::get('/create', [PreWeddingPackageController::class, 'create'])->name('admin.prewedding_package.create');
        Route::post('/store', [PreWeddingPackageController::class, 'store'])->name('admin.prewedding_package.store');
    });
    Route::group(['middleware' => ['permission:treatment.edit,admin']], function () {
        Route::get('/edit/{id}', [PreWeddingPackageController::class, 'edit'])->name('admin.prewedding_package.edit');
        Route::post('/update', [PreWeddingPackageController::class, 'update'])->name('admin.prewedding_package.update');
    });
    Route::group(['middleware' => ['permission:treatment.delete,admin']], function () {
    });
    Route::get('/get_all', [PreWeddingPackageController::class, 'getAll'])->name('admin.prewedding_package.get_all');
});


Route::prefix('user')->group(function(){
    Route::get('/search', [UserController::class, 'search'])->name('admin.user.search');
});

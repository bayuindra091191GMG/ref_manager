<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Models\Customer;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use App\Http\Controllers\Admin\MenuAccessController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/testingform', function () {

    $customers = Customer::get();
    $formattedCustomer = [];
    foreach ($customers as $customer) {
      $formattedCustomer[] = ['id' => $customer->id, 'code' => $customer->code, 'name' => $customer->name];
    }
    return view('admin.testingform', compact('formattedCustomer'));
});

Route::get('/testmonitor', function () {
    \App\Jobs\UpdateMonitor::dispatch();
});

// Route::get('/', [LoginController::class, 'mainLoginShow'])->name('admin.main.login');
Route::get('/', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/login/submit', [LoginController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
Route::get('/jat-sync', [LoginController::class, 'SyncTimbanganHeader'])->name('admin.SyncTimbanganHeader');
// Route::get('/testingform', [DashboardController::class, 'testingform'])->name('admin.dashboard.testingform');

Route::get('/setrole', function (){
    $permissions = [];
    $menus = \App\Models\Menu::all();
    foreach ($menus as $menu){
        $actions = explode(',', $menu->actions);
        foreach ($actions as $action){
            $permissions[] = $menu->code . '.' . $action;

            $check = \Spatie\Permission\Models\Permission::where('name', $menu->code . '.' . $action)->first();
            if(!$check){
                echo $menu->code . '.' . $action . '<br>';
                \Spatie\Permission\Models\Permission::create([
                    'name' => $menu->code . '.' . $action,
                    'guard_name' => 'admin',
                ]);
            }
        }
    }
});

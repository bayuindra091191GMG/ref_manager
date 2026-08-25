<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/admin/app_customer.php',
            __DIR__.'/../routes/admin/attendance.php',
            __DIR__.'/../routes/admin/booking.php',
            __DIR__.'/../routes/admin/prewedding.php',
            __DIR__.'/../routes/admin/dashboard.php',
            __DIR__.'/../routes/admin/excel.php',
            __DIR__.'/../routes/admin/finance.php',
            __DIR__.'/../routes/admin/home_service.php',
            __DIR__.'/../routes/admin/hrd.php',
            __DIR__.'/../routes/admin/import.php',
            __DIR__.'/../routes/admin/inventory.php',
            __DIR__.'/../routes/admin/ledger.php',
            __DIR__.'/../routes/admin/master_data.php',
            __DIR__.'/../routes/admin/monitor.php',
            __DIR__.'/../routes/admin/monitor_oura.php',
            __DIR__.'/../routes/admin/notification.php',
            __DIR__.'/../routes/admin/pos.php',
            __DIR__.'/../routes/admin/purchasing.php',
            __DIR__.'/../routes/admin/report.php',
            __DIR__.'/../routes/admin/revision.php',
            __DIR__.'/../routes/admin/sales.php',
            __DIR__.'/../routes/admin/production.php',
            __DIR__.'/../routes/admin/script.php',
            __DIR__.'/../routes/admin/setting.php',
            __DIR__.'/../routes/admin/voucher.php',
            __DIR__.'/../routes/admin/voucher_pos.php',
        ],
        api: [
            __DIR__.'/../routes/api.php',
            __DIR__.'/../routes/api/user.php',
            __DIR__.'/../routes/api/cart.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn(Request $request) => route('admin.login'));
        $middleware->alias([
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'api.logger' => \App\Http\Middleware\ApiLogger::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

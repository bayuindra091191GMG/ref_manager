<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new \App\Jobs\InitiateReminder())->everyMinute();
Schedule::job(new \App\Jobs\UpdateMonitor())->everyMinute();
Schedule::job(new \App\Jobs\SendUnpaidBookingPaymentReminder())->everyMinute();
Schedule::job(new \App\Jobs\SendUnpaidVoucherPaymentReminder())->everyMinute();
Schedule::job(new \App\Jobs\CancelExpiredBookingPayments())->everyMinute();
Schedule::job(new \App\Jobs\CancelExpiredVoucherPayments())->everyMinute();


Schedule::job(new \App\Jobs\GenerateTransactionCommision())
    ->dailyAt('23:57')
    ->timezone('Asia/Jakarta');

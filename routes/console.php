<?php

use App\Actions\RedProviderPortal\QueryOrderUpdates;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(QueryOrderUpdates::class)->handle())
    ->when(Config::get('services.red_provider_portal.poll_order_updates', false))
    ->everyMinute();


// TODO implement polling of not completed red provider portal orders

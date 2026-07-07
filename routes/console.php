<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('vehicles:sync')
    ->dailyAt('03:00')
    ->withoutOverlapping(expiresAt: 180)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/vehicles-sync.log'));

Schedule::command('vehicles:enrich')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/vehicles-enrich.log'));

Schedule::command('brands:sync-logos')
    ->dailyAt('06:00')
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/vehicles-sync.log'));

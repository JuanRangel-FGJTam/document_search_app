<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command('app:delete-documents')
    ->timezone('America/Mexico_City')
    ->daily();

Schedule::command('app:sync-records-to-legacy')->daily()->timezone('America/Mexico_City')->at('01:00');
// Schedule::command('app:sync-records-to-legacy')->everyTenMinutes();
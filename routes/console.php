<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('titipin:close-expired')->everyMinute();

Schedule::command('titipin:downgrade-expired-tiers')->daily();

Schedule::command('analytics:sync')->hourly();
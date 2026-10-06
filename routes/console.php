<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Schedule your custom Artisan command to run daily
Schedule::command('users:purge-contacts')->daily();

// news events
Schedule::command('news:publish-scheduled')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->onOneServer();
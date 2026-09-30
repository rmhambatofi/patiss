<?php

use Illuminate\Support\Facades\Schedule;

// o2switch has no long-running worker: the cPanel cron runs the scheduler every minute,
// which drains the database queue (KTD8).
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')
    ->everyMinute()
    ->withoutOverlapping();

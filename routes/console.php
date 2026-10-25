<?php

use App\Console\Commands\BackupStorage;
use App\Console\Commands\CleanExpiredCarts;
use Illuminate\Support\Facades\Schedule;

Schedule::command(CleanExpiredCarts::class)->hourly();
Schedule::command(BackupStorage::class)->dailyAt('04:00')->withoutOverlapping();

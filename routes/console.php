<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('billing:generate')->dailyAt('01:00')->timezone('Asia/Jakarta');
Schedule::command('billing:mark-overdue')->dailyAt('02:00')->timezone('Asia/Jakarta');
Schedule::command('billing:remind')->dailyAt('08:00')->timezone('Asia/Jakarta')->withoutOverlapping();

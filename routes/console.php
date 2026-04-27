<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Har kuni ertalab 9:00 da checkin tekshiruvi
Schedule::command('bot:checkin')->dailyAt('09:00');

// Har soatda javobsiz bitimlarni bekor qilish
Schedule::command('deal:timeout')->hourly();

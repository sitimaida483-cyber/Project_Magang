<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pengingat otomatis presensi masuk & pulang berjalan setiap menit
\Illuminate\Support\Facades\Schedule::command('presensi:kirim-pengingat')->everyMinute();

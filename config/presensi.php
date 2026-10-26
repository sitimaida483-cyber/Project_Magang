<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pengaturan Waktu Pengingat Presensi (Menit)
    |--------------------------------------------------------------------------
    |
    | Menentukan berapa menit sebelum jam_masuk atau jam_keluar notifikasi
    | pengingat dikirimkan otomatis oleh Laravel Scheduler kepada personel.
    | Default: 15 menit sebelumnya.
    |
    */
    'menit_pengingat' => (int) env('PRESENSI_MENIT_PENGINGAT', 15),

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi VAPID Web Push
    |--------------------------------------------------------------------------
    */
    'vapid' => [
        'subject'     => env('VAPID_SUBJECT', 'mailto:bidtik@kalsel.polri.go.id'),
        'public_key'  => env('VAPID_PUBLIC_KEY', ''),
        'private_key' => env('VAPID_PRIVATE_KEY', ''),
    ],
];

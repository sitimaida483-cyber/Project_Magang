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

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Pengajuan Surat Keterangan / Izin Dinas
    |--------------------------------------------------------------------------
    */
    // Ukuran maksimal berkas lampiran PDF (dalam Kilobytes, default: 2048 KB = 2 MB)
    'surat_max_size_kb' => (int) env('SURAT_MAX_SIZE_KB', 2048),

    // Daftar jenis keterangan surat yang TETAP diwajibkan presensi lokasi
    // Default: kosong (semua surat yang disetujui membebaskan kewajiban presensi lokasi)
    // Contoh jika nanti diminta pembimbing: ['wfh', 'piket']
    'jenis_surat_tetap_presensi' => [],
];

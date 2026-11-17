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

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Berkas Selfie & Tanda Tangan Presensi
    |--------------------------------------------------------------------------
    */
    // Ukuran maksimal byte untuk berkas selfie & tanda tangan (default: 3 MB)
    'foto_max_bytes'    => (int) env('PRESENSI_FOTO_MAX_BYTES', 3 * 1024 * 1024),
    // Batas resolusi dimensi maksimum (lebar & tinggi dalam pixel)
    'foto_max_width'    => (int) env('PRESENSI_FOTO_MAX_WIDTH', 1920),
    'foto_max_height'   => (int) env('PRESENSI_FOTO_MAX_HEIGHT', 1920),
    // Kualitas re-encode JPEG (1-100)
    'foto_jpeg_quality' => (int) env('PRESENSI_FOTO_JPEG_QUALITY', 85),

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Deteksi Anomali Pemalsuan Presensi (Tahap 2)
    |--------------------------------------------------------------------------
    */
    'anomali' => [
        // Mode anomali: 'catat' (presensi tetap disimpan tapi ber-flag anomali)
        // atau 'tolak' (presensi ditolak dengan pesan kesalahan umum)
        'mode' => env('PRESENSI_ANOMALI_MODE', 'catat'),

        // Aturan a: Jarak ke titik pusat kantor sangat kecil (default < 1.0 meter menandakan fake GPS pin)
        'jarak_terlalu_sempurna_meter' => (float) env('ANOMALI_JARAK_MIN_METER', 1.0),

        // Aturan b: Akurasi GPS kosong, terlalu kecil (<= 1.0 meter) atau terlalu besar (> 150.0 meter)
        'akurasi_min_meter' => (float) env('ANOMALI_AKURASI_MIN_METER', 1.0),
        'akurasi_max_meter' => (float) env('ANOMALI_AKURASI_MAX_METER', 150.0),

        // Aturan c: Koordinat identik pada hari yang sama (pembulatan 6 angka desimal)
        'cek_koordinat_duplikat' => (bool) env('ANOMALI_CEK_KOORDINAT_DUPLIKAT', true),

        // Aturan d: Deteksi foto selfie identik/bekas yang pernah dipakai sebelumnya oleh anggota yang sama
        'cek_foto_duplikat' => (bool) env('ANOMALI_CEK_FOTO_DUPLIKAT', true),

        // Aturan e: Rentang waktu minimal presensi pulang setelah masuk (dalam menit, default: 60)
        'pulang_min_menit_setelah_masuk' => (int) env('ANOMALI_PULANG_MIN_MENIT', 60),

        // Aturan f: Pembatasan subnet jaringan kantor (opsional, default: nonaktif/kosong)
        // Format di .env: 192.168.1.0/24,10.0.0.0/8
        'subnet_kantor' => array_filter(array_map('trim', explode(',', (string) env('PRESENSI_ALLOWED_SUBNETS', '')))),
    ],
];

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\AnggotaController;
use App\Http\Controllers\Admin\PetaController;
use App\Http\Controllers\Admin\LogAktivitasController;
use App\Http\Controllers\Admin\PresensiFileController;
use App\Http\Controllers\Admin\PresensiController as AdminPresensiController;
use App\Http\Controllers\Admin\PengajuanSuratController as AdminPengajuanSuratController;
use App\Http\Controllers\Anggota\AuthController as AnggotaAuthController;
use App\Http\Controllers\Anggota\PresensiController as AnggotaPresensiController;
use App\Http\Controllers\Anggota\PengajuanSuratController as AnggotaPengajuanSuratController;
use App\Http\Controllers\Anggota\NotifikasiController;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Presensi Web Bidtik Polda Kalsel
|--------------------------------------------------------------------------
*/

// Halaman utama otomatis mengarahkan ke dashboard (jika admin), presensi (jika anggota), atau login anggota
Route::get('/', function () {
    if (auth('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    if (auth('anggota')->check()) {
        return redirect()->route('presensi.index');
    }
    return redirect()->route('login');
});

// ==========================================
// RUTE AUTENTIKASI ANGGOTA (PERSONEL)
// ==========================================
Route::get('/login', [AnggotaAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AnggotaAuthController::class, 'login'])->name('login.proses');
Route::post('/logout', [AnggotaAuthController::class, 'logout'])->name('logout');

// ==========================================
// RUTE PANEL ANGGOTA (TERLINDUNGI GUARD 'anggota')
// ==========================================
Route::middleware('anggota.auth')->group(function () {
    // Presensi Kehadiran Anggota
    Route::prefix('presensi')->name('presensi.')->group(function () {
        Route::get('/', [AnggotaPresensiController::class, 'index'])->name('index');
        Route::post('/cek-nrp', [AnggotaPresensiController::class, 'cekNrp'])->name('cek_nrp'); // Cadangan endpoint AJAX
        Route::post('/simpan', [AnggotaPresensiController::class, 'store'])->name('store');
        Route::post('/simpan-perangkat', [NotifikasiController::class, 'subscribe'])->name('subscribe');
    });

    // Pengajuan Surat Keterangan Personel (Revisi 2)
    Route::prefix('surat')->name('anggota.surat.')->group(function () {
        Route::get('/', [AnggotaPengajuanSuratController::class, 'index'])->name('index');
        Route::get('/buat', [AnggotaPengajuanSuratController::class, 'create'])->name('create');
        Route::post('/', [AnggotaPengajuanSuratController::class, 'store'])->name('store');
        Route::post('/{id}/batal', [AnggotaPengajuanSuratController::class, 'cancel'])->name('cancel');
        Route::get('/{id}/file', [AnggotaPengajuanSuratController::class, 'file'])->name('file');
    });

    // Ubah Password Mandiri Anggota
    Route::get('/ubah-password', [AnggotaAuthController::class, 'showUbahPassword'])->name('anggota.password');
    Route::put('/ubah-password', [AnggotaAuthController::class, 'ubahPassword'])->name('anggota.password.update');
});

// ========================
// RUTE AUTENTIKASI ADMIN
// ========================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.proses');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // ========================
    // PANEL ADMIN (TERLINDUNGI)
    // ========================
    Route::middleware('admin.auth')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Akses aman file foto selfie dan tanda tangan presensi (hanya untuk admin login)
        Route::get('/presensi/file/{type}/{filename}', [PresensiFileController::class, 'show'])->name('presensi.file');

        // Modul Rekap Presensi (Tahap 7)
        Route::get('/presensi', [AdminPresensiController::class, 'index'])->name('presensi.index');
        Route::get('/presensi/ekspor-pdf', [AdminPresensiController::class, 'eksporPdf'])->name('presensi.ekspor_pdf');
        Route::get('/presensi/ekspor-excel', [AdminPresensiController::class, 'eksporExcel'])->name('presensi.ekspor_excel');
        Route::get('/presensi/{id}', [AdminPresensiController::class, 'show'])->name('presensi.show');
        
        // Modul Pengajuan Surat Keterangan (Revisi 2)
        Route::get('/surat', [AdminPengajuanSuratController::class, 'index'])->name('surat.index');
        Route::get('/surat/{id}', [AdminPengajuanSuratController::class, 'show'])->name('surat.show');
        Route::post('/surat/{id}/proses', [AdminPengajuanSuratController::class, 'proses'])->name('surat.proses');
        Route::get('/surat/{id}/file', [AdminPengajuanSuratController::class, 'file'])->name('surat.file');

        // Modul Peta Lokasi (Tahap 8)
        Route::get('/peta', [PetaController::class, 'index'])->name('peta.index');

        // Modul Kelola Anggota (Tahap 4)
        Route::put('/anggota/{id}/reset-password', [AnggotaController::class, 'resetPassword'])->name('anggota.reset_password');
        Route::resource('/anggota', AnggotaController::class);

        // Modul Pengaturan Kantor (Tahap 3)
        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::put('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');

        // Modul Log Aktivitas (Tahap 9)
        Route::get('/log', [LogAktivitasController::class, 'index'])->name('log.index');
    });
});

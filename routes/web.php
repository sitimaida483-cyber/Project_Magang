<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\AnggotaController;
use App\Http\Controllers\Admin\PetaController;
use App\Http\Controllers\Admin\LogAktivitasController;
use App\Http\Controllers\Admin\PresensiFileController;
use App\Http\Controllers\Admin\PresensiController as AdminPresensiController;
use App\Http\Controllers\Anggota\PresensiController as AnggotaPresensiController;
use App\Http\Controllers\Anggota\NotifikasiController;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Presensi Web Bidtik Polda Kalsel
|--------------------------------------------------------------------------
*/

// Halaman utama otomatis mengarahkan ke halaman presensi anggota atau dashboard jika admin
Route::get('/', function () {
    if (auth('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('presensi.index');
});

// ==========================================
// RUTE HALAMAN PUBLIK PRESENSI ANGGOTA (HP)
// ==========================================
Route::prefix('presensi')->name('presensi.')->group(function () {
    Route::get('/', [AnggotaPresensiController::class, 'index'])->name('index');
    Route::post('/cek-nrp', [AnggotaPresensiController::class, 'cekNrp'])->name('cek_nrp');
    Route::post('/simpan', [AnggotaPresensiController::class, 'store'])->name('store');
    Route::post('/simpan-perangkat', [NotifikasiController::class, 'subscribe'])->name('subscribe');
});

// ========================
// RUTE AUTENTIKASI ADMIN
// ========================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.proses');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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
        
        // Modul Peta Lokasi (Tahap 8)
        Route::get('/peta', [PetaController::class, 'index'])->name('peta.index');

        // Modul Kelola Anggota (Tahap 4)
        Route::resource('/anggota', AnggotaController::class);

        // Modul Pengaturan Kantor (Tahap 3)
        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::put('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');

        // Modul Log Aktivitas (Tahap 9)
        Route::get('/log', [LogAktivitasController::class, 'index'])->name('log.index');
    });
});

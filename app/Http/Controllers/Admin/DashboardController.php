<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Presensi;
use Illuminate\Support\Carbon;

/**
 * DashboardController — menampilkan ringkasan presensi hari ini.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $hari_ini = Carbon::today();

        // Statistik presensi hari ini
        $total_masuk = Presensi::whereDate('tanggal', $hari_ini)
            ->where('jenis_presensi', 'masuk')
            ->count();

        $total_pulang = Presensi::whereDate('tanggal', $hari_ini)
            ->where('jenis_presensi', 'pulang')
            ->count();

        $total_terlambat = Presensi::whereDate('tanggal', $hari_ini)
            ->where('jenis_presensi', 'masuk')
            ->where('status_kehadiran', 'terlambat')
            ->count();

        $total_anggota_aktif = Anggota::where('status', 'aktif')->count();

        // Presensi terbaru hari ini (10 terakhir)
        $presensi_terbaru = Presensi::with('anggota')
            ->whereDate('tanggal', $hari_ini)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard.index', compact(
            'total_masuk',
            'total_pulang',
            'total_terlambat',
            'total_anggota_aktif',
            'presensi_terbaru',
            'hari_ini'
        ));
    }
}

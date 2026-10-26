<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * LogAktivitasController (Admin Panel)
 * Mengelola tampilan audit trail seluruh aktivitas penting yang dilakukan oleh admin
 * (seperti login/logout, penambahan/perubahan personel, konfigurasi kantor).
 */
class LogAktivitasController extends Controller
{
    /**
     * Tampilkan riwayat audit log aktivitas admin dengan filter.
     */
    public function index(Request $request): View
    {
        $query = LogAktivitas::with('admin');

        // 1. Filter Rentang Tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('waktu_aktivitas', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('waktu_aktivitas', '<=', $request->tanggal_selesai);
        }

        // 2. Filter Tabel Terkait
        if ($request->filled('tabel_terkait')) {
            $query->where('tabel_terkait', $request->tabel_terkait);
        }

        // 3. Pencarian Kata Kunci (Aktivitas, Keterangan, atau Alamat IP)
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('aktivitas', 'like', "%{$q}%")
                    ->orWhere('keterangan', 'like', "%{$q}%")
                    ->orWhere('alamat_ip', 'like', "%{$q}%");
            });
        }

        // Statistik
        $totalLog = LogAktivitas::count();
        $logHariIni = LogAktivitas::whereDate('waktu_aktivitas', Carbon::today())->count();
        $ipUnik = LogAktivitas::distinct('alamat_ip')->count('alamat_ip');

        // Ambil data dengan paginasi 20 per halaman
        $logs = $query->orderByDesc('waktu_aktivitas')
            ->orderByDesc('id_log')
            ->paginate(20)
            ->withQueryString();

        // Daftar tabel unik untuk dropdown filter
        $daftarTabel = LogAktivitas::whereNotNull('tabel_terkait')
            ->distinct()
            ->pluck('tabel_terkait');

        return view('admin.log.index', compact(
            'logs',
            'totalLog',
            'logHariIni',
            'ipUnik',
            'daftarTabel'
        ));
    }
}


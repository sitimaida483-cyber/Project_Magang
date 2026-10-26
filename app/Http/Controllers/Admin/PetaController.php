<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Pengaturan;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * PetaController (Admin Panel)
 * Menampilkan visualisasi peta spasial sebaran presensi personel Bidtik Polda Kalsel
 * berbasis Leaflet.js dengan titik koordinat presensi, radius kantor, dan filter interaktif.
 */
class PetaController extends Controller
{
    /**
     * Tampilkan halaman peta persebaran presensi personel.
     */
    public function index(Request $request): View
    {
        $pengaturan = Pengaturan::first();

        // Tanggal default adalah hari ini (WITA)
        $tanggalAktif = $request->input('tanggal', Carbon::today()->toDateString());

        $query = Presensi::with('anggota')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Filter Tanggal
        if ($tanggalAktif) {
            $query->whereDate('tanggal', $tanggalAktif);
        }

        // Filter Personel
        if ($request->filled('id_anggota')) {
            $query->where('id_anggota', $request->id_anggota);
        }

        // Filter Jenis Presensi (Masuk / Pulang)
        if ($request->filled('jenis_presensi') && in_array($request->jenis_presensi, ['masuk', 'pulang'])) {
            $query->where('jenis_presensi', $request->jenis_presensi);
        }

        $presensiCollection = $query->orderBy('waktu', 'asc')->get();

        // Hitung statistik titik
        $totalTitik = $presensiCollection->count();
        $totalMasuk = $presensiCollection->where('jenis_presensi', 'masuk')->count();
        $totalPulang = $presensiCollection->where('jenis_presensi', 'pulang')->count();
        $totalTerlambat = $presensiCollection->where('status_kehadiran', 'terlambat')->count();

        // Format data marker untuk dikirim ke Leaflet.js
        $dataPresensiList = $presensiCollection->map(function ($p) {
            $fotoUrl = null;
            if ($p->foto_selfie) {
                $fotoUrl = route('admin.presensi.file', [
                    'type'     => 'foto',
                    'filename' => basename($p->foto_selfie),
                ]);
            }

            return [
                'id_presensi'      => $p->id_presensi,
                'nrp'              => $p->anggota->nrp ?? '-',
                'nama'             => $p->anggota->nama ?? 'Personel',
                'pangkat'          => $p->anggota->jabatan ?? '-',
                'unit_kerja'       => $p->anggota->unit_kerja ?? '-',
                'latitude'         => (float) $p->latitude,
                'longitude'        => (float) $p->longitude,
                'jenis_presensi'   => $p->jenis_presensi,
                'status_kehadiran' => $p->status_kehadiran,
                'waktu'            => Carbon::parse($p->waktu)->format('H:i:s') . ' WITA',
                'tanggal'          => Carbon::parse($p->tanggal)->translatedFormat('d M Y'),
                'foto_url'         => $fotoUrl,
                'detail_url'       => route('admin.presensi.show', $p->id_presensi),
            ];
        })->values();

        // Daftar anggota untuk filter dropdown
        $anggotaList = Anggota::orderBy('nama')->get();

        return view('admin.peta.index', [
            'pengaturan'       => $pengaturan,
            'anggotaList'      => $anggotaList,
            'dataPresensiList' => $dataPresensiList,
            'totalTitik'       => $totalTitik,
            'totalMasuk'       => $totalMasuk,
            'totalPulang'      => $totalPulang,
            'totalTerlambat'   => $totalTerlambat,
            'tanggalAktif'     => $tanggalAktif,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PengaturanRequest;
use App\Models\Pengaturan;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * PengaturanController
 * Mengelola konfigurasi titik kantor, radius presensi, dan jam kerja.
 */
class PengaturanController extends Controller
{
    /**
     * Tampilkan formulir pengaturan dan peta lokasi kantor.
     */
    public function index(): View
    {
        // Ambil pengaturan yang ada, atau buat data awal jika belum tersedia
        $pengaturan = Pengaturan::first();

        if (! $pengaturan) {
            $pengaturan = Pengaturan::create([
                'id_admin'     => auth('admin')->user()->id_admin,
                'nama_lokasi'  => 'Bidtik Polda Kalimantan Selatan',
                'latitude'     => -3.3194000,
                'longitude'    => 114.5925000,
                'radius_meter' => 100,
                'jam_masuk'    => '07:30:00',
                'jam_keluar'   => '16:00:00',
            ]);
        }

        return view('admin.pengaturan.index', compact('pengaturan'));
    }

    /**
     * Simpan perubahan pengaturan kantor dan catat ke log aktivitas.
     */
    public function update(PengaturanRequest $request): RedirectResponse
    {
        $pengaturan = Pengaturan::first();

        if (! $pengaturan) {
            $pengaturan = new Pengaturan();
        }

        // Format waktu agar sesuai tipe database TIME (HH:MM:SS)
        $jamMasuk  = strlen($request->jam_masuk) === 5 ? $request->jam_masuk . ':00' : $request->jam_masuk;
        $jamKeluar = strlen($request->jam_keluar) === 5 ? $request->jam_keluar . ':00' : $request->jam_keluar;

        $pengaturan->id_admin     = auth('admin')->user()->id_admin;
        $pengaturan->nama_lokasi  = $request->nama_lokasi;
        $pengaturan->latitude     = $request->latitude;
        $pengaturan->longitude    = $request->longitude;
        $pengaturan->radius_meter = $request->radius_meter;
        $pengaturan->jam_masuk    = $jamMasuk;
        $pengaturan->jam_keluar   = $jamKeluar;
        $pengaturan->save();

        // Catat ke log aktivitas admin
        LogAktivitasService::catat(
            idAdmin: auth('admin')->user()->id_admin,
            aktivitas: 'Ubah Pengaturan',
            tabelTerkait: 'pengaturan',
            keterangan: "Mengubah lokasi kantor '{$request->nama_lokasi}' (Lat: {$request->latitude}, Lng: {$request->longitude}, Radius: {$request->radius_meter}m, Jam Masuk: {$request->jam_masuk}, Jam Keluar: {$request->jam_keluar})"
        );

        return redirect()->route('admin.pengaturan.index')
            ->with('sukses', 'Pengaturan kantor dan batas presensi berhasil diperbarui!');
    }
}

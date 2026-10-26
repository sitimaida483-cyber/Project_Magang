<?php

namespace App\Http\Controllers\Anggota;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\PerangkatNotifikasi;
use App\Services\NotifikasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * NotifikasiController
 * Mengelola langganan (subscription) Web Push perangkat anggota.
 */
class NotifikasiController extends Controller
{
    /**
     * Simpan subscription Web Push perangkat anggota.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'id_anggota'      => 'required|exists:anggota,id_anggota',
            'subscription'    => 'required|array',
            'subscription.endpoint' => 'required|string',
            'jenis_perangkat' => 'nullable|string|max:100',
        ]);

        $anggota = Anggota::findOrFail($request->id_anggota);
        $endpoint = $request->subscription['endpoint'];
        $tokenJson = json_encode($request->subscription);

        // Cari apakah endpoint ini sudah pernah tersimpan
        $perangkat = PerangkatNotifikasi::where('token_notifikasi', 'like', "%{$endpoint}%")->first();

        if ($perangkat) {
            $perangkat->update([
                'id_anggota'        => $anggota->id_anggota,
                'token_notifikasi'  => $tokenJson,
                'jenis_perangkat'   => $request->jenis_perangkat ?? $perangkat->jenis_perangkat,
                'status'            => 'aktif',
            ]);
        } else {
            $perangkat = PerangkatNotifikasi::create([
                'id_anggota'        => $anggota->id_anggota,
                'token_notifikasi'  => $tokenJson,
                'jenis_perangkat'   => $request->jenis_perangkat ?? 'Browser HP/Desktop',
                'status'            => 'aktif',
            ]);
        }

        // Kirim notifikasi sambutan sebagai uji coba pengiriman pertama
        NotifikasiService::kirim(
            perangkat: $perangkat,
            jenisNotifikasi: 'presensi_berhasil',
            judul: 'Presensi Bidtik Polda Kalsel',
            pesan: "Halo {$anggota->nama}, notifikasi pengingat presensi otomatis telah aktif pada perangkat ini.",
            targetUrl: '/presensi'
        );

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Perangkat Anda berhasil didaftarkan untuk menerima pengingat presensi otomatis.',
        ]);
    }
}

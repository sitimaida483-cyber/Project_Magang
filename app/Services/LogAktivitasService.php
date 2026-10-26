<?php

namespace App\Services;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;

/**
 * LogAktivitasService
 * Helper untuk mencatat setiap aksi penting admin ke tabel log_aktivitas.
 */
class LogAktivitasService
{
    /**
     * Catat aktivitas admin ke database.
     *
     * @param int    $idAdmin      ID admin yang melakukan aksi
     * @param string $aktivitas    Deskripsi singkat aksi (mis: "Login Admin")
     * @param string $tabelTerkait Nama tabel yang dipengaruhi (mis: "anggota")
     * @param string $keterangan   Detail tambahan (opsional)
     */
    public static function catat(
        int $idAdmin,
        string $aktivitas,
        string $tabelTerkait = '',
        string $keterangan = ''
    ): void {
        LogAktivitas::create([
            'id_admin'        => $idAdmin,
            'aktivitas'       => $aktivitas,
            'tabel_terkait'   => $tabelTerkait ?: null,
            'keterangan'      => $keterangan ?: null,
            'alamat_ip'       => request()->ip(),
            'waktu_aktivitas' => now(),
        ]);
    }
}

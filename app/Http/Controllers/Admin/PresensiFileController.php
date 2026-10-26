<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * PresensiFileController
 * Menyajikan file foto selfie dan tanda tangan digital secara aman
 * hanya untuk sesi administrator yang telah terautentikasi (mencegah akses publik tanpa izin).
 */
class PresensiFileController extends Controller
{
    /**
     * Tampilkan file foto selfie atau tanda tangan.
     *
     * @param string $type Jenis file: 'foto_selfie' atau 'tanda_tangan'
     * @param string $filename Nama file
     */
    public function show(string $type, string $filename): BinaryFileResponse|Response
    {
        // Validasi tipe folder untuk keamanan direktori transversal
        if (! in_array($type, ['foto_selfie', 'tanda_tangan'])) {
            abort(404, 'Direktori tidak ditemukan.');
        }

        // Hindari karakter path transversal (../)
        $cleanFilename = basename($filename);
        $path = storage_path('app/private_presensi/' . $type . '/' . $cleanFilename);

        if (! file_exists($path)) {
            abort(404, 'File presensi tidak ditemukan.');
        }

        return response()->file($path);
    }
}

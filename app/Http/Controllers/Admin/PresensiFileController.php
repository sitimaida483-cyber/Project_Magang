<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
    public function show(string $type, string $filename): BinaryFileResponse
    {
        // 1. Validasi tipe folder untuk keamanan direktori transversal
        if (! in_array($type, ['foto_selfie', 'tanda_tangan'], true)) {
            abort(404, 'Direktori tidak ditemukan.');
        }

        // 2. Hindari karakter path traversal (../)
        $cleanFilename = basename($filename);
        $path = storage_path('app/private_presensi/' . $type . '/' . $cleanFilename);

        if (! file_exists($path) || ! is_file($path)) {
            abort(404, 'File presensi tidak ditemukan.');
        }

        // 3. Validasi tipe MIME asli file gambar
        $mime = @mime_content_type($path);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

        if (! in_array($mime, $allowedMimes, true)) {
            abort(403, 'Tipe berkas tidak diizinkan.');
        }

        // 4. Sajikan dengan header keamanan ketat
        return response()->file($path, [
            'Content-Type'           => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control'          => 'private, no-cache, no-store, must-revalidate',
            'Pragma'                 => 'no-cache',
            'Expires'                => '0',
        ]);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AnggotaAuth Middleware
 * Melindungi seluruh halaman dan fitur presensi anggota — memastikan
 * hanya anggota aktif yang terautentikasi dapat mengakses sistem.
 */
class AnggotaAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek apakah anggota sudah login melalui guard 'anggota'
        if (! auth('anggota')->check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'sukses' => false,
                    'pesan'  => 'Sesi login telah berakhir. Silakan login kembali.',
                ], 401);
            }

            return redirect()->route('login')
                ->with('info', 'Silakan login terlebih dahulu untuk mengakses sistem presensi.');
        }

        $anggota = auth('anggota')->user();

        // 2. Cek apakah status keanggotaan aktif
        if (! $anggota->isAktif()) {
            auth('anggota')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'sukses' => false,
                    'pesan'  => 'Status keanggotaan Anda Nonaktif. Anda tidak dapat mengakses sistem presensi.',
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', 'Status keanggotaan Anda Nonaktif. Silakan hubungi admin Bidtik.');
        }

        return $next($request);
    }
}


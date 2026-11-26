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

        // 3. Cek batas waktu tidak aktif sesi personel (default 120 menit, dapat diatur via env SESSION_ANGGOTA_LIFETIME)
        $timeoutMinutes = (int) env('SESSION_ANGGOTA_LIFETIME', 120);
        $lastActivity   = $request->session()->get('anggota_last_activity');

        if ($lastActivity && (time() - $lastActivity > $timeoutMinutes * 60)) {
            auth('anggota')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'sukses' => false,
                    'pesan'  => "Sesi Anda telah berakhir karena tidak ada aktivitas selama {$timeoutMinutes} menit. Silakan login kembali.",
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', "Sesi Anda telah berakhir karena tidak ada aktivitas selama {$timeoutMinutes} menit. Silakan login kembali.");
        }

        // Perbarui timestamp aktivitas terakhir
        $request->session()->put('anggota_last_activity', time());

        // 4. Cek apakah personel wajib mengganti kata sandi
        if ($anggota->wajib_ganti_password) {
            if (! $request->routeIs('anggota.password', 'anggota.password.update', 'logout')) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'sukses'   => false,
                        'pesan'    => 'Demi keamanan akun, Anda diwajibkan mengganti kata sandi terlebih dahulu.',
                        'redirect' => route('anggota.password'),
                    ], 403);
                }

                return redirect()->route('anggota.password')
                    ->with('warning', 'Demi keamanan akun, Anda diwajibkan mengganti kata sandi terlebih dahulu sebelum mengakses fitur lainnya.');
            }
        }

        return $next($request);
    }
}


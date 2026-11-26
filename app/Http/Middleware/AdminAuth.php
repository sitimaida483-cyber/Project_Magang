<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AdminAuth Middleware
 * Melindungi semua halaman admin — redirect ke login jika belum login.
 */
class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek apakah admin sudah login via guard 'admin'
        if (! auth('admin')->check()) {
            return redirect()->route('admin.login')
                ->with('info', 'Silakan login terlebih dahulu.');
        }

        $admin = auth('admin')->user();

        // 2. Cek batas waktu tidak aktif sesi admin (default 30 menit, dapat diatur via env SESSION_ADMIN_LIFETIME)
        $timeoutMinutes = (int) env('SESSION_ADMIN_LIFETIME', 30);
        $lastActivity   = $request->session()->get('admin_last_activity');

        if ($lastActivity && (time() - $lastActivity > $timeoutMinutes * 60)) {
            auth('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->with('error', "Sesi Anda telah berakhir karena tidak ada aktivitas selama {$timeoutMinutes} menit. Silakan login kembali.");
        }

        // Perbarui timestamp aktivitas terakhir
        $request->session()->put('admin_last_activity', time());

        // 3. Cek apakah admin wajib mengganti kata sandi
        if ($admin->wajib_ganti_password) {
            if (! $request->routeIs('admin.password', 'admin.password.update', 'admin.logout')) {
                return redirect()->route('admin.password')
                    ->with('warning', 'Demi keamanan akun, Anda diwajibkan mengganti kata sandi terlebih dahulu sebelum mengakses fitur lainnya.');
            }
        }

        return $next($request);
    }
}

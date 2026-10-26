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
        // Cek apakah admin sudah login via guard 'admin'
        if (! auth('admin')->check()) {
            return redirect()->route('admin.login')
                ->with('info', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}

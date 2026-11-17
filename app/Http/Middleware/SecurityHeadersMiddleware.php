<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeadersMiddleware
 * Menambahkan header pengamanan HTTP standar pada seluruh respons web.
 * - X-Content-Type-Options: nosniff (mencegah MIME sniffing)
 * - Referrer-Policy: strict-origin-when-cross-origin
 * - X-Frame-Options: SAMEORIGIN (mengizinkan iframe pada domain yang sama untuk preview PDF surat)
 * - Permissions-Policy: mengizinkan camera dan geolocation hanya untuk domain sendiri (self)
 */
class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Header Pengamanan Dasar
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Permissions Policy: Wajib mengizinkan kamera dan geolocation untuk domain sendiri
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(self), camera=(self), microphone=(), payment=(), usb=()'
        );

        // Content Security Policy (Report-Only mode opsional jika diaktifkan via env)
        if (config('presensi.csp_report_only', false)) {
            $response->headers->set(
                'Content-Security-Policy-Report-Only',
                "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com; img-src 'self' data: blob: https:; font-src 'self' https://cdn.jsdelivr.net; connect-src 'self' https:; frame-src 'self';"
            );
        }

        return $response;
    }
}


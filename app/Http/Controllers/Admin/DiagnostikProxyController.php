<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * DiagnostikProxyController
 * Memungkinkan administrator memeriksa jalur jaringan riil, header reverse proxy,
 * dan IP klien yang terbaca di VPS produksi.
 * Halaman ini HANYA aktif jika DIAGNOSTIK_PROXY_ENABLED=true di .env.
 */
class DiagnostikProxyController extends Controller
{
    public function index(Request $request): View
    {
        // Fitur keamanan: tolak akses jika tidak diaktifkan eksplisit di .env
        if (! config('presensi.diagnostik_proxy_enabled', env('DIAGNOSTIK_PROXY_ENABLED', false))) {
            abort(404, 'Halaman diagnostik proxy dinonaktifkan.');
        }

        $ipTerdeteksi = $request->ip();
        $remoteAddr   = $_SERVER['REMOTE_ADDR'] ?? '-';
        $cfIp         = $request->header('CF-Connecting-IP') ?? '-';
        $xForwardedFor = $request->header('X-Forwarded-For') ?? '-';
        $xRealIp      = $request->header('X-Real-IP') ?? '-';
        $userAgent    = $request->userAgent();

        $trustedConfig = config('trustedproxy.proxies');
        $isWildcard    = $trustedConfig === '*';
        $totalTrusted  = is_array($trustedConfig) ? count($trustedConfig) : ($isWildcard ? 'Wildcard (*)' : 0);

        return view('admin.diagnostik.proxy', compact(
            'ipTerdeteksi',
            'remoteAddr',
            'cfIp',
            'xForwardedFor',
            'xRealIp',
            'userAgent',
            'totalTrusted',
            'isWildcard'
        ));
    }
}


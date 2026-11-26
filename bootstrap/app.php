<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Konfigurasi Trusted Proxies (Cloudflare & Hosting Proxy) via ENV
        $cfIps = [
            '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
            '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
            '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
            '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
            '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
            '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
        ];
        $trustedEnv = env('TRUSTED_PROXIES', 'cloudflare');

        if ($trustedEnv === '*') {
            $middleware->trustProxies(at: '*');
        } elseif (! empty($trustedEnv)) {
            $proxies = [];
            foreach (explode(',', (string) $trustedEnv) as $item) {
                $item = trim($item);
                if (strtolower($item) === 'cloudflare') {
                    $proxies = array_merge($proxies, $cfIps);
                } elseif (! empty($item)) {
                    $proxies[] = $item;
                }
            }
            if (! empty($proxies)) {
                $middleware->trustProxies(at: array_values(array_unique($proxies)));
            }
        }

        // Header Keamanan Global (nosniff, SAMEORIGIN, Permissions-Policy)
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);

        // Autentikasi sesi: memastikan pencabutan sesi di perangkat lain (logoutOtherDevices) efektif
        $middleware->authenticateSessions();

        $middleware->alias([
            'admin.auth'   => \App\Http\Middleware\AdminAuth::class,
            'anggota.auth' => \App\Http\Middleware\AnggotaAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

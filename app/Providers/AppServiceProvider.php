<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Konfigurasi UncompromisedVerifier dengan timeout singkat (3 detik)
        // dan penanganan graceful agar form tidak gagal jika layanan luar tidak terjangkau.
        $this->app->singleton(\Illuminate\Contracts\Validation\UncompromisedVerifier::class, function ($app) {
            return new \Illuminate\Validation\NotPwnedVerifier($app['http.factory'], timeout: 3);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Carbon::setLocale('id');

        // Paksa skema HTTPS saat berada di environment production atau saat APP_URL menggunakan HTTPS
        if (app()->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}

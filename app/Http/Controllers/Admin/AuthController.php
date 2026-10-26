<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Services\LogAktivitasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * AuthController — menangani login dan logout admin.
 */
class AuthController extends Controller
{
    /**
     * Tampilkan halaman form login.
     */
    public function showLogin()
    {
        // Jika sudah login, langsung ke dashboard
        if (auth('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Proses login admin dengan proteksi brute-force.
     * Maksimal 5 kali percobaan per menit per IP.
     */
    public function login(LoginRequest $request)
    {
        // Kunci throttle berdasarkan username + IP
        $throttleKey = Str::lower($request->username) . '|' . $request->ip();

        // Cek apakah sudah melebihi batas percobaan
        if (RateLimiter::tooManyAttempts($throttleKey, maxAttempts: 5)) {
            $detik = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'username' => "Terlalu banyak percobaan login. Coba lagi dalam {$detik} detik.",
            ])->withInput($request->only('username'));
        }

        // Coba autentikasi menggunakan guard 'admin'
        $credentials = $request->only('username', 'password');

        if (auth('admin')->attempt($credentials, $request->boolean('ingat_saya'))) {
            // Login berhasil — reset throttle dan regenerasi session
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            // Catat log aktivitas login
            $admin = auth('admin')->user();
            LogAktivitasService::catat(
                idAdmin: $admin->id_admin,
                aktivitas: 'Login Admin',
                tabelTerkait: 'admin',
                keterangan: "Admin '{$admin->username}' berhasil login."
            );

            return redirect()->intended(route('admin.dashboard'))
                ->with('sukses', 'Selamat datang, ' . $admin->nama . '!');
        }

        // Login gagal — tambah hitungan throttle
        RateLimiter::hit($throttleKey, decay: 60); // 60 detik

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->withInput($request->only('username'));
    }

    /**
     * Proses logout admin.
     */
    public function logout(Request $request)
    {
        $admin = auth('admin')->user();

        if ($admin) {
            // Catat log aktivitas logout sebelum session dihapus
            LogAktivitasService::catat(
                idAdmin: $admin->id_admin,
                aktivitas: 'Logout Admin',
                tabelTerkait: 'admin',
                keterangan: "Admin '{$admin->username}' logout dari sistem."
            );
        }

        // Logout dari guard admin
        auth('admin')->logout();

        // Hapus session dan regenerasi token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('sukses', 'Anda berhasil logout.');
    }
}

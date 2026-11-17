<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * AuthController — menangani login, logout, dan ubah password mandiri admin.
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
     * Proses login admin dengan proteksi brute-force 2 lapis:
     * - Lapis 1: IP + Username (maks 5 kali percobaan per 60 detik)
     * - Lapis 2: Username saja (maks 20 kali percobaan per 10 menit)
     */
    public function login(LoginRequest $request)
    {
        $throttleKeyIp   = Str::lower($request->username) . '|' . $request->ip();
        $throttleKeyUser = 'admin_user:' . Str::lower($request->username);

        // Cek apakah terkena rem brute-force lapis 1 (IP + Username)
        if (RateLimiter::tooManyAttempts($throttleKeyIp, maxAttempts: 5)) {
            $detik = RateLimiter::availableIn($throttleKeyIp);
            return back()->withErrors([
                'username' => "Terlalu banyak percobaan login. Coba lagi dalam {$detik} detik.",
            ])->withInput($request->only('username'));
        }

        // Cek apakah terkena rem brute-force lapis 2 (Username saja)
        if (RateLimiter::tooManyAttempts($throttleKeyUser, maxAttempts: 20)) {
            $detik = RateLimiter::availableIn($throttleKeyUser);
            return back()->withErrors([
                'username' => "Terlalu banyak percobaan login untuk akun ini. Coba lagi dalam {$detik} detik.",
            ])->withInput($request->only('username'));
        }

        // Coba autentikasi menggunakan guard 'admin'
        $credentials = $request->only('username', 'password');

        if (auth('admin')->attempt($credentials, $request->boolean('ingat_saya'))) {
            // Login berhasil — reset seluruh throttle dan regenerasi session
            RateLimiter::clear($throttleKeyIp);
            RateLimiter::clear($throttleKeyUser);
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

        // Login gagal — catat percobaan gagal di kedua lapis
        RateLimiter::hit($throttleKeyIp, 60);       // 60 detik
        RateLimiter::hit($throttleKeyUser, 600);    // 10 menit (600 detik)

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->withInput($request->only('username'));
    }

    /**
     * Tampilkan halaman ubah password mandiri admin.
     */
    public function showUbahPassword(): View
    {
        $admin = auth('admin')->user();
        return view('admin.auth.password', compact('admin'));
    }

    /**
     * Proses ubah password mandiri admin.
     */
    public function ubahPassword(Request $request): RedirectResponse
    {
        $admin = auth('admin')->user();

        $request->validate([
            'password_lama' => ['required', 'string'],
            'password'      => [
                'required',
                'string',
                'confirmed',
                Password::min(10)
                    ->mixedCase()
                    ->numbers()
                    ->uncompromised(),
            ],
        ], [
            'password_lama.required' => 'Password saat ini wajib diisi.',
            'password.required'      => 'Password baru wajib diisi.',
            'password.confirmed'     => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (! Hash::check($request->password_lama, $admin->password)) {
            return back()->with('error', 'Password saat ini yang Anda masukkan salah.');
        }

        $admin->update([
            'password' => $request->password, // Otomatis di-hash oleh cast 'hashed' pada model
        ]);

        LogAktivitasService::catat(
            idAdmin: $admin->id_admin,
            aktivitas: 'Ubah Password Admin',
            tabelTerkait: 'admin',
            keterangan: "Admin '{$admin->username}' memperbarui password akunnya."
        );

        return back()->with('sukses', 'Password akun Administrator berhasil diperbarui.');
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

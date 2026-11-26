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
            $request->session()->put('admin_last_activity', time());

            // Catat log aktivitas login
            $admin = auth('admin')->user();
            LogAktivitasService::catat(
                idAdmin: $admin->id_admin,
                aktivitas: 'Login Admin',
                tabelTerkait: 'admin',
                keterangan: "Admin '{$admin->username}' berhasil login."
            );

            // Jika wajib ganti password, arahkan langsung ke formulir ganti sandi
            if ($admin->wajib_ganti_password) {
                return redirect()->route('admin.password')
                    ->with('warning', 'Demi keamanan, Anda diwajibkan mengganti kata sandi terlebih dahulu sebelum mengakses halaman lain.');
            }

            return redirect()->intended(route('admin.dashboard'))
                ->with('sukses', 'Selamat datang, ' . $admin->nama . '!');
        }

        // Login gagal — catat percobaan gagal di kedua lapis
        RateLimiter::hit($throttleKeyIp, 60);       // 60 detik
        RateLimiter::hit($throttleKeyUser, 600);    // 10 menit (600 detik)

        // Catat login gagal ke tabel log_aktivitas (hanya username & alasan, tidak mencatat sandi)
        $adminTarget = \App\Models\Admin::where('username', $request->username)->first();
        $idAdminLog  = $adminTarget?->id_admin ?? \App\Models\Admin::value('id_admin');
        $alasan      = $adminTarget ? "Password tidak sesuai untuk username '{$request->username}'" : "Username tidak terdaftar: '{$request->username}'";

        if ($idAdminLog) {
            LogAktivitasService::catat(
                idAdmin: $idAdminLog,
                aktivitas: 'Login Admin Gagal',
                tabelTerkait: 'admin',
                keterangan: "Percobaan login admin gagal dari IP {$request->ip()}. Alasan: {$alasan}."
            );
        } else {
            \Illuminate\Support\Facades\Log::warning('Login admin gagal', [
                'username'  => $request->username,
                'alamat_ip' => $request->ip(),
                'alasan'    => $alasan,
            ]);
        }

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
                Password::min(12)
                    ->max(128)
                    ->mixedCase()
                    ->numbers()
                    ->uncompromised(),
                new \App\Rules\TidakMemuatDataPengguna([
                    $admin->username,
                    $admin->nama,
                ]),
            ],
        ], [
            'password_lama.required' => 'Password saat ini wajib diisi.',
            'password.required'      => 'Password baru wajib diisi.',
            'password.min'           => 'Password admin minimal harus :min karakter.',
            'password.max'           => 'Password admin maksimal :max karakter.',
            'password.mixed'         => 'Password baru wajib mengombinasikan huruf besar (kapital) dan huruf kecil.',
            'password.numbers'       => 'Password baru wajib mengandung setidaknya satu angka (0-9).',
            'password.uncompromised' => 'Password yang dipilih terdeteksi pernah mengalami kebocoran data publik. Silakan gunakan password lain.',
            'password.confirmed'     => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (! Hash::check($request->password_lama, $admin->password)) {
            return back()->with('error', 'Password saat ini yang Anda masukkan salah.');
        }

        $admin->update([
            'password'             => $request->password, // Otomatis di-hash oleh cast 'hashed' pada model
            'wajib_ganti_password' => false,              // Kewajiban ganti password selesai
        ]);

        // Cabut sesi di perangkat lain milik akun admin ini (sesi saat ini tetap aktif)
        auth('admin')->logoutOtherDevices($request->password);

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

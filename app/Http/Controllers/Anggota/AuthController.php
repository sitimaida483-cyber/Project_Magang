<?php

namespace App\Http\Controllers\Anggota;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * AuthController (Sisi Anggota)
 * Menangani alur login, logout, dan ubah password mandiri personel Bidtik Polda Kalsel
 * menggunakan guard 'anggota' dengan proteksi brute force rate limiting.
 */
class AuthController extends Controller
{
    /**
     * Tampilkan formulir login anggota.
     */
    public function showLogin(): View|RedirectResponse
    {
        // Jika anggota sudah login, langsung arahkan ke halaman presensi
        if (auth('anggota')->check()) {
            return redirect()->route('presensi.index');
        }

        return view('anggota.auth.login');
    }

    /**
     * Proses autentikasi login anggota.
     */
    public function login(Request $request): RedirectResponse
    {
        // 1. Validasi input
        $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Email kedinasan wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // 2. Rate Limiting Brute-Force (Maksimal 5 percobaan per menit per email & IP)
        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withInput($request->only('email'))
                ->with('error', "Terlalu banyak percobaan login yang gagal. Silakan coba kembali dalam {$seconds} detik.");
        }

        // 3. Cari anggota berdasarkan email
        $anggota = Anggota::where('email', $request->email)->first();

        // 4. Verifikasi kredensial (pesan error umum untuk keamanan)
        if (! $anggota || ! Hash::check($request->password, $anggota->password)) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withInput($request->only('email'))
                ->with('error', 'Email atau password salah.');
        }

        // 5. Cek keaktifan status keanggotaan
        if (! $anggota->isAktif()) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withInput($request->only('email'))
                ->with('error', 'Status keanggotaan Anda Nonaktif. Akses presensi dinonaktifkan.');
        }

        // 6. Login berhasil: bersihkan rate limiter & regenerate session
        RateLimiter::clear($throttleKey);

        auth('anggota')->login($anggota, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('presensi.index'))
            ->with('sukses', "Selamat datang, {$anggota->nama}! Silakan lakukan presensi kehadiran.");
    }

    /**
     * Proses keluar (logout) anggota.
     */
    public function logout(Request $request): RedirectResponse
    {
        auth('anggota')->logout();

        // Invalidate session tanpa mengganggu sesi admin jika berada di browser terpisah
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('sukses', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Tampilkan formulir ubah password mandiri anggota.
     */
    public function showUbahPassword(): View
    {
        $anggota = auth('anggota')->user();
        return view('anggota.auth.password', compact('anggota'));
    }

    /**
     * Proses pembaruan password mandiri anggota.
     */
    public function ubahPassword(Request $request): RedirectResponse
    {
        $anggota = auth('anggota')->user();

        $request->validate([
            'password_lama' => ['required', 'string'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password_lama.required' => 'Password saat ini wajib diisi.',
            'password.required'      => 'Password baru wajib diisi.',
            'password.min'           => 'Password baru minimal harus 8 karakter.',
            'password.confirmed'     => 'Konfirmasi password baru tidak cocok.',
        ]);

        // Cek kecocokan password lama
        if (! Hash::check($request->password_lama, $anggota->password)) {
            return back()->with('error', 'Password saat ini yang Anda masukkan salah.');
        }

        // Simpan password baru
        $anggota->update([
            'password' => $request->password, // Otomatis di-hash oleh cast 'hashed' pada model
        ]);

        return back()->with('sukses', 'Password akun Anda berhasil diperbarui.');
    }
}


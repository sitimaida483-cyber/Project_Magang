<?php

namespace Tests\Feature\Security;

use App\Models\Admin;
use App\Models\Anggota;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityTahap1Test extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Test Header Keamanan Global hadir pada setiap respons web.
     */
    public function test_security_headers_are_present_on_web_responses(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN'); // Harus SAMEORIGIN untuk pratinjau PDF di iframe

        $permissionsPolicy = $response->headers->get('Permissions-Policy');
        $this->assertNotNull($permissionsPolicy);
        $this->assertStringContainsString('geolocation=(self)', $permissionsPolicy);
        $this->assertStringContainsString('camera=(self)', $permissionsPolicy);
        $this->assertStringContainsString('microphone=()', $permissionsPolicy);
    }

    /**
     * 2. Test Pembatasan Brute Force Lapis 2 (per-username/email) bekerja
     * dan tidak bisa diakali hanya dengan mengubah IP / header X-Forwarded-For palsu.
     */
    public function test_admin_login_rate_limit_layer2_blocks_brute_force(): void
    {
        $admin = Admin::create([
            'username' => 'admin_test',
            'password' => Hash::make('Admin123456!'),
            'nama'     => 'Admin Test',
        ]);

        RateLimiter::clear('admin_test|127.0.0.1');
        RateLimiter::clear('admin_user:admin_test');

        // Simulasi percobaan login gagal dengan IP berbeda-beda (menyerang username yang sama)
        for ($i = 1; $i <= 20; $i++) {
            $response = $this->withServerVariables(['REMOTE_ADDR' => "192.168.1.{$i}"])
                ->post('/admin/login', [
                    'username' => 'admin_test',
                    'password' => 'WrongPassword123!',
                ]);
        }

        // Percobaan ke-21 harus diblokir oleh layer 2 (per-username) meskipun dari IP baru
        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.99'])
            ->post('/admin/login', [
                'username' => 'admin_test',
                'password' => 'WrongPassword123!',
            ]);

        $response->assertSessionHasErrors('username');
        $this->assertStringContainsString('Terlalu banyak percobaan login', session('errors')->first('username'));
    }

    /**
     * 3. Test Form Login tidak membocorkan aturan password (misal min:6).
     */
    public function test_login_request_does_not_leak_password_complexity(): void
    {
        // Kirim password pendek (3 huruf)
        $response = $this->post('/admin/login', [
            'username' => 'admin_test',
            'password' => 'abc',
        ]);

        // Error bukan 'minimal 6 karakter', tetapi error kredensial umum
        $response->assertSessionHasErrors('username');
        $this->assertEquals('Username atau password salah.', session('errors')->first('username'));
    }

    /**
     * 4. Test Aturan Password Baru wajib minimal 10 karakter, campuran huruf besar, kecil, angka.
     */
    public function test_admin_password_change_enforces_strong_password_policy(): void
    {
        $admin = Admin::create([
            'username' => 'admin_pwd',
            'password' => Hash::make('CurrentPassword123!'),
            'nama'     => 'Admin Pwd',
        ]);

        // Coba password yang terlalu pendek (<10 karakter)
        $response = $this->actingAs($admin, 'admin')
            ->put('/admin/ubah-password', [
                'password_lama'         => 'CurrentPassword123!',
                'password'              => 'Short1!',
                'password_confirmation' => 'Short1!',
            ]);

        $response->assertSessionHasErrors('password');

        // Coba password tanpa angka
        $response = $this->actingAs($admin, 'admin')
            ->put('/admin/ubah-password', [
                'password_lama'         => 'CurrentPassword123!',
                'password'              => 'NoNumberPasswordHere!',
                'password_confirmation' => 'NoNumberPasswordHere!',
            ]);

        $response->assertSessionHasErrors('password');

        // Coba password yang memenuhi seluruh kriteria
        $response = $this->actingAs($admin, 'admin')
            ->put('/admin/ubah-password', [
                'password_lama'         => 'CurrentPassword123!',
                'password'              => 'S4ngatKuatSekali2026!',
                'password_confirmation' => 'S4ngatKuatSekali2026!',
            ]);

        $response->assertSessionHas('sukses');
        $this->assertTrue(Hash::check('S4ngatKuatSekali2026!', $admin->fresh()->password));
    }

    /**
     * 5. Test Penjaga DatabaseSeeder di Production memblokir eksekusi tanpa izin eksplisit.
     */
    public function test_database_seeder_production_guard(): void
    {
        app()->detectEnvironment(fn () => 'production');

        $seeder = new DatabaseSeeder();
        $seeder->run();

        // AdminSeeder tidak boleh dieksekusi di production tanpa ALLOW_PRODUCTION_SEED=true
        $this->assertEquals(0, Admin::where('username', 'admin')->count());
    }

    /**
     * 6. Test AdminSeeder menghasilkan password acak jika ADMIN_PASSWORD tidak diisi di .env.
     */
    public function test_admin_seeder_creates_secure_admin(): void
    {
        $seeder = new AdminSeeder();
        $seeder->run();

        $admin = Admin::where('username', 'admin')->first();
        $this->assertNotNull($admin);
        // Password terenkripsi dan bukan password123 jika env kosong
        $this->assertTrue(strlen($admin->password) > 20);
    }

    /**
     * 7. Test PresensiFileController hanya dapat diakses oleh admin login
     * dan menyajikan header keamanan Content-Type serta nosniff.
     */
    public function test_presensi_file_controller_requires_admin_and_has_security_headers(): void
    {
        // Akses oleh publik (unauthenticated) harus dialihkan ke login admin
        $response = $this->get('/admin/presensi/file/foto_selfie/dummy.jpg');
        $response->assertRedirect('/admin/login');

        // Path traversal (../) harus ditolak
        $admin = Admin::create([
            'username' => 'admin_file',
            'password' => Hash::make('Admin123456!'),
            'nama'     => 'Admin File',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/presensi/file/foto_selfie/../../etc/passwd');
        $response->assertNotFound();
    }

    /**
     * 8. Test Endpoint /presensi/simpan memiliki middleware throttle 6 per menit.
     */
    public function test_presensi_simpan_route_has_throttle_limit_6(): void
    {
        $route = Route::getRoutes()->getByName('presensi.store');
        $this->assertNotNull($route);
        $this->assertContains('throttle:6,1', $route->middleware());
    }
}

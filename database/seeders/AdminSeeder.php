<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeder untuk membuat akun admin awal secara aman via environment variable.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $username = env('ADMIN_USERNAME', 'admin');
        $password = env('ADMIN_PASSWORD');
        $isRandom = false;

        if (empty($password)) {
            // Hasilkan password acak berkekuatan tinggi jika tidak ditentukan di .env
            $password = Str::password(16, letters: true, numbers: true, symbols: true, spaces: false);
            $isRandom = true;
        }

        // Cek jika admin sudah ada (idempotent)
        $admin = Admin::where('username', $username)->first();

        if ($admin) {
            $this->command?->info("Akun admin [{$username}] sudah ada di database. Melewati pembuatan.");
            return;
        }

        Admin::create([
            'username' => $username,
            'password' => Hash::make($password),
            'nama'     => env('ADMIN_NAME', 'Administrator Bidtik'),
        ]);

        $this->command?->info('--------------------------------------------------');
        $this->command?->info('Akun Administrator Berhasil Dibuat:');
        $this->command?->info("Username : {$username}");
        if ($isRandom) {
            $this->command?->warn("Password Acak : {$password}");
            $this->command?->warn('PERINGATAN: Catat password ini sekarang! Password ini tidak akan ditampilkan lagi.');
        } else {
            $this->command?->info('Password : [Sesuai nilai ADMIN_PASSWORD pada .env]');
        }
        $this->command?->info('--------------------------------------------------');
    }
}

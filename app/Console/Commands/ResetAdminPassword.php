<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * ResetAdminPassword
 * Command artisan untuk menyetel atau mereset password akun admin
 * secara aman baik di lingkungan lokal maupun VPS.
 */
class ResetAdminPassword extends Command
{
    /**
     * Signature command:
     * php artisan admin:reset-password {username=admin} {password?}
     */
    protected $signature = 'admin:reset-password 
                            {username=admin : Username admin yang akan direset} 
                            {password? : Password baru (opsional, jika kosong akan diminta atau dibuatkan)}';

    /**
     * Deskripsi command artisan.
     */
    protected $description = 'Reset atau setel password akun administrator secara aman via CLI';

    public function handle(): int
    {
        $username = (string) $this->argument('username');
        $admin = Admin::where('username', $username)->first();

        if (! $admin) {
            $this->error("Akun admin dengan username [{$username}] tidak ditemukan di database.");
            
            $daftarAdmin = Admin::pluck('username')->implode(', ');
            if (! empty($daftarAdmin)) {
                $this->line("Username admin yang tersedia: {$daftarAdmin}");
            }
            return self::FAILURE;
        }

        $password = $this->argument('password');

        if (empty($password)) {
            $password = $this->secret('Masukkan password baru (minimal 8 karakter)') 
                ?: $this->ask('Masukkan password baru');
        }

        if (empty($password)) {
            // Jika tetap kosong, buatkan password acak yang kuat
            $password = Str::random(12);
            $this->warn("Password kosong, sistem membuatkan password acak baru: {$password}");
        }

        if (strlen($password) < 8) {
            $this->error('Password baru minimal harus 8 karakter.');
            return self::FAILURE;
        }

        $admin->password = Hash::make($password);
        $admin->save();

        $this->info('====================================================');
        $this->info("Password untuk admin [{$username}] BERHASIL direset!");
        $this->info("Nama     : {$admin->nama}");
        $this->info("Username : {$username}");
        $this->info("Password : {$password}");
        $this->info('====================================================');

        return self::SUCCESS;
    }
}

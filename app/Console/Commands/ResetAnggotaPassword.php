<?php

namespace App\Console\Commands;

use App\Models\Anggota;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * ResetAnggotaPassword
 * Command artisan untuk menyetel atau mereset password akun anggota/personel
 * secara aman baik di lingkungan lokal maupun VPS menggunakan NRP atau Email.
 */
class ResetAnggotaPassword extends Command
{
    /**
     * Signature command:
     * php artisan anggota:reset-password {identifier : NRP atau Email anggota} {password? : Password baru}
     */
    protected $signature = 'anggota:reset-password 
                            {identifier : NRP atau Email anggota yang akan direset} 
                            {password? : Password baru (opsional, jika kosong akan diminta atau dibuatkan)}';

    /**
     * Deskripsi command artisan.
     */
    protected $description = 'Reset atau setel password akun anggota/personel via CLI menggunakan NRP atau Email';

    public function handle(): int
    {
        $identifier = trim((string) $this->argument('identifier'));
        
        $anggota = Anggota::where('nrp', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (! $anggota) {
            $this->error("Anggota dengan NRP/Email [{$identifier}] tidak ditemukan.");
            return self::FAILURE;
        }

        $password = $this->argument('password');

        if (empty($password)) {
            $password = $this->secret('Masukkan password baru (minimal 8 karakter)') 
                ?: $this->ask('Masukkan password baru');
        }

        if (empty($password)) {
            $password = Str::random(12);
            $this->warn("Password kosong, sistem membuatkan password acak baru: {$password}");
        }

        if (strlen($password) < 8) {
            $this->error('Password baru minimal harus 8 karakter.');
            return self::FAILURE;
        }

        // Jika anggota belum memiliki email, setel email default berbasis NRP
        if (empty($anggota->email)) {
            $anggota->email = $anggota->nrp . '@bidtik.polri.go.id';
        }

        $anggota->password = Hash::make($password);
        $anggota->save();

        $this->info('====================================================');
        $this->info("Password untuk anggota [{$anggota->nama}] BERHASIL direset!");
        $this->info("NRP      : {$anggota->nrp}");
        $this->info("Nama     : {$anggota->nama}");
        $this->info("Email    : {$anggota->email}");
        $this->info("Password : {$password}");
        $this->info('====================================================');

        return self::SUCCESS;
    }
}

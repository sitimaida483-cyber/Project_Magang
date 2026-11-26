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
            $password = $this->secret('Masukkan password baru (minimal 12 karakter, campuran huruf besar, kecil, dan angka)') 
                ?: $this->ask('Masukkan password baru');
        }

        if (empty($password)) {
            $this->error('Password baru tidak boleh kosong.');
            return self::FAILURE;
        }

        // Validasi aturan password admin
        $validator = \Illuminate\Support\Facades\Validator::make(['password' => $password], [
            'password' => [
                'required',
                'string',
                \Illuminate\Validation\Rules\Password::min(12)
                    ->max(128)
                    ->mixedCase()
                    ->numbers(),
                new \App\Rules\TidakMemuatDataPengguna([
                    $admin->username,
                    $admin->nama,
                ]),
            ],
        ], [
            'password.min' => 'Password admin minimal harus 12 karakter.',
            'password.max' => 'Password admin maksimal 128 karakter.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $err) {
                $this->error($err);
            }
            return self::FAILURE;
        }

        $admin->password = Hash::make($password);
        $admin->wajib_ganti_password = true;
        $admin->save();

        \App\Services\LogAktivitasService::catat(
            idAdmin: $admin->id_admin,
            aktivitas: 'Reset Password Admin',
            tabelTerkait: 'admin',
            keterangan: "Password admin '{$admin->username}' direset via CLI Artisan."
        );

        $this->info('====================================================');
        $this->info("Password untuk admin [{$username}] BERHASIL direset!");
        $this->info("Nama                 : {$admin->nama}");
        $this->info("Username             : {$username}");
        $this->info("Status Keamanan      : Wajib Ganti Password Diaktifkan");
        $this->info("Keterangan           : Password telah tersimpan dengan aman");
        $this->info('====================================================');

        return self::SUCCESS;
    }
}

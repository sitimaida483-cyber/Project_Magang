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
                            {password? : Password baru (opsional, jika kosong akan diminta atau dibuatkan)}
                            {--email= : Perbarui alamat email anggota}
                            {--status= : Perbarui status keanggotaan (aktif/nonaktif)}';

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
            $password = $this->secret('Masukkan password baru (minimal 10 karakter, campuran huruf besar, kecil, dan angka)') 
                ?: $this->ask('Masukkan password baru');
        }

        if (empty($password)) {
            $this->error('Password baru tidak boleh kosong.');
            return self::FAILURE;
        }

        // Validasi aturan password personel
        $validator = \Illuminate\Support\Facades\Validator::make(['password' => $password], [
            'password' => [
                'required',
                'string',
                \Illuminate\Validation\Rules\Password::min(10)
                    ->max(128)
                    ->mixedCase()
                    ->numbers(),
                new \App\Rules\TidakMemuatDataPengguna([
                    $anggota->nrp,
                    $anggota->nama,
                    $anggota->email,
                ]),
            ],
        ], [
            'password.min' => 'Password personel minimal harus 10 karakter.',
            'password.max' => 'Password personel maksimal 128 karakter.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $err) {
                $this->error($err);
            }
            return self::FAILURE;
        }

        // Perbarui email jika opsi --email disertakan
        if ($this->option('email')) {
            $anggota->email = trim((string) $this->option('email'));
        } elseif (empty($anggota->email)) {
            $anggota->email = $anggota->nrp . '@bidtik.polri.go.id';
        }

        // Perbarui status jika opsi --status disertakan, atau pastikan aktif
        if ($this->option('status')) {
            $anggota->status = $this->option('status');
        } elseif ($anggota->status !== 'aktif') {
            $anggota->status = 'aktif';
        }

        $anggota->password = Hash::make($password);
        $anggota->wajib_ganti_password = true;
        $anggota->save();

        \Illuminate\Support\Facades\Log::info('Password personel direset via CLI', [
            'id_anggota' => $anggota->id_anggota,
            'nrp'        => $anggota->nrp,
            'email'      => $anggota->email,
        ]);

        $this->info('====================================================');
        $this->info("Akun anggota [{$anggota->nama}] BERHASIL diperbarui!");
        $this->info("NRP                  : {$anggota->nrp}");
        $this->info("Nama                 : {$anggota->nama}");
        $this->info("Email                : {$anggota->email}");
        $this->info("Status               : {$anggota->status}");
        $this->info("Status Keamanan      : Wajib Ganti Password Diaktifkan");
        $this->info("Keterangan           : Password telah tersimpan dengan aman");
        $this->info('====================================================');

        return self::SUCCESS;
    }
}

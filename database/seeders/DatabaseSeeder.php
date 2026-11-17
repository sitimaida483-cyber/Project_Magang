<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder utama — menjalankan semua seeder secara berurutan.
 * Dilengkapi penjaga environment production untuk mencegah penimpaan data.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Penjaga keamanan di production
        if (app()->environment('production') && ! env('ALLOW_PRODUCTION_SEED', false)) {
            $this->command?->error('EKSEKUSI SEEDER DIBATALKAN: Menjalankan seeder di environment production diblokir demi keamanan data.');
            $this->command?->warn('Jika benar-benar diperlukan untuk inisialisasi awal, setel ALLOW_PRODUCTION_SEED=true di .env.');
            $this->command?->error('PERHATIAN: JANGAN PERNAH menjalankan "migrate:fresh" di production karena akan menghapus seluruh data presensi!');
            return;
        }

        $this->call([
            AdminSeeder::class,      // 1. Buat akun admin awal (bisa di production bila ALLOW_PRODUCTION_SEED=true)
            PengaturanSeeder::class, // 2. Buat pengaturan kantor / koordinat default
        ]);

        // Seeder data contoh hanya berjalan di environment lokal / testing
        if (app()->isLocal() || app()->runningUnitTests()) {
            $this->call([
                AnggotaSeeder::class, // 3. Buat data contoh personel (hanya untuk lokal/testing)
            ]);
        } else {
            $this->command?->info('AnggotaSeeder dilewati karena environment bukan lokal.');
        }
    }
}

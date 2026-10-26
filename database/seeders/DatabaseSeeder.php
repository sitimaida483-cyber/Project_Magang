<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder utama — menjalankan semua seeder secara berurutan.
 * Urutan penting: admin dulu, baru yang lain (ada FK ke admin).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,      // 1. Buat akun admin terlebih dahulu
            PengaturanSeeder::class, // 2. Buat pengaturan lokasi (butuh id_admin)
            AnggotaSeeder::class,    // 3. Buat data anggota (butuh id_admin)
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Pengaturan;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk pengaturan awal — koordinat sekitar Banjarmasin.
 */
class PengaturanSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::first(); // Ambil admin pertama

        Pengaturan::create([
            'id_admin'     => $admin->id_admin,
            'nama_lokasi'  => 'Mako Polda Kalimantan Selatan (Banjarbaru)',
            'latitude'     => -3.4819883,   // Mako Polda Kalsel Banjarbaru (Jl. Bina Praja Timur)
            'longitude'    => 114.8271472,
            'radius_meter' => 100,          // Radius presensi 100 meter
            'jam_masuk'    => '07:30:00',    // Jam masuk pukul 07:30
            'jam_keluar'   => '16:00:00',    // Jam keluar pukul 16:00
        ]);
    }
}

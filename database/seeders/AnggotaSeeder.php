<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Anggota;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk 5 data anggota contoh.
 */
class AnggotaSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::first(); // Ambil admin pertama

        $dataAnggota = [
            [
                'nrp'        => '80010001',
                'nama'       => 'Bripda Ahmad Rizki',
                'jabatan'    => 'Bintara',
                'unit_kerja' => 'Bidtik',
                'no_telepon' => '081234567801',
                'status'     => 'aktif',
            ],
            [
                'nrp'        => '80010002',
                'nama'       => 'Bripda Siti Rahayu',
                'jabatan'    => 'Bintara',
                'unit_kerja' => 'Bidtik',
                'no_telepon' => '081234567802',
                'status'     => 'aktif',
            ],
            [
                'nrp'        => '80010003',
                'nama'       => 'Briptu Dedi Kurniawan',
                'jabatan'    => 'Bintara Tingkat I',
                'unit_kerja' => 'Bidtik',
                'no_telepon' => '081234567803',
                'status'     => 'aktif',
            ],
            [
                'nrp'        => '80010004',
                'nama'       => 'Aipda Hendra Susanto',
                'jabatan'    => 'Bintara Madya',
                'unit_kerja' => 'Bidtik',
                'no_telepon' => '081234567804',
                'status'     => 'aktif',
            ],
            [
                'nrp'        => '80010005',
                'nama'       => 'Aiptu Wulandari',
                'jabatan'    => 'Bintara Tingkat I',
                'unit_kerja' => 'Bidtik',
                'no_telepon' => '081234567805',
                'status'     => 'nonaktif', // Contoh anggota nonaktif
            ],
        ];

        foreach ($dataAnggota as $data) {
            Anggota::create(array_merge($data, ['id_admin' => $admin->id_admin]));
        }
    }
}

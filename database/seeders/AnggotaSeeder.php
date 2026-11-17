<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Anggota;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder untuk 5 data anggota contoh (hanya untuk pengujian di environment lokal).
 */
class AnggotaSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->isLocal() && ! app()->runningUnitTests()) {
            $this->command?->warn('AnggotaSeeder hanya dapat dijalankan di environment lokal/testing.');
            return;
        }

        $admin = Admin::first(); // Ambil admin pertama
        $defaultPassword = Hash::make(env('SEED_ANGGOTA_PASSWORD', 'PresTIK@Kalsel2026'));

        $dataAnggota = [
            [
                'nrp'        => '80010001',
                'nama'       => 'Bripda Ahmad Rizki',
                'email'      => 'ahmad.rizki@bidtik.polri.go.id',
                'password'   => $defaultPassword,
                'jabatan'    => 'Bintara',
                'unit_kerja' => 'Bidtik',
                'no_telepon' => '081234567801',
                'status'     => 'aktif',
            ],
            [
                'nrp'        => '80010002',
                'nama'       => 'Bripda Siti Rahayu',
                'email'      => 'siti.rahayu@bidtik.polri.go.id',
                'password'   => $defaultPassword,
                'jabatan'    => 'Bintara',
                'unit_kerja' => 'Bidtik',
                'no_telepon' => '081234567802',
                'status'     => 'aktif',
            ],
            [
                'nrp'        => '80010003',
                'nama'       => 'Briptu Dedi Kurniawan',
                'email'      => 'dedi.kurniawan@bidtik.polri.go.id',
                'password'   => $defaultPassword,
                'jabatan'    => 'Bintara Tingkat I',
                'unit_kerja' => 'Bidtik',
                'no_telepon' => '081234567803',
                'status'     => 'aktif',
            ],
            [
                'nrp'        => '80010004',
                'nama'       => 'Aipda Hendra Susanto',
                'email'      => 'hendra.susanto@bidtik.polri.go.id',
                'password'   => $defaultPassword,
                'jabatan'    => 'Bintara Madya',
                'unit_kerja' => 'Bidtik',
                'no_telepon' => '081234567804',
                'status'     => 'aktif',
            ],
            [
                'nrp'        => '80010005',
                'nama'       => 'Aiptu Wulandari',
                'email'      => 'wulandari@bidtik.polri.go.id',
                'password'   => $defaultPassword,
                'jabatan'    => 'Bintara Tingkat I',
                'unit_kerja' => 'Bidtik',
                'no_telepon' => '081234567805',
                'status'     => 'nonaktif', // Contoh anggota nonaktif
            ],
        ];

        foreach ($dataAnggota as $data) {
            Anggota::updateOrCreate(
                ['nrp' => $data['nrp']],
                array_merge($data, ['id_admin' => $admin ? $admin->id_admin : 1])
            );
        }
    }
}

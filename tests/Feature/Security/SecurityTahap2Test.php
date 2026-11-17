<?php

namespace Tests\Feature\Security;

use App\Models\Admin;
use App\Models\Anggota;
use App\Models\Pengaturan;
use App\Models\Presensi;
use App\Services\DeteksiAnomaliService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityTahap2Test extends TestCase
{
    use RefreshDatabase;

    private Anggota $anggota;
    private Pengaturan $pengaturan;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = Admin::create([
            'username' => 'admin_test_setup',
            'password' => Hash::make('Admin123456!'),
            'nama'     => 'Admin Setup',
        ]);

        $this->pengaturan = Pengaturan::create([
            'id_admin'       => $admin->id_admin,
            'nama_lokasi'    => 'Kantor Bidtik Polda Kalsel',
            'latitude'       => -3.3194000,
            'longitude'      => 114.5925000,
            'radius_meter'   => 200,
            'jam_masuk'      => '08:00:00',
            'jam_pulang'     => '16:00:00',
            'jam_keluar'     => '17:00:00',
            'toleransi_menit'=> 15,
        ]);

        $this->anggota = Anggota::create([
            'id_admin'   => $admin->id_admin,
            'nrp'        => '80010099',
            'nama'       => 'Bripda Uji Anomali',
            'email'      => 'uji.anomali@bidtik.polri.go.id',
            'password'   => Hash::make('PasswordUji123!'),
            'jabatan'    => 'Bintara',
            'unit_kerja' => 'Bidtik',
            'status'     => 'aktif',
        ]);
    }

    /**
     * 1. Test Aturan A: Jarak sangat kecil (< 1 meter) ditandai anomali.
     */
    public function test_aturan_a_jarak_terlalu_sempurna_ditandai_anomali(): void
    {
        $alasan = DeteksiAnomaliService::evaluasi(
            anggota: $this->anggota,
            pengaturan: $this->pengaturan,
            jenisPresensi: 'masuk',
            userLat: -3.3194000,
            userLng: 114.5925000,
            jarakMeter: 0.5, // Sangat kecil (< 1.0 m)
            akurasiGps: 15.0,
            fotoHash: 'hash_test_1',
            ipAddress: '192.168.1.10',
            tanggalHariIni: '2026-11-16',
            waktuSekarang: '07:30:00'
        );

        $this->assertNotEmpty($alasan);
        $this->assertStringContainsString('luar biasa presisi', $alasan[0]);
    }

    /**
     * 2. Test Aturan B: Akurasi GPS kosong, <= 1m, atau > 150m ditandai anomali.
     */
    public function test_aturan_b_akurasi_gps_abnormal_ditandai_anomali(): void
    {
        // Kasus 1: Akurasi null / tidak ada
        $alasanNull = DeteksiAnomaliService::evaluasi(
            anggota: $this->anggota,
            pengaturan: $this->pengaturan,
            jenisPresensi: 'masuk',
            userLat: -3.3194500,
            userLng: 114.5925500,
            jarakMeter: 50.0,
            akurasiGps: null,
            fotoHash: 'hash_test_2',
            ipAddress: '192.168.1.10',
            tanggalHariIni: '2026-11-16',
            waktuSekarang: '07:30:00'
        );
        $this->assertStringContainsString('tidak dilaporkan', $alasanNull[0]);

        // Kasus 2: Akurasi <= 1.0 meter (terlalu sempurna / mock location)
        $alasanKecil = DeteksiAnomaliService::evaluasi(
            anggota: $this->anggota,
            pengaturan: $this->pengaturan,
            jenisPresensi: 'masuk',
            userLat: -3.3194500,
            userLng: 114.5925500,
            jarakMeter: 50.0,
            akurasiGps: 0.8,
            fotoHash: 'hash_test_2b',
            ipAddress: '192.168.1.10',
            tanggalHariIni: '2026-11-16',
            waktuSekarang: '07:30:00'
        );
        $this->assertStringContainsString('terlalu sempurna', $alasanKecil[0]);

        // Kasus 3: Akurasi > 150 meter (terlalu buruk)
        $alasanBesar = DeteksiAnomaliService::evaluasi(
            anggota: $this->anggota,
            pengaturan: $this->pengaturan,
            jenisPresensi: 'masuk',
            userLat: -3.3194500,
            userLng: 114.5925500,
            jarakMeter: 50.0,
            akurasiGps: 180.0,
            fotoHash: 'hash_test_2c',
            ipAddress: '192.168.1.10',
            tanggalHariIni: '2026-11-16',
            waktuSekarang: '07:30:00'
        );
        $this->assertStringContainsString('sangat rendah', $alasanBesar[0]);
    }

    /**
     * 3. Test Aturan C: Koordinat (6 desimal) identik dengan presensi lain pada hari yang sama.
     */
    public function test_aturan_c_koordinat_identik_pada_hari_sama_ditandai_anomali(): void
    {
        // Buat presensi pertama dengan koordinat tertentu
        Presensi::create([
            'id_anggota'       => $this->anggota->id_anggota,
            'tanggal'          => '2026-11-16',
            'waktu'            => '07:00:00',
            'jenis_presensi'   => 'masuk',
            'foto_selfie'      => 'selfie1.jpg',
            'tanda_tangan'     => '',
            'latitude'         => -3.3194567,
            'longitude'        => 114.5925678,
            'status_kehadiran' => 'tepat_waktu',
        ]);

        // Evaluasi koordinat yang dibulatkan 6 desimal identik
        $alasan = DeteksiAnomaliService::evaluasi(
            anggota: $this->anggota,
            pengaturan: $this->pengaturan,
            jenisPresensi: 'pulang',
            userLat: -3.3194569, // round 6 desimal = -3.319457
            userLng: 114.5925679, // round 6 desimal = 114.592568
            jarakMeter: 50.0,
            akurasiGps: 15.0,
            fotoHash: 'hash_test_3',
            ipAddress: '192.168.1.10',
            tanggalHariIni: '2026-11-16',
            waktuSekarang: '16:30:00'
        );

        $this->assertNotEmpty($alasan);
        $this->assertStringContainsString('identik persis', $alasan[0]);
    }

    /**
     * 4. Test Aturan D: Foto selfie dengan hash SHA-256 yang sama pernah dipakai sebelumnya.
     */
    public function test_aturan_d_foto_hash_duplikat_ditandai_anomali(): void
    {
        $hashFotoBekas = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';

        Presensi::create([
            'id_anggota'       => $this->anggota->id_anggota,
            'tanggal'          => '2026-11-15',
            'waktu'            => '07:30:00',
            'jenis_presensi'   => 'masuk',
            'foto_selfie'      => 'selfie_kemarin.jpg',
            'tanda_tangan'     => '',
            'latitude'         => -3.3194500,
            'longitude'        => 114.5925500,
            'status_kehadiran' => 'tepat_waktu',
            'foto_hash'        => $hashFotoBekas,
        ]);

        $alasan = DeteksiAnomaliService::evaluasi(
            anggota: $this->anggota,
            pengaturan: $this->pengaturan,
            jenisPresensi: 'masuk',
            userLat: -3.3194800,
            userLng: 114.5925800,
            jarakMeter: 50.0,
            akurasiGps: 15.0,
            fotoHash: $hashFotoBekas, // Foto yang sama persis
            ipAddress: '192.168.1.10',
            tanggalHariIni: '2026-11-16',
            waktuSekarang: '07:30:00'
        );

        $this->assertNotEmpty($alasan);
        $this->assertStringContainsString('hash SHA-256 kembar', $alasan[0]);
    }

    /**
     * 5. Test Aturan E: Presensi pulang < 60 menit setelah masuk ditandai anomali.
     */
    public function test_aturan_e_pulang_terlalu_cepat_ditandai_anomali(): void
    {
        Presensi::create([
            'id_anggota'       => $this->anggota->id_anggota,
            'tanggal'          => '2026-11-16',
            'waktu'            => '08:00:00',
            'jenis_presensi'   => 'masuk',
            'foto_selfie'      => 'selfie_masuk.jpg',
            'tanda_tangan'     => '',
            'latitude'         => -3.3194500,
            'longitude'        => 114.5925500,
            'status_kehadiran' => 'tepat_waktu',
        ]);

        // Presensi pulang diajukan hanya 20 menit kemudian (08:20:00)
        $alasan = DeteksiAnomaliService::evaluasi(
            anggota: $this->anggota,
            pengaturan: $this->pengaturan,
            jenisPresensi: 'pulang',
            userLat: -3.3194800,
            userLng: 114.5925800,
            jarakMeter: 50.0,
            akurasiGps: 15.0,
            fotoHash: 'hash_test_5',
            ipAddress: '192.168.1.10',
            tanggalHariIni: '2026-11-16',
            waktuSekarang: '08:20:00'
        );

        $this->assertNotEmpty($alasan);
        $this->assertStringContainsString('kurang dari batas minimum 60 menit', $alasan[0]);
    }

    /**
     * 6. Test Aturan F: Presensi di luar subnet jaringan kantor ditandai anomali.
     */
    public function test_aturan_f_subnet_kantor_menandai_ip_luar(): void
    {
        Config::set('presensi.anomali.subnet_kantor', ['192.168.1.0/24', '10.0.0.0/8']);

        $alasan = DeteksiAnomaliService::evaluasi(
            anggota: $this->anggota,
            pengaturan: $this->pengaturan,
            jenisPresensi: 'masuk',
            userLat: -3.3194500,
            userLng: 114.5925500,
            jarakMeter: 50.0,
            akurasiGps: 15.0,
            fotoHash: 'hash_test_6',
            ipAddress: '203.0.113.50', // IP Publik di luar subnet kantor
            tanggalHariIni: '2026-11-16',
            waktuSekarang: '07:30:00'
        );

        $this->assertNotEmpty($alasan);
        $this->assertStringContainsString('di luar daftar subnet', $alasan[0]);
    }

    /**
     * 7. Test Filter Rekap Presensi Admin untuk status_anomali=perlu_ditinjau.
     */
    public function test_admin_rekap_filter_perlu_ditinjau(): void
    {
        $admin = Admin::create([
            'username' => 'admin_rekap',
            'password' => Hash::make('Admin123456!'),
            'nama'     => 'Admin Rekap',
        ]);

        // Rekaman Normal
        Presensi::create([
            'id_anggota'       => $this->anggota->id_anggota,
            'tanggal'          => '2026-11-16',
            'waktu'            => '07:30:00',
            'jenis_presensi'   => 'masuk',
            'foto_selfie'      => 'selfie_normal.jpg',
            'tanda_tangan'     => '',
            'latitude'         => -3.3194500,
            'longitude'        => 114.5925500,
            'status_kehadiran' => 'tepat_waktu',
            'flag_anomali'     => false,
        ]);

        // Rekaman Anomali
        Presensi::create([
            'id_anggota'       => $this->anggota->id_anggota,
            'tanggal'          => '2026-11-16',
            'waktu'            => '07:45:00',
            'jenis_presensi'   => 'masuk',
            'foto_selfie'      => 'selfie_anomali.jpg',
            'tanda_tangan'     => '',
            'latitude'         => -3.3194500,
            'longitude'        => 114.5925500,
            'status_kehadiran' => 'tepat_waktu',
            'flag_anomali'     => true,
            'catatan_anomali'  => json_encode(['Akurasi GPS tidak dilaporkan']),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/presensi?status_anomali=perlu_ditinjau');

        $response->assertOk();
        $response->assertViewHas('totalPerluDitinjau', 1);
    }
}

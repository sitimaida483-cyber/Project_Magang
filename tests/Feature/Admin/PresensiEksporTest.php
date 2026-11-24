<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Anggota;
use App\Models\Pengaturan;
use App\Models\Presensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PresensiEksporTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'username' => 'admin_ekspor',
            'password' => Hash::make('Password123!'),
            'nama'     => 'Admin Ekspor Uji',
        ]);

        $pengaturan = Pengaturan::create([
            'id_admin'       => $this->admin->id_admin,
            'nama_lokasi'    => 'Kantor Bidtik Polda Kalsel',
            'latitude'       => -3.3194000,
            'longitude'      => 114.5925000,
            'radius_meter'   => 200,
            'jam_masuk'      => '08:00:00',
            'jam_pulang'     => '16:00:00',
            'jam_keluar'     => '17:00:00',
            'toleransi_menit'=> 15,
        ]);

        $anggota = Anggota::create([
            'id_admin'   => $this->admin->id_admin,
            'nrp'        => '80010099',
            'nama'       => 'Bripda Ekspor',
            'email'      => 'ekspor@bidtik.polri.go.id',
            'password'   => Hash::make('Password123!'),
            'jabatan'    => 'Bintara',
            'unit_kerja' => 'Bidtik',
            'status'     => 'aktif',
        ]);

        Presensi::create([
            'id_anggota'       => $anggota->id_anggota,
            'tanggal'          => Carbon::today()->toDateString(),
            'waktu'            => '07:45:00',
            'jenis_presensi'   => 'masuk',
            'foto_selfie'      => 'selfie.jpg',
            'tanda_tangan'     => '',
            'latitude'         => -3.3194000,
            'longitude'        => 114.5925000,
            'status_kehadiran' => 'tepat_waktu',
            'flag_anomali'     => false,
        ]);
    }

    /**
     * Test unduh PDF rekap presensi menghasilkan dokumen PDF resmi dengan Kop Surat Polda Kalsel.
     */
    public function test_ekspor_pdf_rekap_presensi_berhasil_dengan_kop_surat(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.presensi.ekspor_pdf'));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));

        // Render view langsung untuk memeriksa keberadaan elemen kop dan tanggal unduh
        $view = view('admin.presensi.pdf', [
            'dataPresensi'     => Presensi::with('anggota')->get(),
            'pengaturan'       => Pengaturan::first(),
            'periodeTeks'      => 'Semua Periode',
            'waktuCetak'       => Carbon::now('Asia/Makassar')->locale('id')->translatedFormat('d F Y, H:i') . ' WITA',
            'tanggalUnduhTeks' => 'Banjarbaru, ' . Carbon::now('Asia/Makassar')->locale('id')->translatedFormat('d F Y'),
            'logoPolriBase64'  => null,
            'namaAdmin'        => $this->admin->nama,
        ])->render();

        $this->assertStringContainsString('KEPOLISIAN NEGARA REPUBLIK INDONESIA', $view);
        $this->assertStringContainsString('DAERAH KALIMANTAN SELATAN', $view);
        $this->assertStringContainsString('<u>Jalan Bina Praja Timur Sei Tiung Banjarbaru 70734</u>', $view);
        $this->assertStringContainsString('Banjarbaru, ' . Carbon::now('Asia/Makassar')->locale('id')->translatedFormat('d F Y'), $view);
    }

    /**
     * Test unduh Excel rekap presensi menghasilkan stream dokumen spreadsheet.
     */
    public function test_ekspor_excel_rekap_presensi_berhasil(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.presensi.ekspor_excel'));

        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', $response->headers->get('Content-Type'));
    }
}

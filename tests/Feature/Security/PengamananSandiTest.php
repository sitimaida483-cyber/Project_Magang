<?php

namespace Tests\Feature\Security;

use App\Models\Admin;
use App\Models\Anggota;
use App\Models\LogAktivitas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PengamananSandiTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Anggota $anggota;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'username'             => 'admin_keamanan',
            'password'             => Hash::make('Admin123Kalsel#'),
            'nama'                 => 'Admin Penguji Keamanan',
            'wajib_ganti_password' => false,
        ]);

        $this->anggota = Anggota::create([
            'id_admin'             => $this->admin->id_admin,
            'nrp'                  => '80010088',
            'nama'                 => 'Bripda Surya Pratama',
            'email'                => 'surya.pratama@bidtik.polri.go.id',
            'password'             => Hash::make('SuryaBukanSandi1#'),
            'wajib_ganti_password' => false,
            'jabatan'              => 'Bintara',
            'unit_kerja'           => 'Bid TIK',
            'status'               => 'aktif',
        ]);
    }

    /**
     * 1. Validasi sandi personel: menolak kurang dari 10 karakter atau lebih dari 128 karakter.
     */
    public function test_sandi_personel_menolak_kurang_dari_10_atau_lebih_128_karakter(): void
    {
        $this->actingAs($this->anggota, 'anggota');

        // Kurang dari 10 karakter (9 karakter)
        $response = $this->put(route('anggota.password.update'), [
            'password_lama'         => 'SuryaBukanSandi1#',
            'password'              => 'Abc12345!',
            'password_confirmation' => 'Abc12345!',
        ]);
        $response->assertSessionHasErrors('password');

        // Lebih dari 128 karakter
        $responsePanjang = $this->put(route('anggota.password.update'), [
            'password_lama'         => 'SuryaBukanSandi1#',
            'password'              => str_repeat('A1b!', 35), // 140 karakter
            'password_confirmation' => str_repeat('A1b!', 35),
        ]);
        $responsePanjang->assertSessionHasErrors('password');
    }

    /**
     * 2. Validasi sandi personel: wajib huruf besar, huruf kecil, dan angka.
     */
    public function test_sandi_personel_wajib_huruf_besar_kecil_dan_angka(): void
    {
        $this->actingAs($this->anggota, 'anggota');

        // Tanpa huruf besar
        $responseNoUpper = $this->put(route('anggota.password.update'), [
            'password_lama'         => 'SuryaBukanSandi1#',
            'password'              => 'hanyakecil12345!',
            'password_confirmation' => 'hanyakecil12345!',
        ]);
        $responseNoUpper->assertSessionHasErrors('password');

        // Tanpa angka
        $responseNoNum = $this->put(route('anggota.password.update'), [
            'password_lama'         => 'SuryaBukanSandi1#',
            'password'              => 'HurufBesarKecilSaja!',
            'password_confirmation' => 'HurufBesarKecilSaja!',
        ]);
        $responseNoNum->assertSessionHasErrors('password');
    }

    /**
     * 3. Validasi sandi personel: tolak sandi yang memuat nama, NRP, atau email pemilik akun.
     */
    public function test_sandi_personel_menolak_yang_memuat_nama_nrp_atau_email(): void
    {
        $this->actingAs($this->anggota, 'anggota');

        // Memuat kata dalam nama ("surya")
        $resNama = $this->put(route('anggota.password.update'), [
            'password_lama'         => 'SuryaBukanSandi1#',
            'password'              => 'SuryaJuara2026!#',
            'password_confirmation' => 'SuryaJuara2026!#',
        ]);
        $resNama->assertSessionHasErrors('password');

        // Memuat NRP ("80010088")
        $resNrp = $this->put(route('anggota.password.update'), [
            'password_lama'         => 'SuryaBukanSandi1#',
            'password'              => 'Polri80010088#A',
            'password_confirmation' => 'Polri80010088#A',
        ]);
        $resNrp->assertSessionHasErrors('password');

        // Memuat email prefix ("pratama")
        $resEmail = $this->put(route('anggota.password.update'), [
            'password_lama'         => 'SuryaBukanSandi1#',
            'password'              => 'PratamaHebat2026!',
            'password_confirmation' => 'PratamaHebat2026!',
        ]);
        $resEmail->assertSessionHasErrors('password');
    }

    /**
     * 4. Validasi sandi admin: minimal 12 karakter, tolak username atau nama admin.
     */
    public function test_sandi_admin_minimal_12_karakter_dan_tidak_memuat_identitas(): void
    {
        $this->actingAs($this->admin, 'admin');

        // 11 karakter (kurang dari 12 karakter)
        $resPendek = $this->put(route('admin.password.update'), [
            'password_lama'         => 'Admin123Kalsel#',
            'password'              => 'Rahasia12#A',
            'password_confirmation' => 'Rahasia12#A',
        ]);
        $resPendek->assertSessionHasErrors('password');

        // Memuat username ("admin")
        $resUsername = $this->put(route('admin.password.update'), [
            'password_lama'         => 'Admin123Kalsel#',
            'password'              => 'AdminSuper2026!#',
            'password_confirmation' => 'AdminSuper2026!#',
        ]);
        $resUsername->assertSessionHasErrors('password');
    }

    /**
     * 5. Sandi valid yang mengandung spasi dan simbol khusus diperbolehkan.
     */
    public function test_sandi_valid_dengan_spasi_dan_simbol_diterima(): void
    {
        $this->actingAs($this->anggota, 'anggota');

        $sandiBaru = 'Kalsel Baiman 2026 #'; // 20 karakter, ada huruf besar, kecil, angka, spasi, simbol

        $response = $this->put(route('anggota.password.update'), [
            'password_lama'         => 'SuryaBukanSandi1#',
            'password'              => $sandiBaru,
            'password_confirmation' => $sandiBaru,
        ]);

        $response->assertSessionHasNoErrors();
        $this->anggota->refresh();
        $this->assertTrue(Hash::check($sandiBaru, $this->anggota->password));
    }

    /**
     * 6. Form login admin dan personel tetap berjalan dengan aturan 'required' standar (tanpa validasi ketat).
     */
    public function test_form_login_admin_dan_personel_tetap_berjalan(): void
    {
        // Login Admin
        $resAdmin = $this->post(route('admin.login.proses'), [
            'username' => 'admin_keamanan',
            'password' => 'Admin123Kalsel#',
        ]);
        $resAdmin->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin, 'admin');

        // Logout Admin
        $this->post(route('admin.logout'));

        // Login Personel
        $resAnggota = $this->post(route('login.proses'), [
            'email'    => '80010088',
            'password' => 'SuryaBukanSandi1#',
        ]);
        $resAnggota->assertRedirect(route('presensi.index'));
        $this->assertAuthenticatedAs($this->anggota, 'anggota');
    }

    /**
     * 7. Alihan wajib ganti sandi: admin yang wajib_ganti_password = true dialihkan ke form ubah password.
     */
    public function test_alihan_wajib_ganti_sandi_admin(): void
    {
        $this->admin->update(['wajib_ganti_password' => true]);
        $this->actingAs($this->admin, 'admin');

        // Coba akses dashboard admin
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.password'));

        // Akses halaman ubah password tetap diizinkan
        $resPassword = $this->get(route('admin.password'));
        $resPassword->assertOk();
    }

    /**
     * 8. Alihan wajib ganti sandi: personel yang wajib_ganti_password = true dialihkan ke form ubah password.
     */
    public function test_alihan_wajib_ganti_sandi_personel(): void
    {
        $this->anggota->update(['wajib_ganti_password' => true]);
        $this->actingAs($this->anggota, 'anggota');

        // Coba akses halaman presensi
        $response = $this->get(route('presensi.index'));
        $response->assertRedirect(route('anggota.password'));

        // Akses halaman ubah password tetap diizinkan
        $resPassword = $this->get(route('anggota.password'));
        $resPassword->assertOk();
    }

    /**
     * 9. Setelah sandi berhasil diganti, kolom wajib_ganti_password otomatis menjadi false.
     */
    public function test_berhasil_ganti_sandi_mengubah_wajib_ganti_password_menjadi_false(): void
    {
        $this->anggota->update(['wajib_ganti_password' => true]);
        $this->actingAs($this->anggota, 'anggota');

        $sandiBaru = 'KalselKompak2026#';
        $res = $this->put(route('anggota.password.update'), [
            'password_lama'         => 'SuryaBukanSandi1#',
            'password'              => $sandiBaru,
            'password_confirmation' => $sandiBaru,
        ]);

        $res->assertSessionHasNoErrors();
        $this->anggota->refresh();
        $this->assertFalse((bool) $this->anggota->wajib_ganti_password);

        // Setelah false, bisa akses halaman presensi secara normal
        $resPresensi = $this->get(route('presensi.index'));
        $resPresensi->assertOk();
    }

    /**
     * 10. Reset password personel oleh admin mengaktifkan wajib_ganti_password = true dan memberi pesan peringatan.
     */
    public function test_reset_password_oleh_admin_mengaktifkan_wajib_ganti_password(): void
    {
        $this->actingAs($this->admin, 'admin');

        $sandiSementara = 'SandiBaruSementara2026#';
        $res = $this->put(route('admin.anggota.reset_password', $this->anggota->id_anggota), [
            'password' => $sandiSementara,
        ]);

        $res->assertSessionHasNoErrors();
        $res->assertSessionHas('warning');

        $this->anggota->refresh();
        $this->assertTrue((bool) $this->anggota->wajib_ganti_password);
        $this->assertTrue(Hash::check($sandiSementara, $this->anggota->password));
    }

    /**
     * 11. Batas sesi tidak aktif admin: jika melewati batas SESSION_ADMIN_LIFETIME, otomatis logout.
     */
    public function test_batas_sesi_tidak_aktif_admin(): void
    {
        $this->actingAs($this->admin, 'admin');

        // Simulasi aktivitas 35 menit yang lalu (timeout default 30 menit)
        session(['admin_last_activity' => time() - (35 * 60)]);

        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHas('error');
        $this->assertGuest('admin');
    }

    /**
     * 12. Batas sesi tidak aktif personel: jika melewati batas SESSION_ANGGOTA_LIFETIME, otomatis logout.
     */
    public function test_batas_sesi_tidak_aktif_personel(): void
    {
        $this->actingAs($this->anggota, 'anggota');

        // Simulasi aktivitas 125 menit yang lalu (timeout default 120 menit)
        session(['anggota_last_activity' => time() - (125 * 60)]);

        $response = $this->get(route('presensi.index'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest('anggota');
    }

    /**
     * 13. Pencatatan log aktivitas admin: login gagal dan login berhasil tercatat tanpa membocorkan sandi.
     */
    public function test_pencatatan_log_aktivitas_admin(): void
    {
        // 1. Coba login gagal
        $this->post(route('admin.login.proses'), [
            'username' => 'admin_keamanan',
            'password' => 'SandiSalah123!',
        ]);

        $logGagal = LogAktivitas::where('aktivitas', 'Login Admin Gagal')->latest('id_log')->first();
        $this->assertNotNull($logGagal);
        $this->assertStringContainsString('admin_keamanan', $logGagal->keterangan);
        $this->assertStringNotContainsString('SandiSalah123!', $logGagal->keterangan); // Sandi TIDAK boleh tercatat

        // 2. Login berhasil
        $this->post(route('admin.login.proses'), [
            'username' => 'admin_keamanan',
            'password' => 'Admin123Kalsel#',
        ]);

        $logBerhasil = LogAktivitas::where('aktivitas', 'Login Admin')->latest('id_log')->first();
        $this->assertNotNull($logBerhasil);
        $this->assertStringContainsString('admin_keamanan', $logBerhasil->keterangan);
        $this->assertStringNotContainsString('Admin123Kalsel#', $logBerhasil->keterangan);
    }
}

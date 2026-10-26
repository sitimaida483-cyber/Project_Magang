<?php

namespace App\Console\Commands;

use App\Models\Anggota;
use App\Models\Pengaturan;
use App\Models\Presensi;
use App\Services\NotifikasiService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * KirimPengingatPresensi
 * Command scheduler untuk mengirimkan notifikasi Web Push pengingat presensi
 * masuk dan pulang secara otomatis sesuai konfigurasi jam kantor.
 */
class KirimPengingatPresensi extends Command
{
    /**
     * Nama dan signature command artisan.
     * Opsi --force untuk pengujian manual: --force=masuk atau --force=pulang
     */
    protected $signature = 'presensi:kirim-pengingat {--force= : Paksa kirim pengingat (masuk|pulang) untuk pengujian}';

    /**
     * Deskripsi command artisan.
     */
    protected $description = 'Kirim notifikasi pengingat otomatis sebelum jam masuk dan jam keluar ke perangkat anggota aktif';

    public function handle(): int
    {
        $pengaturan = Pengaturan::first();

        if (! $pengaturan) {
            $this->warn('Pengaturan kantor belum dikonfigurasi. Pengingat dibatalkan.');
            return self::FAILURE;
        }

        $menitPengingat = config('presensi.menit_pengingat', 15);
        $waktuSekarang   = Carbon::now()->format('H:i');
        $hariIni         = Carbon::today()->toDateString();
        $forceMode       = $this->option('force');

        // Hitung waktu target pengingat
        $targetJamMasuk  = Carbon::parse($pengaturan->jam_masuk)->subMinutes($menitPengingat)->format('H:i');
        $targetJamKeluar = Carbon::parse($pengaturan->jam_keluar)->subMinutes($menitPengingat)->format('H:i');

        $this->info("Waktu Sekarang: {$waktuSekarang} WITA | Target Pengingat Masuk: {$targetJamMasuk} | Target Pengingat Pulang: {$targetJamKeluar}");

        // 1. PENGINGAT PRESENSI MASUK
        if ($waktuSekarang === $targetJamMasuk || $forceMode === 'masuk') {
            $this->info("Memproses pengingat presensi masuk ({$menitPengingat} menit sebelum jam {$pengaturan->jam_masuk})...");

            // Cari ID anggota yang sudah presensi masuk hari ini
            $anggotaSudahMasukIds = Presensi::whereDate('tanggal', $hariIni)
                ->where('jenis_presensi', 'masuk')
                ->pluck('id_anggota')
                ->toArray();

            // Ambil anggota aktif yang BELUM presensi masuk
            $anggotaBelumMasuk = Anggota::where('status', 'aktif')
                ->whereNotIn('id_anggota', $anggotaSudahMasukIds)
                ->with(['perangkatNotifikasi' => function ($q) {
                    $q->where('status', 'aktif');
                }])
                ->get();

            $totalTerkirim = 0;
            foreach ($anggotaBelumMasuk as $anggota) {
                foreach ($anggota->perangkatNotifikasi as $perangkat) {
                    $jamMasukFmt = substr($pengaturan->jam_masuk, 0, 5);
                    $pesan = "Halo {$anggota->nama}, batas waktu presensi masuk adalah pukul {$jamMasukFmt} WITA. Silakan lakukan presensi tepat waktu.";

                    $sukses = NotifikasiService::kirim(
                        perangkat: $perangkat,
                        jenisNotifikasi: 'pengingat_masuk',
                        judul: '⏰ Pengingat Presensi Masuk',
                        pesan: $pesan,
                        targetUrl: '/presensi'
                    );

                    if ($sukses) {
                        $totalTerkirim++;
                    }
                }
            }

            $this->info("Selesai mengirim {$totalTerkirim} notifikasi pengingat masuk.");
        }

        // 2. PENGINGAT PRESENSI PULANG
        if ($waktuSekarang === $targetJamKeluar || $forceMode === 'pulang') {
            $this->info("Memproses pengingat presensi pulang ({$menitPengingat} menit sebelum jam {$pengaturan->jam_keluar})...");

            // Cari ID anggota yang sudah presensi masuk hari ini
            $anggotaSudahMasukIds = Presensi::whereDate('tanggal', $hariIni)
                ->where('jenis_presensi', 'masuk')
                ->pluck('id_anggota')
                ->toArray();

            // Cari ID anggota yang sudah presensi pulang hari ini
            $anggotaSudahPulangIds = Presensi::whereDate('tanggal', $hariIni)
                ->where('jenis_presensi', 'pulang')
                ->pluck('id_anggota')
                ->toArray();

            // Ambil anggota yang SUDAH masuk tapi BELUM pulang
            $anggotaBelumPulang = Anggota::where('status', 'aktif')
                ->whereIn('id_anggota', $anggotaSudahMasukIds)
                ->whereNotIn('id_anggota', $anggotaSudahPulangIds)
                ->with(['perangkatNotifikasi' => function ($q) {
                    $q->where('status', 'aktif');
                }])
                ->get();

            $totalTerkirim = 0;
            foreach ($anggotaBelumPulang as $anggota) {
                foreach ($anggota->perangkatNotifikasi as $perangkat) {
                    $jamKeluarFmt = substr($pengaturan->jam_keluar, 0, 5);
                    $pesan = "Halo {$anggota->nama}, jam dinas kantor berakhir pukul {$jamKeluarFmt} WITA. Jangan lupa lakukan presensi pulang sebelum meninggalkan kantor.";

                    $sukses = NotifikasiService::kirim(
                        perangkat: $perangkat,
                        jenisNotifikasi: 'pengingat_pulang',
                        judul: '🏠 Pengingat Presensi Pulang',
                        pesan: $pesan,
                        targetUrl: '/presensi'
                    );

                    if ($sukses) {
                        $totalTerkirim++;
                    }
                }
            }

            $this->info("Selesai mengirim {$totalTerkirim} notifikasi pengingat pulang.");
        }

        return self::SUCCESS;
    }
}

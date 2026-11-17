<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Pengaturan;
use App\Models\Presensi;
use Illuminate\Support\Carbon;

/**
 * DeteksiAnomaliService
 * Menganalisis parameter telemetri presensi untuk mendeteksi indikasi
 * pemalsuan lokasi (Fake GPS), manipulasi foto, atau kejanggalan waktu kerja.
 */
class DeteksiAnomaliService
{
    /**
     * Jalankan seluruh aturan analisis anomali presensi.
     * Mengembalikan array alasan kejanggalan jika ditemukan.
     *
     * @return array<string> Daftar deskripsi alasan anomali
     */
    public static function evaluasi(
        Anggota $anggota,
        Pengaturan $pengaturan,
        string $jenisPresensi,
        float $userLat,
        float $userLng,
        float $jarakMeter,
        ?float $akurasiGps,
        string $fotoHash,
        string $ipAddress,
        string $tanggalHariIni,
        string $waktuSekarang
    ): array {
        $alasan = [];

        // Aturan a: Jarak ke titik pusat kantor sangat kecil (< 1 meter)
        $minJarak = (float) config('presensi.anomali.jarak_terlalu_sempurna_meter', 1.0);
        if ($jarakMeter < $minJarak) {
            $alasan[] = "Jarak GPS ke titik kantor luar biasa presisi ({$jarakMeter} m), terindikasi koordinat palsu / Fake GPS.";
        }

        // Aturan b: Akurasi GPS kosong, sangat kecil (<= 1m), atau sangat besar (> 150m)
        $minAkurasi = (float) config('presensi.anomali.akurasi_min_meter', 1.0);
        $maxAkurasi = (float) config('presensi.anomali.akurasi_max_meter', 150.0);

        if ($akurasiGps === null || $akurasiGps <= 0) {
            $alasan[] = "Akurasi sinyal GPS tidak dilaporkan oleh perangkat (koordinat tanpa metadata akurasi).";
        } elseif ($akurasiGps <= $minAkurasi) {
            $alasan[] = "Akurasi sinyal GPS terlalu sempurna ({$akurasiGps} m), terindikasi injeksi mock location/emulator.";
        } elseif ($akurasiGps > $maxAkurasi) {
            $alasan[] = "Akurasi sinyal GPS sangat rendah ({$akurasiGps} m > {$maxAkurasi} m), deviasi lokasi terlalu besar.";
        }

        // Aturan c: Koordinat (6 desimal) identik dengan presensi lain pada hari yang sama
        if (config('presensi.anomali.cek_koordinat_duplikat', true)) {
            // Toleransi 6 desimal (0.000005 derajat ≈ 0.5 meter)
            $delta = 0.000005;
            $adaKoordinatKembar = Presensi::whereDate('tanggal', $tanggalHariIni)
                ->whereBetween('latitude', [$userLat - $delta, $userLat + $delta])
                ->whereBetween('longitude', [$userLng - $delta, $userLng + $delta])
                ->exists();

            if ($adaKoordinatKembar) {
                $latFormatted = number_format($userLat, 6, '.', '');
                $lngFormatted = number_format($userLng, 6, '.', '');
                $alasan[] = "Koordinat GPS ({$latFormatted}, {$lngFormatted}) identik persis dengan rekaman presensi lain pada hari yang sama.";
            }
        }

        // Aturan d: Foto selfie yang sama persis (foto_hash) pernah dipakai sebelumnya oleh anggota itu
        if (config('presensi.anomali.cek_foto_duplikat', true) && ! empty($fotoHash)) {
            $adaFotoBekas = Presensi::where('id_anggota', $anggota->id_anggota)
                ->where('foto_hash', $fotoHash)
                ->exists();

            if ($adaFotoBekas) {
                $alasan[] = "Berkas foto selfie identik (hash SHA-256 kembar) dengan foto presensi terdahulu milik personel ini.";
            }
        }

        // Aturan e: Presensi pulang < 60 menit setelah masuk, atau presensi masuk setelah jam_keluar kantor
        if ($jenisPresensi === 'pulang') {
            $presensiMasuk = Presensi::where('id_anggota', $anggota->id_anggota)
                ->whereDate('tanggal', $tanggalHariIni)
                ->where('jenis_presensi', 'masuk')
                ->first();

            if ($presensiMasuk) {
                $tanggalStr = $presensiMasuk->tanggal instanceof \Carbon\CarbonInterface 
                    ? $presensiMasuk->tanggal->format('Y-m-d') 
                    : (string) $presensiMasuk->tanggal;
                $waktuMasuk = Carbon::parse($tanggalStr . ' ' . $presensiMasuk->waktu);
                $waktuPulang = Carbon::parse($tanggalHariIni . ' ' . $waktuSekarang);
                $selisihMenit = $waktuMasuk->diffInMinutes($waktuPulang, false);
                $minMenitPulang = (int) config('presensi.anomali.pulang_min_menit_setelah_masuk', 60);

                if ($selisihMenit < $minMenitPulang) {
                    $alasan[] = "Presensi pulang diajukan hanya {$selisihMenit} menit setelah presensi masuk (kurang dari batas minimum {$minMenitPulang} menit).";
                }
            }
        } elseif ($jenisPresensi === 'masuk') {
            $jamKeluarKantor = substr($pengaturan->jam_keluar ?? '17:00:00', 0, 8);
            if ($waktuSekarang > $jamKeluarKantor) {
                $alasan[] = "Presensi masuk dilakukan setelah jam operasional kepulangan kantor ({$jamKeluarKantor} WITA).";
            }
        }

        // Aturan f: Opsional - daftar jaringan kantor (CIDR)
        $allowedSubnets = config('presensi.anomali.subnet_kantor', []);
        if (! empty($allowedSubnets)) {
            $ipCocok = false;
            foreach ($allowedSubnets as $cidr) {
                if (self::ipInCidr($ipAddress, $cidr)) {
                    $ipCocok = true;
                    break;
                }
            }
            if (! $ipCocok) {
                $alasan[] = "Alamat IP ({$ipAddress}) berada di luar daftar subnet jaringan dinas kantor yang diizinkan.";
            }
        }

        return $alasan;
    }

    /**
     * Helper: Periksa apakah IP berada dalam subnet CIDR.
     */
    public static function ipInCidr(string $ip, string $cidr): bool
    {
        if (! str_contains($cidr, '/')) {
            return $ip === $cidr;
        }

        [$subnet, $mask] = explode('/', $cidr, 2);

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskLong = ~((1 << (32 - (int) $mask)) - 1);

            return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
        }

        return false;
    }
}

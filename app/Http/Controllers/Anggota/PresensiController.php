<?php

namespace App\Http\Controllers\Anggota;

use App\Http\Controllers\Controller;
use App\Http\Requests\Anggota\PresensiRequest;
use App\Models\Anggota;
use App\Models\Pengaturan;
use App\Models\Presensi;
use App\Services\HaversineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * PresensiController (Publik)
 * Menangani alur presensi anggota personel tanpa login via HP/browser.
 */
class PresensiController extends Controller
{
    /**
     * Tampilkan halaman formulir presensi anggota (mobile-first).
     */
    public function index(): View
    {
        $pengaturan = Pengaturan::first();

        return view('anggota.presensi.index', compact('pengaturan'));
    }

    /**
     * Endpoint AJAX: Cek validitas NRP dan status presensi hari ini.
     */
    public function cekNrp(Request $request): JsonResponse
    {
        $request->validate([
            'nrp' => 'required|string',
        ]);

        $anggota = Anggota::where('nrp', $request->nrp)->first();

        if (! $anggota) {
            return response()->json([
                'sukses' => false,
                'pesan'  => 'NRP tidak terdaftar dalam sistem. Pastikan NRP Anda benar atau hubungi admin.',
            ], 404);
        }

        if ($anggota->status !== 'aktif') {
            return response()->json([
                'sukses' => false,
                'pesan'  => 'Status keanggotaan Anda Nonaktif. Anda tidak dapat melakukan presensi.',
            ], 403);
        }

        $hariIni = Carbon::today()->toDateString();

        // Cek rekaman presensi hari ini
        $presensiMasuk = Presensi::where('id_anggota', $anggota->id_anggota)
            ->whereDate('tanggal', $hariIni)
            ->where('jenis_presensi', 'masuk')
            ->first();

        $presensiPulang = Presensi::where('id_anggota', $anggota->id_anggota)
            ->whereDate('tanggal', $hariIni)
            ->where('jenis_presensi', 'pulang')
            ->first();

        // Tentukan rekomendasi aksi presensi
        $rekomendasi = 'masuk';
        if ($presensiMasuk && ! $presensiPulang) {
            $rekomendasi = 'pulang';
        } elseif ($presensiMasuk && $presensiPulang) {
            $rekomendasi = 'lengkap';
        }

        return response()->json([
            'sukses' => true,
            'data'   => [
                'id_anggota'      => $anggota->id_anggota,
                'nama'            => $anggota->nama,
                'nrp'             => $anggota->nrp,
                'jabatan'         => $anggota->jabatan,
                'unit_kerja'      => $anggota->unit_kerja,
                'sudah_masuk'     => (bool) $presensiMasuk,
                'waktu_masuk'     => $presensiMasuk ? Carbon::parse($presensiMasuk->waktu)->format('H:i') : null,
                'sudah_pulang'    => (bool) $presensiPulang,
                'waktu_pulang'    => $presensiPulang ? Carbon::parse($presensiPulang->waktu)->format('H:i') : null,
                'rekomendasi'     => $rekomendasi,
            ],
        ]);
    }

    /**
     * Simpan rekaman presensi anggota (Masuk / Pulang).
     */
    public function store(PresensiRequest $request): JsonResponse
    {
        // 1. Identifikasi Anggota
        $anggota = Anggota::where('nrp', $request->nrp)->first();

        if (! $anggota || $anggota->status !== 'aktif') {
            return response()->json([
                'sukses' => false,
                'pesan'  => 'Personel tidak ditemukan atau berstatus nonaktif.',
            ], 422);
        }

        // 2. Ambil Pengaturan Lokasi & Waktu Kantor
        $pengaturan = Pengaturan::first();
        if (! $pengaturan) {
            return response()->json([
                'sukses' => false,
                'pesan'  => 'Pengaturan lokasi kantor belum dikonfigurasi oleh admin.',
            ], 500);
        }

        $sekarang   = Carbon::now();
        $tanggalHariIni = $sekarang->toDateString();
        $waktuSekarang  = $sekarang->format('H:i:s');

        // 3. ATURAN BISNIS: Validasi Presensi Masuk & Pulang
        if ($request->jenis_presensi === 'masuk') {
            $sudahMasuk = Presensi::where('id_anggota', $anggota->id_anggota)
                ->whereDate('tanggal', $tanggalHariIni)
                ->where('jenis_presensi', 'masuk')
                ->exists();

            if ($sudahMasuk) {
                return response()->json([
                    'sukses' => false,
                    'pesan'  => 'Anda sudah melakukan presensi MASUK pada hari ini.',
                ], 422);
            }
        } elseif ($request->jenis_presensi === 'pulang') {
            // Presensi pulang HANYA boleh setelah presensi masuk
            $sudahMasuk = Presensi::where('id_anggota', $anggota->id_anggota)
                ->whereDate('tanggal', $tanggalHariIni)
                ->where('jenis_presensi', 'masuk')
                ->exists();

            if (! $sudahMasuk) {
                return response()->json([
                    'sukses' => false,
                    'pesan'  => 'Presensi PULANG ditolak. Anda belum melakukan presensi masuk hari ini.',
                ], 422);
            }

            $sudahPulang = Presensi::where('id_anggota', $anggota->id_anggota)
                ->whereDate('tanggal', $tanggalHariIni)
                ->where('jenis_presensi', 'pulang')
                ->exists();

            if ($sudahPulang) {
                return response()->json([
                    'sukses' => false,
                    'pesan'  => 'Anda sudah melakukan presensi PULANG pada hari ini.',
                ], 422);
            }
        }

        // 4. ATURAN BISNIS: Validasi Jarak Geolocation (Haversine)
        $userLat = (float) $request->latitude;
        $userLng = (float) $request->longitude;
        $kantorLat = (float) $pengaturan->latitude;
        $kantorLng = (float) $pengaturan->longitude;

        $jarakMeter = HaversineService::hitungJarak($userLat, $userLng, $kantorLat, $kantorLng);

        if ($jarakMeter > (float) $pengaturan->radius_meter) {
            return response()->json([
                'sukses'      => false,
                'pesan'       => "Presensi DITOLAK karena Anda berada di luar radius kantor! Jarak Anda saat ini: " . number_format($jarakMeter, 0, ',', '.') . " meter (Batas radius: {$pengaturan->radius_meter} meter).",
                'jarak_meter' => $jarakMeter,
                'radius_meter'=> $pengaturan->radius_meter,
            ], 422);
        }

        // 5. ATURAN BISNIS: Tentukan status_kehadiran
        // 'terlambat' jika presensi masuk melewati jam_masuk, selain itu 'tepat_waktu'
        $statusKehadiran = 'tepat_waktu';
        if ($request->jenis_presensi === 'masuk') {
            // Jam masuk kantor (HH:MM:SS)
            $jamBatasMasuk = substr($pengaturan->jam_masuk, 0, 8);
            if ($waktuSekarang > $jamBatasMasuk) {
                $statusKehadiran = 'terlambat';
            }
        }

        // 6. Simpan File Foto Selfie & Tanda Tangan Digital secara Aman
        $pathFoto = $this->simpanBase64KeFile($request->foto_selfie, 'foto_selfie', $anggota->nrp, 'jpg');
        $pathTtd  = $this->simpanBase64KeFile($request->tanda_tangan, 'tanda_tangan', $anggota->nrp, 'png');

        if (! $pathFoto || ! $pathTtd) {
            return response()->json([
                'sukses' => false,
                'pesan'  => 'Gagal memproses berkas foto selfie atau tanda tangan digital.',
            ], 500);
        }

        // 7. Simpan Record ke Tabel Presensi
        $presensi = Presensi::create([
            'id_anggota'       => $anggota->id_anggota,
            'tanggal'          => $tanggalHariIni,
            'waktu'            => $waktuSekarang,
            'jenis_presensi'   => $request->jenis_presensi,
            'foto_selfie'      => $pathFoto,
            'tanda_tangan'     => $pathTtd,
            'latitude'         => $userLat,
            'longitude'        => $userLng,
            'status_kehadiran' => $statusKehadiran,
        ]);

        // Cek apakah anggota belum memiliki perangkat notifikasi (untuk pemicu izin push di Tahap 6)
        $mintaIzinNotifikasi = ! $anggota->perangkatNotifikasi()->exists();

        $pesanSukses = "Presensi " . strtoupper($request->jenis_presensi) . " berhasil dicatat!";
        if ($request->jenis_presensi === 'masuk') {
            $pesanSukses .= $statusKehadiran === 'terlambat' 
                ? " (Status: Terlambat, tercatat pukul {$waktuSekarang} WITA)." 
                : " (Status: Tepat Waktu, tercatat pukul {$waktuSekarang} WITA).";
        } else {
            $pesanSukses .= " (Tercatat pukul {$waktuSekarang} WITA. Terima kasih dan selamat beristirahat).";
        }

        return response()->json([
            'sukses'                => true,
            'pesan'                 => $pesanSukses,
            'minta_izin_notifikasi' => $mintaIzinNotifikasi,
            'id_anggota'            => $anggota->id_anggota,
            'data'                  => [
                'id_presensi'      => $presensi->id_presensi,
                'jenis_presensi'   => $presensi->jenis_presensi,
                'waktu'            => $waktuSekarang,
                'status_kehadiran' => $statusKehadiran,
                'jarak_meter'      => $jarakMeter,
            ],
        ]);
    }

    /**
     * Helper: Dekode data base64 dan simpan ke folder penyimpanan privat.
     */
    private function simpanBase64KeFile(string $base64String, string $folder, string $nrp, string $extension): ?string
    {
        try {
            // Pisahkan header data URI (misal: "data:image/jpeg;base64,....")
            if (str_contains($base64String, ',')) {
                $base64Data = explode(',', $base64String)[1];
            } else {
                $base64Data = $base64String;
            }

            $decoded = base64_decode($base64Data);
            if ($decoded === false) {
                return null;
            }

            $namaFile = $folder . '_' . $nrp . '_' . date('Ymd_His') . '_' . Str::random(8) . '.' . $extension;
            $direktoriTujuan = storage_path('app/private_presensi/' . $folder);

            if (! file_exists($direktoriTujuan)) {
                mkdir($direktoriTujuan, 0755, true);
            }

            file_put_contents($direktoriTujuan . '/' . $namaFile, $decoded);

            // Simpan path relatif ke database
            return $folder . '/' . $namaFile;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }
}

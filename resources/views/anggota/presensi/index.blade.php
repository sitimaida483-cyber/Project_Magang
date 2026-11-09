@extends('layouts.anggota')

@section('title', 'Presensi Kehadiran')

@section('content')
<div class="mb-4">
    <h1 class="h1-navy">Presensi Kehadiran</h1>
    <div class="subtitle-navy">Presensi Digital Bidtik Polda Kalimantan Selatan</div>
</div>

<!-- Alert Status Keterangan Surat Hari Ini (Jika Disetujui) -->
@if(isset($suratHariIni) && $suratHariIni)
    <div class="alert border-0 rounded-3 mb-4" 
         style="background-color: {{ $bebasPresensi ? 'var(--np-soft)' : 'var(--np-warning-soft)' }}; color: {{ $bebasPresensi ? 'var(--np-primary)' : 'var(--np-warning)' }}; border: 1px solid {{ $bebasPresensi ? 'var(--np-line)' : 'var(--np-warning-line)' }} !important;">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-file-earmark-text-fill fs-5"></i>
            <strong class="fs-6">Hari ini: {{ $suratHariIni->label_jenis }} / Disetujui</strong>
        </div>
        <div class="small">
            Periode: {{ \Carbon\Carbon::parse($suratHariIni->tanggal_mulai)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($suratHariIni->tanggal_selesai)->translatedFormat('d M Y') }}.
            @if($bebasPresensi)
                Presensi lokasi hari ini tidak diwajibkan.
            @else
                Presensi lokasi tetap dapat dilakukan sesuai ketentuan.
            @endif
        </div>
    </div>
@endif

<!-- Alert Notifikasi Global -->
<div id="alertBox" class="alert d-none small mb-4" role="alert"></div>

<!-- FORM PRESENSI -->
<form id="formPresensi" onsubmit="return false;">
    @csrf
    <!-- Input Hidden Koordinat & Payload -->
    <input type="hidden" id="nrp" name="nrp" value="{{ $anggota->nrp }}">
    <input type="hidden" id="latitude" name="latitude">
    <input type="hidden" id="longitude" name="longitude">
    <input type="hidden" id="fotoSelfie" name="foto_selfie">

    <div class="row g-4">
        <!-- Kolom Kiri: 420px Desktop (Identitas, Jenis, GPS) -->
        <div class="col-12 col-lg-5" style="max-width: 440px;">
            <!-- 1. IDENTITAS PERSONEL (Page 5) -->
            <div class="card np-card mb-3" id="identityCard">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 48px; height: 48px; background-color: var(--np-soft); color: var(--np-primary); font-size: 24px; border: 1px solid #C7D7FE;">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="color: var(--np-ink); font-size: 16px;" id="personelNama">{{ $anggota->nama }}</div>
                            <div class="text-muted small" id="personelNrp">NRP {{ $anggota->nrp }} / <span id="personelPangkat">{{ $anggota->unit_kerja ?? 'Bid TIK' }}</span></div>
                        </div>
                    </div>

                    <!-- Status Presensi Hari Ini (Masuk / Pulang) -->
                    <div class="row g-2 pt-2 border-top" style="border-color: var(--np-line) !important;">
                        <div class="col-6">
                            <span class="text-muted d-block small" style="font-size: 12px;">Masuk</span>
                            <div class="mt-1">
                                <span id="badgeStatusMasuk" class="badge {{ $presensiMasuk ? 'badge-np-success' : 'badge-np-warning' }}">
                                    @if($presensiMasuk)
                                        Sudah ({{ \Carbon\Carbon::parse($presensiMasuk->waktu)->format('H:i') }} WITA)
                                    @else
                                        Belum Masuk
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small" style="font-size: 12px;">Pulang</span>
                            <div class="mt-1">
                                <span id="badgeStatusPulang" class="badge {{ $presensiPulang ? 'badge-np-info' : 'badge-np-warning' }}">
                                    @if($presensiPulang)
                                        Sudah ({{ \Carbon\Carbon::parse($presensiPulang->waktu)->format('H:i') }} WITA)
                                    @else
                                        Belum Pulang
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. PILIH JENIS PRESENSI (Page 5: Tombol Berdampingan Langsung) -->
            <div class="d-flex gap-2 mb-3">
                <div class="flex-grow-1">
                    <input type="radio" class="btn-check" name="jenis_presensi" id="jenisMasuk" value="masuk" 
                           {{ (!$presensiMasuk) ? 'checked' : '' }}
                           {{ ($presensiMasuk) ? 'disabled' : '' }}>
                    <label class="btn w-100 py-2 d-flex align-items-center justify-content-center jenis-presensi-btn" for="jenisMasuk" style="min-height: 48px; border-radius: 10px; font-weight: 700;">
                        <span>Presensi Masuk</span>
                    </label>
                </div>
                <div class="flex-grow-1">
                    <input type="radio" class="btn-check" name="jenis_presensi" id="jenisPulang" value="pulang" 
                           {{ ($presensiMasuk && !$presensiPulang) ? 'checked' : '' }}
                           {{ (!$presensiMasuk || $presensiPulang) ? 'disabled' : '' }}>
                    <label class="btn w-100 py-2 d-flex align-items-center justify-content-center jenis-presensi-btn" for="jenisPulang" style="min-height: 48px; border-radius: 10px; font-weight: 700;">
                        <span>Presensi Pulang</span>
                    </label>
                </div>
            </div>

            <!-- 3. LOKASI GPS & RADIUS (Page 5) -->
            <div class="card np-card mb-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold" style="color: var(--np-ink); font-size: 15px;">Lokasi GPS</span>
                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-bold" id="btnRefreshGps" style="color: var(--np-primary); font-size: 13px;">
                            Refresh GPS
                        </button>
                    </div>
                    <div class="mb-2">
                        <span id="radiusBadge" class="badge badge-np-warning">Mendeteksi GPS...</span>
                    </div>
                    <div class="text-muted small" id="gpsInfo" style="font-size: 13px;">
                        Sedang mencari sinyal satelit GPS perangkat Anda...
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: 684px Desktop (Selfie Kamera Depan & Aksi) -->
        <div class="col-12 col-lg-7">
            <div class="card np-card mb-3">
                <div class="card-header border-0 pb-0 pt-3 px-3">
                    <span class="fw-bold text-dark fs-6">Foto Selfie Kamera Depan</span>
                </div>
                <div class="card-body p-3">
                    <!-- Frame Kamera -->
                    <div class="position-relative overflow-hidden mb-3" style="aspect-ratio: 4/3; background-color: #0F172A; border: 1px solid var(--np-line); border-radius: 8px;">
                        <video id="cameraStream" autoplay playsinline muted style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); display: block;"></video>
                        <img id="photoPreview" alt="Pratinjau Foto Selfie" style="width: 100%; height: 100%; object-fit: cover; display: none;">

                        <!-- Label Overlay Pojok Bawah -->
                        <div class="position-absolute bottom-0 start-0 m-3 px-2 py-1 rounded small fw-bold text-white bg-dark bg-opacity-75" id="cameraStatusBadge" style="font-size: 11px; letter-spacing: 0.5px;">
                            SELFIE KAMERA DEPAN
                        </div>

                        <!-- Spinner Loading Kamera -->
                        <div id="cameraLoading" class="position-absolute top-50 start-50 translate-middle text-center text-white p-3" style="z-index: 10;">
                            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                            <div class="small fw-semibold">Menghubungkan Kamera Depan...</div>
                            <div class="text-white-50" style="font-size: 0.75rem;">Mohon izinkan akses kamera pada peramban</div>
                        </div>
                    </div>

                    <!-- Status Deteksi Wajah -->
                    <div id="faceCheckStatus" class="p-3 rounded-2 small mb-3" role="status" aria-live="polite" style="background-color: #FAFCFE; border: 1px solid var(--np-line); color: var(--np-muted); font-size: 13px;">
                        Posisikan wajah tegak lurus dan tidak terhalang.
                    </div>

                    <!-- Tombol Kamera (Ambil Foto / Foto Ulang) -->
                    <div class="d-flex flex-column gap-2 mb-3">
                        <button type="button" class="btn btn-primary w-100 fw-bold" id="btnSnapPhoto" disabled style="min-height: 48px; border-radius: 10px;">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span> Menghubungkan Kamera...
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-100 fw-bold d-none" id="btnRetakePhoto" style="min-height: 48px; border-radius: 10px;">
                            Foto Ulang
                        </button>
                        @if(isset($bebasPresensi) && $bebasPresensi)
                            <button type="button" id="btnSubmitPresensi" class="btn btn-secondary w-100 fw-bold" disabled style="min-height: 48px; border-radius: 10px;">
                                Presensi Dinonaktifkan
                            </button>
                        @elseif($presensiMasuk && $presensiPulang)
                            <button type="button" class="btn btn-secondary w-100 fw-bold" disabled style="min-height: 48px; border-radius: 10px;">
                                Kirim Presensi Sekarang
                            </button>
                        @else
                            <button type="button" id="btnSubmitPresensi" class="btn btn-primary w-100 fw-bold presensi-submit-button" style="min-height: 48px; border-radius: 10px;">
                                Kirim Presensi Sekarang
                            </button>
                        @endif
                    </div>

                    <!-- Canvas Snapshot (Tersembunyi) -->
                    <canvas id="photoCanvas" class="d-none"></canvas>

                    <!-- Subtext Jam Kerja -->
                    <div class="text-center text-muted small mt-2" style="font-size: 12px;">
                        Jam masuk {{ substr($pengaturan->jam_masuk ?? '07:30', 0, 5) }} / Jam pulang {{ substr($pengaturan->jam_keluar ?? '16:00', 0, 5) }} WITA
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('modals')
<!-- Modal Sukses Presensi (Pilihan A: Navy Precision) -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="modal-content border-0">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <span class="badge badge-np-success">Berhasil</span>
                </div>
                <h3 class="fw-bold text-dark mb-1">Presensi Berhasil!</h3>
                <p class="text-muted small mb-4" id="successMessageModal">-</p>

                <div class="p-3 bg-light rounded-3 text-start small mb-4 border" style="background-color: var(--np-bg) !important;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Personel</span>
                        <strong id="modalPersonel" class="text-dark">-</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Jenis Presensi</span>
                        <strong id="modalJenis" class="text-dark">-</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Waktu Catat</span>
                        <strong id="modalWaktu" class="text-dark">- WITA</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Status Ketepatan</span>
                        <span id="modalStatus">-</span>
                    </div>
                </div>

                <!-- Bagian Penawaran Izin Web Push -->
                <div id="pushPromptSection" class="d-none p-3 rounded-3 text-start small mb-4 border" style="background-color: #FEF3C7; border-color: #FDE68A !important;">
                    <div class="fw-bold text-dark mb-1">Aktifkan Pengingat Presensi?</div>
                    <p class="text-muted mb-3" style="font-size: 12px;">
                        Terima pengingat sebelum jam masuk dan jam pulang.
                    </p>
                    <button type="button" class="btn btn-primary btn-sm w-100 fw-bold" id="btnAktifkanPush" style="min-height: 40px;">
                        Izinkan Pengingat Otomatis
                    </button>
                    <div id="pushStatusInfo" class="text-success small mt-2 d-none text-center"></div>
                </div>

                <button type="button" class="btn btn-outline-secondary w-100 fw-bold" data-bs-dismiss="modal" onclick="location.reload();" style="min-height: 48px; border-radius: 10px;">
                    Selesai & Muat Ulang
                </button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    // Konfigurasi Kantor dari Backend
    const KANTOR_CONFIG = {
        lat: parseFloat("{{ $pengaturan->latitude ?? -3.3194000 }}"),
        lng: parseFloat("{{ $pengaturan->longitude ?? 114.5925000 }}"),
        radius: parseInt("{{ $pengaturan->radius_meter ?? 100 }}"),
        nama: "{{ $pengaturan->nama_lokasi ?? 'Bidtik Polda Kalsel' }}"
    };

    // Global State
    let streamCamera = null;
    let faceDetector = null;
    let cameraReady = false;
    let faceDetectedForCurrentPhoto = false;
    let userCoordinates = { lat: null, lng: null, distance: null };
    let currentPresensiAnggotaId = {{ $anggota->id_anggota }};

    document.addEventListener('DOMContentLoaded', function() {
        // Registrasi Service Worker untuk Web Push Notifikasi
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(function(err) {
                console.warn('Gagal meregistrasi Service Worker:', err);
            });
        }

        initCamera();
        initFaceDetector();
        detectGeolocation();
        setupEventListeners();
    });

    async function initFaceDetector() {
        const status = document.getElementById('faceCheckStatus');
        if (status) {
            status.className = 'alert alert-info py-2 small mb-2';
            status.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghubungkan modul deteksi wajah...';
        }

        try {
            if (typeof window.createFaceDetector === 'function') {
                // Timeout 6 detik agar tidak menggantung jika koneksi CDN lambat
                const timeoutPromise = new Promise((_, reject) => setTimeout(() => reject(new Error('Timeout memuat model')), 6000));
                faceDetector = await Promise.race([window.createFaceDetector(), timeoutPromise]);
                if (status) {
                    status.className = 'alert alert-info py-2 small mb-2';
                    status.innerHTML = '<i class="bi bi-person-check-fill me-1 text-primary"></i> Deteksi wajah biometrik aktif. Posisikan wajah Anda tegak lurus menghadap kamera.';
                }
            } else {
                if (status) {
                    status.className = 'alert alert-info py-2 small mb-2';
                    status.innerHTML = '<i class="bi bi-camera-fill me-1 text-primary"></i> Kamera siap. Posisikan wajah Anda tegak lurus menghadap kamera dan ambil selfie.';
                }
            }
        } catch (error) {
            console.warn('Deteksi wajah AI fallback:', error);
            if (status) {
                status.className = 'alert alert-info py-2 small mb-2';
                status.innerHTML = '<i class="bi bi-camera-fill me-1 text-primary"></i> Kamera siap. Posisikan wajah Anda tegak lurus menghadap kamera dan ambil selfie.';
            }
        } finally {
            updateCaptureButton();
        }
    }

    function updateCaptureButton() {
        const button = document.getElementById('btnSnapPhoto');
        if (!button) return;

        if (!cameraReady) {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghubungkan Kamera...';
        } else {
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-camera-fill me-1"></i> Ambil Foto Selfie';
        }
    }

    function setFaceCheckStatus(type, message) {
        const status = document.getElementById('faceCheckStatus');
        if (!status) return;
        if (type === 'success') {
            status.style.backgroundColor = 'var(--np-soft)';
            status.style.borderColor = '#C7D7FE';
            status.style.color = 'var(--np-primary)';
        } else if (type === 'warning' || type === 'danger') {
            status.style.backgroundColor = 'var(--np-warning-soft)';
            status.style.borderColor = 'var(--np-warning-line)';
            status.style.color = 'var(--np-warning)';
        } else {
            status.style.backgroundColor = '#FAFCFE';
            status.style.borderColor = 'var(--np-line)';
            status.style.color = 'var(--np-muted)';
        }
        status.innerHTML = message;
    }

    // Pemeriksaan Kualitas Wajah (Pose Lurus & Tidak Tertutup)
    function checkFaceQuality(ctx, canvas, detection) {
        const keypoints = detection.keypoints;
        if (!keypoints || keypoints.length < 4) {
            return {
                valid: false,
                message: 'Fitur wajah tidak terdeteksi lengkap. Pastikan seluruh wajah menghadap kamera.'
            };
        }

        const eyeA = keypoints[0];
        const eyeB = keypoints[1];
        const eyeLeft = (eyeA.x < eyeB.x) ? eyeA : eyeB;
        const eyeRight = (eyeA.x < eyeB.x) ? eyeB : eyeA;
        const nose = keypoints[2];
        const mouth = keypoints[3];

        if (!eyeLeft || !eyeRight || !nose || !mouth) {
            return {
                valid: false,
                message: 'Mata, hidung, dan mulut harus terlihat jelas di kamera.'
            };
        }

        const eyeNormDist = Math.hypot(eyeRight.x - eyeLeft.x, eyeRight.y - eyeLeft.y);
        const eyePixelDist = eyeNormDist * canvas.width;

        if (eyeNormDist < 0.06) {
            return {
                valid: false,
                message: 'Wajah terlalu jauh dari kamera. Harap dekatkan posisi wajah Anda.'
            };
        }

        // 1. CEK MENOLEH KE KIRI / KANAN (Yaw)
        const eyeMidX = (eyeLeft.x + eyeRight.x) / 2;
        const noseOffsetX = Math.abs(nose.x - eyeMidX) / Math.max(eyeNormDist, 0.01);
        if (noseOffsetX > 0.13) {
            return {
                valid: false,
                message: 'Wajah harus lurus menghadap kamera. Dilarang menoleh ke kiri atau kanan.'
            };
        }

        const distLeft = Math.hypot(nose.x - eyeLeft.x, nose.y - eyeLeft.y);
        const distRight = Math.hypot(nose.x - eyeRight.x, nose.y - eyeRight.y);
        const eyeRatio = Math.min(distLeft, distRight) / Math.max(distLeft, distRight, 0.001);
        if (eyeRatio < 0.72) {
            return {
                valid: false,
                message: 'Wajah terdeteksi menoleh ke samping. Pandang lurus ke lensa kamera.'
            };
        }

        // 2. CEK KEPALA MIRING (Roll)
        const eyeTilt = Math.abs(eyeRight.y - eyeLeft.y) / Math.max(eyeNormDist, 0.01);
        if (eyeTilt > 0.16) {
            return {
                valid: false,
                message: 'Posisi kepala miring. Harap tegakkan kepala lurus menghadap kamera.'
            };
        }

        // 3. CEK SUSUNAN VERTIKAL (Pitch / Menunduk / Mendongak)
        const eyeMidY = (eyeLeft.y + eyeRight.y) / 2;
        if (nose.y <= eyeMidY + 0.03 * eyeNormDist) {
            return {
                valid: false,
                message: 'Wajah mendongak ke atas. Pandang lurus ke arah kamera.'
            };
        }
        if (mouth.y <= nose.y + 0.06 * eyeNormDist) {
            return {
                valid: false,
                message: 'Wajah menunduk ke bawah. Tegakkan wajah lurus menghadap kamera.'
            };
        }

        // 4. CEK APAKAH WAJAH TERTUTUP (Kertas, Buku, Tangan, Masker, HP, dll.)
        const sampleSize = Math.max(16, Math.min(50, Math.round(eyePixelDist * 0.32)));

        function getRegionStats(normX, normY, size) {
            const cx = normX * canvas.width;
            const cy = normY * canvas.height;
            const x0 = Math.max(0, Math.min(canvas.width - size, Math.round(cx - size / 2)));
            const y0 = Math.max(0, Math.min(canvas.height - size, Math.round(cy - size / 2)));

            const imgData = ctx.getImageData(x0, y0, size, size);
            const data = imgData.data;
            let sumLum = 0;
            let sumLumSq = 0;
            let sumR = 0, sumG = 0, sumB = 0;
            let skinCount = 0;
            const total = data.length / 4;

            for (let i = 0; i < data.length; i += 4) {
                const r = data[i];
                const g = data[i + 1];
                const b = data[i + 2];
                const lum = 0.299 * r + 0.587 * g + 0.114 * b;
                sumLum += lum;
                sumLumSq += lum * lum;
                sumR += r;
                sumG += g;
                sumB += b;

                // Karakteristik spektrum alami kulit wajah
                if (r > g - 8 && r > b && (r + g + b) > 55 && (r + g + b) < 700) {
                    skinCount++;
                }
            }

            const avgLum = sumLum / total;
            const variance = Math.max(0, (sumLumSq / total) - (avgLum * avgLum));
            const stdDev = Math.sqrt(variance);

            return {
                avgLum,
                stdDev,
                avgR: sumR / total,
                avgG: sumG / total,
                avgB: sumB / total,
                skinRatio: skinCount / total
            };
        }

        const eyeLeftStats  = getRegionStats(eyeLeft.x, eyeLeft.y, sampleSize);
        const eyeRightStats = getRegionStats(eyeRight.x, eyeRight.y, sampleSize);
        const noseStats     = getRegionStats(nose.x, nose.y, sampleSize);
        const mouthStats    = getRegionStats(mouth.x, mouth.y, sampleSize);

        // Sampel pipi kiri dan pipi kanan
        const cheekLeftStats  = getRegionStats(nose.x - eyeNormDist * 0.45, nose.y, sampleSize);
        const cheekRightStats = getRegionStats(nose.x + eyeNormDist * 0.45, nose.y, sampleSize);

        // A. Cek penutup putih terang (seperti buku/kertas/masker putih)
        if (eyeLeftStats.avgLum > 185 || eyeRightStats.avgLum > 185 ||
            cheekLeftStats.avgLum > 185 || cheekRightStats.avgLum > 185 ||
            mouthStats.avgLum > 185) {
            return {
                valid: false,
                message: 'Wajah terhalang atau tertutup benda (kertas/buku/penutup). Pastikan seluruh wajah terlihat jelas tanpa penghalang.'
            };
        }

        // B. Cek penutup gelap (HP/buku gelap/penutup hitam)
        if (eyeLeftStats.avgLum < 20 || eyeRightStats.avgLum < 20 ||
            noseStats.avgLum < 20 || mouthStats.avgLum < 20 ||
            cheekLeftStats.avgLum < 20 || cheekRightStats.avgLum < 20) {
            return {
                valid: false,
                message: 'Wajah terhalang atau terlalu gelap. Pastikan tidak ada benda menutupi wajah dan pencahayaan cukup.'
            };
        }

        // C. Cek asimetri drastis pipi (misal satu sisi muka tertutup buku/tangan)
        const cheekDiff = Math.abs(cheekLeftStats.avgLum - cheekRightStats.avgLum);
        const cheekMax  = Math.max(cheekLeftStats.avgLum, cheekRightStats.avgLum, 1);
        if (cheekDiff > 30 && (cheekDiff / cheekMax) > 0.22) {
            return {
                valid: false,
                message: 'Sebagian wajah tertutup di salah satu sisi (buku/tangan). Seluruh wajah harus terlihat jelas tanpa penghalang.'
            };
        }

        // D. Cek asimetri drastis kedua mata
        const eyeDiff = Math.abs(eyeLeftStats.avgLum - eyeRightStats.avgLum);
        const eyeMax  = Math.max(eyeLeftStats.avgLum, eyeRightStats.avgLum, 1);
        if (eyeDiff > 30 && (eyeDiff / eyeMax) > 0.22) {
            return {
                valid: false,
                message: 'Salah satu mata terhalang atau tertutup benda. Kedua mata harus terlihat jelas.'
            };
        }

        // E. Cek tekstur mata: mata asli memiliki pupil dan kornea
        if (eyeLeftStats.stdDev < 6 || eyeRightStats.stdDev < 6) {
            return {
                valid: false,
                message: 'Mata tidak terlihat jelas atau tertutup penutup. Pastikan kedua mata terbuka dan jelas.'
            };
        }

        // F. Cek area mulut (masker atau tangan menutup mulut)
        if (mouthStats.stdDev < 5 || mouthStats.avgLum > 185 || mouthStats.skinRatio < 0.15) {
            return {
                valid: false,
                message: 'Mulut atau bagian bawah wajah tertutup (misal masker atau tangan). Buka penutup sebelum presensi.'
            };
        }

        // G. Cek pigmen kulit pipi
        if (cheekLeftStats.skinRatio < 0.22 || cheekRightStats.skinRatio < 0.22) {
            return {
                valid: false,
                message: 'Wajah terhalang atau tertutup benda asing. Wajah harus terbuka dan terlihat jelas.'
            };
        }

        // H. Cek kontras fitur alami hidung (lubang hidung) & mulut (garis bibir)
        // Memastikan hidung dan mulut tidak ditutupi tangan, kain, atau masker
        const nosePxX = Math.round(nose.x * canvas.width);
        const nosePxY = Math.round(nose.y * canvas.height);
        const dyMin = Math.round(eyePixelDist * 0.03);
        const dyMax = Math.round(eyePixelDist * 0.15);
        const dxSpan = Math.round(eyePixelDist * 0.20);
        let minNostrilLum = 255;
        for (let dy = dyMin; dy <= dyMax; dy++) {
            for (let dx = -dxSpan; dx <= dxSpan; dx += 2) {
                const px = Math.max(0, Math.min(canvas.width - 1, nosePxX + dx));
                const py = Math.max(0, Math.min(canvas.height - 1, nosePxY + dy));
                const d = ctx.getImageData(px, py, 1, 1).data;
                const lum = 0.299 * d[0] + 0.587 * d[1] + 0.114 * d[2];
                if (lum < minNostrilLum) minNostrilLum = lum;
            }
        }
        const hasNoseFeature = (noseStats.avgLum - minNostrilLum >= 15) || (minNostrilLum < 80);

        const mouthPxX = Math.round(mouth.x * canvas.width);
        const mouthPxY = Math.round(mouth.y * canvas.height);
        const spanY = Math.round(eyePixelDist * 0.14);
        let maxLipLum = 0;
        let minLipLum = 255;
        for (let dy = -spanY; dy <= spanY; dy += 2) {
            let rowLum = 0;
            for (let dx = -2; dx <= 2; dx++) {
                const px = Math.max(0, Math.min(canvas.width - 1, mouthPxX + dx));
                const py = Math.max(0, Math.min(canvas.height - 1, mouthPxY + dy));
                const d = ctx.getImageData(px, py, 1, 1).data;
                rowLum += 0.299 * d[0] + 0.587 * d[1] + 0.114 * d[2];
            }
            rowLum /= 5;
            if (rowLum > maxLipLum) maxLipLum = rowLum;
            if (rowLum < minLipLum) minLipLum = rowLum;
        }
        const hasMouthFeature = (maxLipLum - minLipLum >= 12) || (minLipLum < 75);

        // Jika kedua fitur (hidung dan mulut) sama sekali tidak terdeteksi kontrasnya (tertutup tangan/benda)
        if (!hasNoseFeature && !hasMouthFeature) {
            return {
                valid: false,
                message: 'Bagian hidung atau mulut terhalang atau tertutup tangan/masker. Pastikan wajah terlihat jelas tanpa penutup.'
            };
        }

        return {
            valid: true,
            message: 'Wajah terverifikasi jelas, tegak lurus, dan tidak terhalang.'
        };
    }

    // Inisialisasi Kamera Depan (Selfie)
    async function initCamera() {
        const video = document.getElementById('cameraStream');
        const cameraLoading = document.getElementById('cameraLoading');
        const btnSnap = document.getElementById('btnSnapPhoto');

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            if (cameraLoading) {
                cameraLoading.innerHTML = `
                    <i class="bi bi-camera-video-off fs-2 d-block mb-1 text-danger"></i>
                    <div class="text-danger fw-bold small">Kamera Tidak Didukung</div>
                    <div class="text-white-50" style="font-size: 0.75rem;">Peramban tidak mendukung akses kamera atau halaman belum menggunakan koneksi aman (HTTPS).</div>
                `;
            }
            return;
        }

        const constraintsList = [
            { video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }, audio: false },
            { video: { facingMode: 'user' }, audio: false },
            { video: { width: { ideal: 640 }, height: { ideal: 480 } }, audio: false },
            { video: true, audio: false }
        ];

        let stream = null;
        let lastError = null;

        for (const constraint of constraintsList) {
            try {
                stream = await navigator.mediaDevices.getUserMedia(constraint);
                if (stream) {
                    break;
                }
            } catch (err) {
                lastError = err;
                console.warn('Constraint getUserMedia gagal, mencoba alternatif...', err);
            }
        }

        if (stream) {
            streamCamera = stream;
            video.srcObject = stream;
            video.muted = true;
            try {
                await video.play();
            } catch (playErr) {
                console.warn('Video play error:', playErr);
            }
            if (cameraLoading) {
                cameraLoading.classList.add('d-none');
            }
            cameraReady = true;
            updateCaptureButton();
        } else {
            console.error('Kamera gagal diakses:', lastError);
            let errorMsg = 'Kamera tidak dapat diakses. Pastikan izin kamera telah diberikan di peramban Anda.';
            if (lastError && (lastError.name === 'NotAllowedError' || lastError.name === 'PermissionDeniedError')) {
                errorMsg = 'Akses kamera diblokir. Klik ikon gembok/setelan situs di bilah URL dan ubah Kamera menjadi Izinkan (Allow).';
            } else if (lastError && (lastError.name === 'NotFoundError' || lastError.name === 'DevicesNotFoundError')) {
                errorMsg = 'Tidak ditemukan perangkat kamera (webcam) pada komputer atau ponsel Anda.';
            }

            if (cameraLoading) {
                cameraLoading.innerHTML = `
                    <i class="bi bi-camera-video-off fs-2 d-block mb-1 text-danger"></i>
                    <div class="text-danger fw-bold small">Kamera Belum Terhubung</div>
                    <div class="text-white-50 px-2" style="font-size: 0.75rem;">${errorMsg}</div>
                `;
            }
            setFaceCheckStatus('warning', `<i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> ${errorMsg}`);
        }
    }

    // Deteksi Geolocation GPS & Rumus Haversine Client-Side
    function detectGeolocation() {
        const gpsInfo = document.getElementById('gpsInfo');
        const radiusBadge = document.getElementById('radiusBadge');
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');

        if (!navigator.geolocation) {
            gpsInfo.textContent = 'Peramban Anda tidak mendukung sensor GPS.';
            radiusBadge.className = 'badge bg-danger';
            radiusBadge.textContent = 'GPS Tidak Didukung';
            return;
        }

        radiusBadge.className = 'badge bg-warning text-dark';
        radiusBadge.textContent = 'Mencari Koordinat...';
        gpsInfo.textContent = 'Menghubungkan sensor satelit GPS...';

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                userCoordinates.lat = lat;
                userCoordinates.lng = lng;

                latInput.value = lat;
                lngInput.value = lng;

                // Hitung Jarak ke Kantor
                const distance = calculateHaversine(lat, lng, KANTOR_CONFIG.lat, KANTOR_CONFIG.lng);
                userCoordinates.distance = distance;

                const distanceFormatted = formatDistance(distance);
                if (distance <= KANTOR_CONFIG.radius) {
                    radiusBadge.className = 'badge badge-np-success';
                    radiusBadge.innerHTML = 'Dalam Radius';
                    gpsInfo.innerHTML = `Jarak ${distanceFormatted} dari kantor. Batas radius ${KANTOR_CONFIG.radius} m.`;
                } else {
                    radiusBadge.className = 'badge badge-np-danger';
                    radiusBadge.innerHTML = 'Di Luar Radius';
                    gpsInfo.innerHTML = `Jarak ${distanceFormatted} dari kantor. Batas radius ${KANTOR_CONFIG.radius} m.`;
                    setFaceCheckStatus('warning', '<strong>Presensi Belum Dapat Dikirim</strong><div class="mt-1">Periksa lokasi perangkat, izinkan GPS dan kamera, lalu coba kembali.</div>');
                }
            },
            function(err) {
                console.error('Error GPS:', err);
                radiusBadge.className = 'badge badge-np-warning';
                radiusBadge.textContent = 'GPS Belum Aktif';
                gpsInfo.innerHTML = 'Izinkan akses lokasi di pengaturan browser.';
                setFaceCheckStatus('warning', '<strong>Presensi Belum Dapat Dikirim</strong><div class="mt-1">Periksa lokasi perangkat, izinkan GPS dan kamera, lalu coba kembali.</div>');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    // Formula Haversine (Meter)
    function calculateHaversine(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    function formatDistance(distance) {
        if (distance < 1000) {
            return `${Math.round(distance)} meter`;
        }
        return `${(distance / 1000).toFixed(1).replace('.', ',')} km`;
    }

    // Pengaturan Tombol & Aksi Form
    function setupEventListeners() {
        const btnSnap = document.getElementById('btnSnapPhoto');
        const btnRetake = document.getElementById('btnRetakePhoto');
        const video = document.getElementById('cameraStream');
        const imgPreview = document.getElementById('photoPreview');
        const canvas = document.getElementById('photoCanvas');
        const fotoSelfieInput = document.getElementById('fotoSelfie');

        btnSnap.addEventListener('click', async function() {
            if (!cameraReady) {
                showAlert('warning', 'Kamera belum siap. Mohon tunggu...');
                return;
            }

            btnSnap.disabled = true;
            btnSnap.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memeriksa wajah...';

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d', { willReadFrequently: true });

            // Mirror snapshot kamera depan agar sesuai dengan pratinjau
            ctx.save();
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            ctx.restore();

            const dataUrl = canvas.toDataURL('image/jpeg', 0.88);

            let faceValid = true;
            let statusMsg = '<i class="bi bi-check-circle-fill text-success me-1"></i> Foto selfie berhasil diambil dan siap dikirim.';

            if (faceDetector && typeof faceDetector.detect === 'function') {
                try {
                    // Deteksi wajah langsung pada frame kanvas snapshot
                    const result = faceDetector.detect(canvas);
                    if (result && result.detections && result.detections.length > 0) {
                        const bestDetection = result.detections[0];
                        const score = bestDetection.categories?.[0]?.score ?? 1.0;
                        if (score < 0.70) {
                            faceValid = false;
                            statusMsg = '<strong>Wajah Belum Terdeteksi</strong><div class="mt-1">Posisikan wajah tepat di depan kamera, tegak lurus, dan tidak terhalang.</div>';
                        } else {
                            // Pemeriksaan kualitas wajah: pose lurus (tidak menoleh kiri/kanan) & tidak tertutup
                            const quality = checkFaceQuality(ctx, canvas, bestDetection);
                            if (!quality.valid) {
                                faceValid = false;
                                statusMsg = `<strong>Wajah Belum Terdeteksi</strong><div class="mt-1">${quality.message}</div>`;
                            } else {
                                faceValid = true;
                                statusMsg = '<strong>Foto Selfie Siap Dikirim</strong><div class="mt-1">Periksa hasil foto sebelum mengirim presensi.</div>';
                            }
                        }
                    } else {
                        faceValid = false;
                        statusMsg = '<strong>Wajah Belum Terdeteksi</strong><div class="mt-1">Posisikan wajah tepat di depan kamera, tegak lurus, dan tidak terhalang.</div>';
                    }
                } catch (err) {
                    console.warn('Runtime deteksi error:', err);
                    faceValid = true;
                    statusMsg = '<strong>Foto Selfie Siap Dikirim</strong><div class="mt-1">Periksa hasil foto sebelum mengirim presensi.</div>';
                }
            }

            if (!faceValid) {
                faceDetectedForCurrentPhoto = false;
                fotoSelfieInput.value = '';
                setFaceCheckStatus('warning', statusMsg);
                updateCaptureButton();
                return;
            }

            fotoSelfieInput.value = dataUrl;
            faceDetectedForCurrentPhoto = true;

            imgPreview.src = dataUrl;
            imgPreview.style.display = 'block';
            video.style.display = 'none';

            btnSnap.classList.add('d-none');
            btnRetake.classList.remove('d-none');
            setFaceCheckStatus('success', statusMsg);
        });

        btnRetake.addEventListener('click', function() {
            fotoSelfieInput.value = '';
            faceDetectedForCurrentPhoto = false;
            imgPreview.style.display = 'none';
            video.style.display = 'block';

            btnRetake.classList.add('d-none');
            btnSnap.classList.remove('d-none');
            updateCaptureButton();
            setFaceCheckStatus('default', 'Posisikan wajah tegak lurus dan tidak terhalang.');
        });

        // Tombol Refresh GPS
        const btnRefreshGps = document.getElementById('btnRefreshGps');
        if (btnRefreshGps) {
            btnRefreshGps.addEventListener('click', detectGeolocation);
        }

        // Tombol Submit Presensi
        const btnSubmit = document.getElementById('btnSubmitPresensi');
        if (btnSubmit) {
            btnSubmit.addEventListener('click', submitPresensi);
        }

        // Tombol Aktifkan Notifikasi Web Push
        const btnPush = document.getElementById('btnAktifkanPush');
        if (btnPush) {
            btnPush.addEventListener('click', function(e) {
                e.preventDefault();
                if (currentPresensiAnggotaId) {
                    requestPushSubscription(currentPresensiAnggotaId);
                } else {
                    console.warn('ID Anggota belum tersedia');
                }
            });
        }
    }

    // Status pengiriman presensi (mencegah klik ganda)
    let isSubmittingPresensi = false;

    // Fungsi Kirim Presensi
    async function submitPresensi() {
        if (isSubmittingPresensi) return;

        hideAlert();

        const nrpInput = document.getElementById('nrp');
        const nrp = nrpInput ? nrpInput.value.trim() : '{{ $anggota->nrp }}';
        const jenisPresensi = document.querySelector('input[name="jenis_presensi"]:checked')?.value;
        const lat = document.getElementById('latitude').value;
        const lng = document.getElementById('longitude').value;
        const foto = document.getElementById('fotoSelfie').value;

        // Validasi Sisi Klien
        if (!nrp) {
            showAlert('danger', 'NRP wajib diisi.');
            document.getElementById('nrp').focus();
            return;
        }
        if (!lat || !lng) {
            showAlert('danger', 'Koordinat GPS belum terdeteksi. Pastikan GPS perangkat aktif dan izin lokasi telah disetujui.');
            return;
        }
        if (!foto) {
            showAlert('danger', 'Silakan ambil foto selfie kamera depan terlebih dahulu.');
            return;
        }
        if (!faceDetectedForCurrentPhoto) {
            showAlert('danger', 'Presensi tidak dapat dikirim sebelum foto selfie diambil dan terverifikasi.');
            return;
        }

        isSubmittingPresensi = true;
        const btnSubmit = document.getElementById('btnSubmitPresensi');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan presensi...';

        try {
            const response = await fetch("{{ route('presensi.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    nrp: nrp,
                    jenis_presensi: jenisPresensi,
                    latitude: lat,
                    longitude: lng,
                    foto_selfie: foto
                })
            });

            const result = await response.json();

            if (response.ok && result.sukses) {
                // Kunci tombol agar tidak tertekan ganda
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Presensi Tercatat';

                // Sinkronkan badge kartu identitas secara langsung
                if (jenisPresensi === 'masuk') {
                    const elMasuk = document.getElementById('badgeStatusMasuk');
                    if (elMasuk) {
                        elMasuk.className = 'badge bg-success-subtle text-success border border-success-subtle fw-semibold';
                        elMasuk.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Sudah (${result.data.waktu || ''} WITA)`;
                    }
                    const radioPulang = document.getElementById('jenisPulang');
                    if (radioPulang) radioPulang.checked = true;
                } else if (jenisPresensi === 'pulang') {
                    const elPulang = document.getElementById('badgeStatusPulang');
                    if (elPulang) {
                        elPulang.className = 'badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold';
                        elPulang.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Sudah (${result.data.waktu || ''} WITA)`;
                    }
                }

                // Tampilkan Data pada Modal Sukses
                const msgEl = document.getElementById('successMessageModal');
                if (msgEl) msgEl.textContent = result.pesan;

                const namaPersonel = (result.data && result.data.nama) 
                    ? result.data.nama 
                    : (document.getElementById('personelNama') && document.getElementById('personelNama').textContent.trim() !== '-' 
                        ? document.getElementById('personelNama').textContent.trim() 
                        : nrp);
                const pEl = document.getElementById('modalPersonel');
                if (pEl) pEl.textContent = namaPersonel;

                const jEl = document.getElementById('modalJenis');
                if (jEl) jEl.textContent = (result.data && result.data.jenis_presensi) ? result.data.jenis_presensi.toUpperCase() : jenisPresensi.toUpperCase();

                const wEl = document.getElementById('modalWaktu');
                if (wEl) wEl.textContent = ((result.data && result.data.waktu) ? result.data.waktu : '') + ' WITA';
                
                const statusEl = document.getElementById('modalStatus');
                if (statusEl) {
                    if (result.data && result.data.status_kehadiran === 'tepat_waktu') {
                        statusEl.className = 'badge bg-success';
                        statusEl.textContent = 'Tepat Waktu';
                    } else {
                        statusEl.className = 'badge bg-danger';
                        statusEl.textContent = 'Terlambat';
                    }
                }

                currentPresensiAnggotaId = result.id_anggota;

                // Tampilkan Modal Sukses SEGERA agar pengguna langsung melihat konfirmasi
                const modalEl = document.getElementById('successModal');
                if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                }

                // Periksa apakah browser saat ini sudah memiliki Web Push subscription aktif di domain ini (asinkron tanpa memblokir modal)
                const pushPromptSection = document.getElementById('pushPromptSection');
                if (('Notification' in window) && ('serviceWorker' in navigator)) {
                    try {
                        const swTimeout = new Promise((_, reject) => setTimeout(() => reject(new Error('SW timeout')), 1500));
                        const reg = await Promise.race([navigator.serviceWorker.ready, swTimeout]);
                        const existingSub = await reg.pushManager.getSubscription();
                        // Jika belum ada subscription aktif di origin domain ini ATAU izin belum granted
                        if (!existingSub || Notification.permission !== 'granted') {
                            pushPromptSection.classList.remove('d-none');
                        } else {
                            pushPromptSection.classList.add('d-none');
                        }
                    } catch (pushErr) {
                        console.warn('Cek status push subscription browser:', pushErr);
                        if (Notification.permission !== 'granted' || result.minta_izin_notifikasi) {
                            pushPromptSection.classList.remove('d-none');
                        } else {
                            pushPromptSection.classList.add('d-none');
                        }
                    }
                } else {
                    pushPromptSection.classList.add('d-none');
                }
            } else {
                isSubmittingPresensi = false;
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-send-fill me-1"></i> KIRIM PRESENSI SEKARANG';

                if (result.pesan && result.pesan.includes('sudah melakukan presensi MASUK')) {
                    // Sinkronkan badge kartu agar konsisten dengan data server
                    const elMasuk = document.getElementById('badgeStatusMasuk');
                    if (elMasuk) {
                        elMasuk.className = 'badge bg-success-subtle text-success border border-success-subtle fw-semibold';
                        elMasuk.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Sudah Tercatat Hari Ini';
                    }
                    const radioPulang = document.getElementById('jenisPulang');
                    if (radioPulang) radioPulang.checked = true;

                    showAlert('info', '<i class="bi bi-info-circle-fill me-1"></i> Presensi <strong>MASUK</strong> Anda untuk hari ini sudah tersimpan di sistem. Tombol pilihan otomatis dialihkan ke <strong>Presensi Pulang</strong>.');
                } else {
                    showAlert('danger', result.pesan || 'Terjadi kesalahan saat menyimpan presensi.');
                }
            }
        } catch (err) {
            isSubmittingPresensi = false;
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-send-fill me-1"></i> KIRIM PRESENSI SEKARANG';
            showAlert('danger', 'Gagal mengirim data ke server. Periksa koneksi internet Anda.');
        }
    }

    // Penanganan Pendaftaran Web Push (VAPID)
    async function requestPushSubscription(idAnggota) {
        const btn = document.getElementById('btnAktifkanPush');
        const statusInfo = document.getElementById('pushStatusInfo');
        statusInfo.className = 'd-none';
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengaktifkan...';

        try {
            if (Notification.permission === 'denied') {
                statusInfo.className = 'text-danger small mt-2 d-block';
                statusInfo.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Izin notifikasi diblokir di peramban ini. Klik ikon gembok/setelan situs di samping bilah URL untuk mengubah Notifikasi ke <strong>Izinkan (Allow)</strong>, lalu coba lagi.';
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Coba Lagi Setelah Diizinkan';
                return;
            }

            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                statusInfo.className = 'text-danger small mt-2 d-block';
                statusInfo.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> Izin notifikasi tidak diberikan oleh peramban.';
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-bell me-1"></i> Izinkan Pengingat Otomatis';
                return;
            }

            const vapidPublicKey = "{{ config('presensi.vapid.public_key') }}";
            if (!vapidPublicKey) {
                statusInfo.className = 'text-warning small mt-2 d-block';
                statusInfo.textContent = 'Kunci VAPID belum dikonfigurasi di server.';
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-bell me-1"></i> Izinkan Pengingat Otomatis';
                return;
            }

            const registration = await Promise.race([
                navigator.serviceWorker.ready,
                new Promise((_, reject) => setTimeout(() => reject(new Error('Koneksi Service Worker timeout.')), 5000))
            ]);

            const convertedVapidKey = urlBase64ToUint8Array(vapidPublicKey);
            let subscription = await registration.pushManager.getSubscription();
            if (!subscription) {
                subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: convertedVapidKey
                });
            }

            const res = await fetch("{{ route('presensi.subscribe') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    id_anggota: idAnggota,
                    subscription: subscription.toJSON(),
                    jenis_perangkat: getDeviceInfo()
                })
            });

            const resData = await res.json();
            if (res.ok && resData.sukses) {
                btn.classList.add('d-none');
                statusInfo.className = 'text-success small mt-2 d-block fw-bold';
                statusInfo.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + (resData.pesan || 'Pengingat otomatis berhasil diaktifkan!');
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Coba Lagi';
                statusInfo.className = 'text-danger small mt-2 d-block';
                statusInfo.textContent = resData.pesan || 'Gagal menyimpan perangkat.';
            }
        } catch (e) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Coba Lagi';
            statusInfo.className = 'text-danger small mt-2 d-block';
            statusInfo.textContent = 'Gagal mengaktifkan Web Push: ' + e.message;
        }
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    function getDeviceInfo() {
        const ua = navigator.userAgent;
        if (/android/i.test(ua)) return 'Android HP';
        if (/iPad|iPhone|iPod/.test(ua)) return 'iOS Device';
        if (/windows/i.test(ua)) return 'Windows PC';
        return 'Web Browser';
    }

    function showAlert(type, message) {
        const box = document.getElementById('alertBox');
        box.className = `alert alert-${type} alert-dismissible fade show small mb-4`;
        box.innerHTML = `
            <i class="bi bi-exclamation-circle-fill me-2"></i> ${message}
            <button type="button" class="btn-close" onclick="hideAlert()"></button>
        `;
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function hideAlert() {
        const box = document.getElementById('alertBox');
        box.className = 'alert d-none small mb-4';
        box.innerHTML = '';
    }
</script>
@endpush

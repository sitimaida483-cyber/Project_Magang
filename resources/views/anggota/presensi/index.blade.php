@extends('layouts.anggota')

@section('title', 'Presensi Kehadiran Personel')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/presensi.css') }}?v={{ file_exists(public_path('css/presensi.css')) ? filemtime(public_path('css/presensi.css')) : time() }}">
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8 col-xl-7">
        <div class="card card-custom p-4 presensi-card border-0 shadow-sm rounded-4 bg-white">
    <!-- Tanggal & Informasi Hari Ini -->
    <div class="text-center pb-3 border-bottom mb-4">
        <h6 class="fw-bold text-dark mb-1">
            <i class="bi bi-calendar-event text-primary me-1"></i>
            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
        </h6>
        <small class="text-muted">
            Jam Masuk: <strong>{{ substr($pengaturan->jam_masuk ?? '07:30', 0, 5) }}</strong> | 
            Jam Pulang: <strong>{{ substr($pengaturan->jam_keluar ?? '16:00', 0, 5) }}</strong> WITA
        </small>
    </div>

    <!-- Alert Status Keterangan Surat Hari Ini (Jika Disetujui) -->
    @if(isset($suratHariIni) && $suratHariIni)
        <div class="alert {{ $bebasPresensi ? 'alert-info' : 'alert-warning' }} border-0 shadow-sm rounded-3 mb-4">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-file-earmark-text-fill fs-5 text-primary"></i>
                <strong class="fs-6 text-dark">Hari ini: {{ $suratHariIni->label_jenis }}</strong>
                <span class="badge bg-success ms-auto"><i class="bi bi-check-circle me-1"></i> Disetujui</span>
            </div>
            <div class="small text-secondary">
                Periode: <strong>{{ \Carbon\Carbon::parse($suratHariIni->tanggal_mulai)->translatedFormat('d M Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($suratHariIni->tanggal_selesai)->translatedFormat('d M Y') }}</strong>
                @if($bebasPresensi)
                    <div class="mt-2 text-primary fw-semibold bg-white p-2 rounded border border-info-subtle">
                        <i class="bi bi-shield-check me-1"></i> Anda memiliki surat keterangan yang telah disetujui. Presensi lokasi hari ini <strong>tidak diwajibkan</strong>.
                    </div>
                @else
                    <div class="mt-2 text-dark bg-white p-2 rounded border">
                        <i class="bi bi-info-circle me-1"></i> Keterangan kedinasan aktif. Presensi lokasi tetap dapat dilakukan sesuai ketentuan.
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Alert Notifikasi Global -->
    <div id="alertBox" class="alert d-none small mb-4" role="alert"></div>

    <!-- FORM PRESENSI -->
    <form id="formPresensi" onsubmit="return false;">
        @csrf

        <!-- IDENTITAS PERSONEL (OTOMATIS DARI AKUN LOGIN) -->
        <div class="mb-4">
            <label class="form-label fw-bold text-dark small mb-2 d-flex justify-content-between align-items-center">
                <span>Identitas Personel</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle">
                    <i class="bi bi-shield-lock-fill me-1"></i> Terautentikasi
                </span>
            </label>

            <!-- Input tersembunyi NRP untuk pengiriman payload -->
            <input type="hidden" id="nrp" name="nrp" value="{{ $anggota->nrp }}">

            <!-- Kartu Identitas Personel -->
            <div id="identityCard" class="p-3 bg-light rounded-3 border">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 fs-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark fs-6" id="personelNama">{{ $anggota->nama }}</div>
                        <div class="small text-secondary font-monospace" id="personelNrp">NRP: {{ $anggota->nrp }}</div>
                        <div class="small text-muted" id="personelPangkat">{{ $anggota->jabatan }} &bull; {{ $anggota->unit_kerja }}</div>
                    </div>
                </div>

                <!-- Status Presensi Hari Ini -->
                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center small">
                    <div>
                        <span class="text-muted d-block" style="font-size: 0.75rem;">Presensi Masuk:</span>
                        @if($presensiMasuk)
                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i> Sudah ({{ \Carbon\Carbon::parse($presensiMasuk->waktu)->format('H:i') }} WITA)
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border">Belum Masuk</span>
                        @endif
                    </div>
                    <div class="text-end">
                        <span class="text-muted d-block" style="font-size: 0.75rem;">Presensi Pulang:</span>
                        @if($presensiPulang)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i> Sudah ({{ \Carbon\Carbon::parse($presensiPulang->waktu)->format('H:i') }} WITA)
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border">Belum Pulang</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- PILIH JENIS PRESENSI -->
        <div class="mb-4">
            <label class="form-label fw-bold text-dark small mb-2">
                Jenis Presensi
            </label>
            <div class="row g-2 presensi-type-choice">
                <div class="col-6">
                    <input type="radio" class="btn-check" name="jenis_presensi" id="jenisMasuk" value="masuk" 
                           {{ (!$presensiMasuk || ($presensiMasuk && $presensiPulang)) ? 'checked' : '' }}>
                    <label class="btn btn-outline-success w-100 py-2 d-flex flex-column align-items-center justify-content-center presensi-type-button presensi-type-button--masuk" for="jenisMasuk">
                        <i class="bi bi-box-arrow-in-right fs-4 mb-1"></i>
                        <span class="fw-bold small">Presensi Masuk</span>
                    </label>
                </div>
                <div class="col-6">
                    <input type="radio" class="btn-check" name="jenis_presensi" id="jenisPulang" value="pulang" 
                           {{ ($presensiMasuk && !$presensiPulang) ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center presensi-type-button presensi-type-button--pulang" for="jenisPulang">
                        <i class="bi bi-box-arrow-right fs-4 mb-1"></i>
                        <span class="fw-bold small">Presensi Pulang</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- DETEKSI GPS & RADIUS KANTOR -->
        <div class="mb-4">
            <label class="form-label fw-bold d-flex align-items-center justify-content-between text-dark small mb-2">
                <span>Lokasi GPS</span>
                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0" id="btnRefreshGps">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh GPS
                </button>
            </label>
            
            <div class="p-3 bg-light rounded-3 border">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Status Posisi:</span>
                    <span id="radiusBadge" class="badge bg-secondary">Mendeteksi GPS...</span>
                </div>
                <div class="small text-secondary" id="gpsInfo">
                    Sedang mencari sinyal satelit GPS perangkat Anda...
                </div>
                <!-- Input Hidden Koordinat -->
                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">
            </div>
        </div>

        <!-- KAMERA SELFIE DEPAN & DETEKSI WAJAH -->
        <div class="mb-4">
            <label class="form-label fw-bold text-dark small mb-2 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-camera-fill text-primary me-1"></i> Foto Selfie Kamera Depan</span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" id="cameraStatusBadge">
                    <i class="bi bi-shield-check me-1"></i> Biometrik Wajah
                </span>
            </label>
            
            <div class="camera-container mb-2 position-relative rounded-4 overflow-hidden shadow-sm" style="aspect-ratio: 4/3; max-height: 400px; background-color: #0b1a2e; border: 2px solid #cbd5e1;">
                <video id="cameraStream" autoplay playsinline muted style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); display: block;"></video>
                <img id="photoPreview" alt="Pratinjau Foto Selfie" style="width: 100%; height: 100%; object-fit: cover; display: none;">

                <!-- Spinner Loading Kamera -->
                <div id="cameraLoading" class="position-absolute top-50 start-50 translate-middle text-center text-white p-3" style="z-index: 10;">
                    <div class="spinner-border spinner-border-sm text-warning mb-2" role="status"></div>
                    <div class="small fw-semibold">Menghubungkan Kamera Depan...</div>
                    <div class="text-white-50" style="font-size: 0.75rem;">Mohon izinkan akses kamera jika diminta peramban</div>
                </div>
            </div>

            <div id="faceCheckStatus" class="alert alert-info py-2 small mb-2" role="status" aria-live="polite">
                <i class="bi bi-info-circle me-1"></i> Menyiapkan kamera dan deteksi wajah...
            </div>

            <!-- Tombol Kamera -->
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary btn-sm flex-grow-1 py-2 fw-semibold shadow-sm" id="btnSnapPhoto" disabled>
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span> Menghubungkan Kamera...
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm d-none py-2 px-3 fw-semibold" id="btnRetakePhoto">
                    <i class="bi bi-arrow-repeat me-1"></i> Foto Ulang
                </button>
            </div>
            <!-- Canvas Tersembunyi untuk Snapshot -->
            <canvas id="photoCanvas" class="d-none"></canvas>
            <input type="hidden" id="fotoSelfie" name="foto_selfie">
        </div>

        <!-- TOMBOL SUBMIT PRESENSI -->
        @if(isset($bebasPresensi) && $bebasPresensi)
            <div class="text-center p-3 bg-light rounded-3 border mb-2">
                <i class="bi bi-shield-check text-success fs-2 d-block mb-1"></i>
                <h6 class="fw-bold text-dark mb-1">Presensi Lokasi Tidak Diwajibkan</h6>
                <p class="text-muted small mb-0">
                    Anda sedang dalam status <strong>{{ $suratHariIni->label_jenis }}</strong> yang telah disetujui untuk hari ini.
                </p>
            </div>
            <button type="button" id="btnSubmitPresensi" class="btn btn-secondary w-100 py-3 fw-bold shadow-sm" disabled>
                <i class="bi bi-slash-circle me-1"></i> TOMBOL PRESENSI DINONAKTIFKAN
            </button>
        @else
            <button type="button" id="btnSubmitPresensi" class="btn btn-primary w-100 py-3 fw-bold shadow presensi-submit-button">
                <i class="bi bi-send-fill me-1"></i> KIRIM PRESENSI SEKARANG
            </button>
        @endif
    </form>
        </div>
    </div>
</div>
@endsection

@push('modals')
<!-- Modal Sukses Presensi -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="rounded-circle bg-success text-white mx-auto d-flex align-items-center justify-content-center mb-3" 
                     style="width: 70px; height: 70px; font-size: 2.2rem;">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Presensi Berhasil!</h4>
                <p class="text-muted small mb-3" id="successMessageModal">-</p>

                <div class="p-3 bg-light rounded text-start small mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Personel:</span>
                        <strong id="modalPersonel">-</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Jenis Presensi:</span>
                        <span class="badge bg-primary text-uppercase" id="modalJenis">-</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Waktu Catat:</span>
                        <strong id="modalWaktu">- WITA</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Status Ketepatan:</span>
                        <span id="modalStatus">-</span>
                    </div>
                </div>

                <!-- Bagian Penawaran Izin Web Push (Khusus Presensi Pertama) -->
                <div id="pushPromptSection" class="d-none p-3 bg-warning bg-opacity-10 border border-warning rounded text-start small mb-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-bell-fill text-warning fs-5"></i>
                        <strong class="text-dark">Aktifkan Pengingat Presensi?</strong>
                    </div>
                    <p class="text-muted mb-2" style="font-size: 0.8rem;">
                        Perangkat ini belum terdaftar untuk menerima pengingat otomatis. Dapatkan notifikasi pengingat sebelum jam masuk dan jam pulang kantor.
                    </p>
                    <button type="button" class="btn btn-warning btn-sm w-100 fw-bold" id="btnAktifkanPush">
                        <i class="bi bi-bell me-1"></i> Izinkan Pengingat Otomatis
                    </button>
                    <div id="pushStatusInfo" class="text-success small mt-1 d-none text-center"></div>
                </div>

                <button type="button" class="btn btn-primary w-100 presensi-submit-button" data-bs-dismiss="modal" onclick="location.reload();">
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
        status.className = `alert alert-${type} py-2 small mb-2`;
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

        // C. Cek asimetri drastis pipi (misal satu sisi muka tertutup buku seperti pada foto)
        const cheekDiff = Math.abs(cheekLeftStats.avgLum - cheekRightStats.avgLum);
        const cheekMax  = Math.max(cheekLeftStats.avgLum, cheekRightStats.avgLum, 1);
        if (cheekDiff > 35 && (cheekDiff / cheekMax) > 0.28) {
            return {
                valid: false,
                message: 'Sebagian wajah tertutup di salah satu sisi. Seluruh wajah harus terlihat jelas tanpa penghalang.'
            };
        }

        // D. Cek asimetri drastis kedua mata
        const eyeDiff = Math.abs(eyeLeftStats.avgLum - eyeRightStats.avgLum);
        const eyeMax  = Math.max(eyeLeftStats.avgLum, eyeRightStats.avgLum, 1);
        if (eyeDiff > 35 && (eyeDiff / eyeMax) > 0.28) {
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

        // H. Cek tangan menutupi mulut/hidung (tangan/lengan melintang dari tepi layar ke arah wajah)
        const armY0 = Math.max(0, Math.round((nose.y - eyeNormDist * 0.15) * canvas.height));
        const armY1 = Math.min(canvas.height, Math.round((mouth.y + eyeNormDist * 0.45) * canvas.height));
        const armH  = Math.max(10, armY1 - armY0);

        // Cek sisi kiri (apakah ada lengan/tangan masuk dari sisi kiri menutupi wajah)
        const leftLimitX = Math.round(Math.max(15, (nose.x - eyeNormDist * 0.55) * canvas.width));
        if (leftLimitX > 25) {
            const leftData = ctx.getImageData(0, armY0, leftLimitX, armH).data;
            let leftSkin = 0;
            const totalLeft = leftData.length / 4;
            for (let i = 0; i < leftData.length; i += 4) {
                const r = leftData[i], g = leftData[i + 1], b = leftData[i + 2];
                if (r > g - 5 && r > b + 10 && (r + g + b) > 70 && (r + g + b) < 680) {
                    leftSkin++;
                }
            }
            if ((leftSkin / totalLeft) > 0.38) {
                return {
                    valid: false,
                    message: 'Terdeteksi tangan menutupi wajah. Jauhkan tangan dari wajah saat melakukan presensi.'
                };
            }
        }

        // Cek sisi kanan (apakah ada lengan/tangan masuk dari sisi kanan menutupi wajah)
        const rightStartX = Math.round(Math.min(canvas.width - 25, (nose.x + eyeNormDist * 0.55) * canvas.width));
        const rightW = canvas.width - rightStartX;
        if (rightW > 25) {
            const rightData = ctx.getImageData(rightStartX, armY0, rightW, armH).data;
            let rightSkin = 0;
            const totalRight = rightData.length / 4;
            for (let i = 0; i < rightData.length; i += 4) {
                const r = rightData[i], g = rightData[i + 1], b = rightData[i + 2];
                if (r > g - 5 && r > b + 10 && (r + g + b) > 70 && (r + g + b) < 680) {
                    rightSkin++;
                }
            }
            if ((rightSkin / totalRight) > 0.38) {
                return {
                    valid: false,
                    message: 'Terdeteksi tangan menutupi wajah. Jauhkan tangan dari wajah saat melakukan presensi.'
                };
            }
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
                    radiusBadge.className = 'badge bg-success';
                    radiusBadge.innerHTML = `<i class="bi bi-check-circle me-1"></i> Dalam Radius (${distanceFormatted})`;
                    gpsInfo.innerHTML = `Lokasi terverifikasi di kantor <strong>${KANTOR_CONFIG.nama}</strong>. Koordinat: <span class="font-monospace">${lat.toFixed(6)}, ${lng.toFixed(6)}</span>`;
                } else {
                    radiusBadge.className = 'badge bg-danger';
                    radiusBadge.innerHTML = `<i class="bi bi-x-circle me-1"></i> Luar Radius (${distanceFormatted})`;
                    gpsInfo.innerHTML = `<span class="text-danger fw-semibold">Jarak Anda ${distanceFormatted} dari kantor!</span> Batas radius presensi adalah ${KANTOR_CONFIG.radius} meter.`;
                }
            },
            function(err) {
                console.error('Error GPS:', err);
                radiusBadge.className = 'badge bg-danger';
                radiusBadge.textContent = 'GPS Mati / Ditolak';
                gpsInfo.innerHTML = `<span class="text-danger">Izin lokasi GPS tidak aktif atau ditolak. Silakan aktifkan GPS dan refresh halaman.</span>`;
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
                            statusMsg = '<i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Wajah tidak terdeteksi secara utuh. Pastikan seluruh wajah (mata, hidung, mulut) terlihat jelas tanpa tertutup tangan atau benda.';
                        } else {
                            // Pemeriksaan kualitas wajah: pose lurus (tidak menoleh kiri/kanan) & tidak tertutup
                            const quality = checkFaceQuality(ctx, canvas, bestDetection);
                            if (!quality.valid) {
                                faceValid = false;
                                statusMsg = `<i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> ${quality.message}`;
                            } else {
                                faceValid = true;
                                statusMsg = '<i class="bi bi-check-circle-fill text-success me-1"></i> Wajah terverifikasi jelas, tegak lurus, dan tidak terhalang.';
                            }
                        }
                    } else {
                        faceValid = false;
                        statusMsg = '<i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Wajah tidak terdeteksi. Posisikan wajah Anda tepat di depan kamera.';
                    }
                } catch (err) {
                    console.warn('Runtime deteksi error:', err);
                    faceValid = true;
                    statusMsg = '<i class="bi bi-check-circle-fill text-success me-1"></i> Foto selfie berhasil diambil.';
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
            setFaceCheckStatus('info', '<i class="bi bi-info-circle me-1"></i> Posisikan wajah Anda tegak lurus menghadap kamera dan ambil foto selfie.');
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

    // Fungsi Kirim Presensi
    async function submitPresensi() {
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
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-send-fill me-1"></i> KIRIM PRESENSI SEKARANG';

            if (response.ok && result.sukses) {
                // Tampilkan Modal Sukses
                document.getElementById('successMessageModal').textContent = result.pesan;
                const namaPersonel = (result.data && result.data.nama) 
                    ? result.data.nama 
                    : (document.getElementById('personelNama') && document.getElementById('personelNama').textContent.trim() !== '-' 
                        ? document.getElementById('personelNama').textContent.trim() 
                        : nrp);
                document.getElementById('modalPersonel').textContent = namaPersonel;
                document.getElementById('modalJenis').textContent = result.data.jenis_presensi;
                document.getElementById('modalWaktu').textContent = result.data.waktu;
                
                const statusEl = document.getElementById('modalStatus');
                if (result.data.status_kehadiran === 'tepat_waktu') {
                    statusEl.className = 'badge bg-success';
                    statusEl.textContent = 'Tepat Waktu';
                } else {
                    statusEl.className = 'badge bg-danger';
                    statusEl.textContent = 'Terlambat';
                }

                currentPresensiAnggotaId = result.id_anggota;
                const pushPromptSection = document.getElementById('pushPromptSection');
                if (result.minta_izin_notifikasi && ('Notification' in window) && ('serviceWorker' in navigator)) {
                    pushPromptSection.classList.remove('d-none');
                } else {
                    pushPromptSection.classList.add('d-none');
                }

                const modal = new bootstrap.Modal(document.getElementById('successModal'));
                modal.show();
            } else {
                showAlert('danger', result.pesan || 'Terjadi kesalahan saat menyimpan presensi.');
            }
        } catch (err) {
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

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

        <!-- KAMERA SELFIE DEPAN -->
        <div class="mb-4">
            <label class="form-label fw-bold text-dark small mb-2">
                Foto Selfie Kamera Depan
            </label>
            
            <div class="camera-container mb-2">
                <video id="cameraStream" autoplay playsinline></video>
                <img id="photoPreview" alt="Pratinjau Foto Selfie">
                <div id="cameraLoading" class="text-white small text-center p-3">
                    <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
                    <div>Menghubungkan kamera...</div>
                </div>
            </div>
            <div id="faceCheckStatus" class="alert alert-info py-2 small" role="status" aria-live="polite">
                Menyiapkan pemeriksaan wajah...
            </div>

            <!-- Tombol Kamera -->
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-dark btn-sm flex-grow-1" id="btnSnapPhoto" disabled>
                    <i class="bi bi-camera-fill me-1"></i> Menyiapkan deteksi wajah...
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="btnRetakePhoto">
                    <i class="bi bi-arrow-repeat me-1"></i> Foto Ulang
                </button>
            </div>
            <!-- Canvas Tersembunyi untuk Snapshot -->
            <canvas id="photoCanvas" class="d-none"></canvas>
            <input type="hidden" id="fotoSelfie" name="foto_selfie">
        </div>

        <!-- TANDA TANGAN DIGITAL -->
        <div class="mb-4">
            <label class="form-label fw-bold d-flex align-items-center justify-content-between text-dark small mb-2">
                <span>Tanda Tangan Digital</span>
                <button type="button" class="btn btn-link btn-sm text-decoration-none text-danger p-0" id="btnClearSignature">
                    <i class="bi bi-eraser me-1"></i> Bersihkan
                </button>
            </label>

            <div class="signature-container">
                <canvas id="signaturePad"></canvas>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                Gunakan jari atau stylus Anda untuk membubuhkan tanda tangan di dalam kotak.
            </small>
            <input type="hidden" id="tandaTangan" name="tanda_tangan">
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
<!-- Library Signature Pad -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

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
    let signaturePad = null;
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

        initSignaturePad();
        initCamera();
        initFaceDetector();
        detectGeolocation();
        setupEventListeners();
    });

    async function initFaceDetector() {
        const status = document.getElementById('faceCheckStatus');
        status.textContent = 'Memuat model pemeriksaan wajah...';

        try {
            faceDetector = await window.createFaceDetector();
            updateCaptureButton();
            status.className = 'alert alert-info py-2 small';
            status.textContent = 'Wajah diperiksa di perangkat ini. Foto tetap dikirim ke server saat presensi disimpan.';
        } catch (error) {
            console.error('Model deteksi wajah gagal dimuat:', error);
            status.className = 'alert alert-danger py-2 small';
            status.textContent = 'Pemeriksaan wajah gagal dimuat. Periksa koneksi internet lalu muat ulang halaman.';
            updateCaptureButton();
        }
    }

    function updateCaptureButton() {
        const button = document.getElementById('btnSnapPhoto');
        button.disabled = !cameraReady || !faceDetector;
        button.innerHTML = button.disabled
            ? '<i class="bi bi-camera-fill me-1"></i> Menyiapkan deteksi wajah...'
            : '<i class="bi bi-camera-fill me-1"></i> Ambil Foto Selfie';
    }

    function setFaceCheckStatus(type, message) {
        const status = document.getElementById('faceCheckStatus');
        status.className = `alert alert-${type} py-2 small`;
        status.textContent = message;
    }

    function isFrontFacingFace(detection) {
        const [firstEye, secondEye, nose, mouth] = detection.keypoints || [];
        if (!firstEye || !secondEye || !nose || !mouth) {
            return false;
        }

        const eyeDistance = Math.hypot(firstEye.x - secondEye.x, firstEye.y - secondEye.y);
        if (eyeDistance < 0.05) {
            return false;
        }

        const eyeCenterX = (firstEye.x + secondEye.x) / 2;
        const eyeCenterY = (firstEye.y + secondEye.y) / 2;

        // 1. Simetri hidung di antara kedua mata (Tolak jika menoleh ke kanan / kiri)
        const noseOffset = Math.abs(nose.x - eyeCenterX) / eyeDistance;
        if (noseOffset > 0.22) {
            return false;
        }

        // 2. Keseimbangan jarak 3D hidung ke mata kiri vs mata kanan
        // Saat menoleh ke samping, salah satu mata mendekat ke hidung secara signifikan
        const distToEye1 = Math.hypot(nose.x - firstEye.x, nose.y - firstEye.y);
        const distToEye2 = Math.hypot(nose.x - secondEye.x, nose.y - secondEye.y);
        const eyeRatio = Math.min(distToEye1, distToEye2) / Math.max(distToEye1, distToEye2);
        if (eyeRatio < 0.78) {
            return false;
        }

        // 3. Simetri mulut di bawah hidung
        const mouthOffset = Math.abs(mouth.x - eyeCenterX) / eyeDistance;
        if (mouthOffset > 0.24) {
            return false;
        }

        // 4. Kemiringan kepala (Roll / Eye Tilt) - harus tegak
        const eyeTilt = Math.abs(firstEye.y - secondEye.y) / eyeDistance;
        if (eyeTilt > 0.22) {
            return false;
        }

        // 5. Susunan vertikal: hidung di bawah mata, mulut di bawah hidung
        const noseBelowEyes = nose.y >= (eyeCenterY + 0.12 * eyeDistance);
        const mouthBelowNose = mouth.y >= (nose.y + 0.18 * eyeDistance);
        if (!noseBelowEyes || !mouthBelowNose) {
            return false;
        }

        // 6. Jika telinga terdeteksi, periksa keseimbangan jarak telinga
        const [, , , , rightEar, leftEar] = detection.keypoints || [];
        if (rightEar && leftEar && rightEar.x !== undefined && leftEar.x !== undefined) {
            const earDist1 = Math.hypot(nose.x - rightEar.x, nose.y - rightEar.y);
            const earDist2 = Math.hypot(nose.x - leftEar.x, nose.y - leftEar.y);
            if (earDist1 > 0 && earDist2 > 0) {
                const earRatio = Math.min(earDist1, earDist2) / Math.max(earDist1, earDist2);
                if (earRatio < 0.58) {
                    return false;
                }
            }
        }

        return true;
    }

    function hasVisibleFacialFeatures(video, detection) {
        const [firstEye, secondEye, nose, mouth] = detection.keypoints || [];
        if (!firstEye || !secondEye || !nose || !mouth) {
            return false;
        }

        // Pastikan fitur utama tidak terpotong di tepi bingkai kamera
        const allInside = [firstEye, secondEye, nose, mouth].every(
            (p) => p.x >= 0.04 && p.x <= 0.96 && p.y >= 0.04 && p.y <= 0.96
        );
        if (!allInside) {
            return false;
        }

        if (!video.videoWidth || !video.videoHeight) {
            return true;
        }

        const eyeDistance = Math.hypot(
            (firstEye.x - secondEye.x) * video.videoWidth,
            (firstEye.y - secondEye.y) * video.videoHeight
        );
        const cropSize = Math.max(16, Math.round(eyeDistance * 0.35));
        const sampleCanvas = document.createElement('canvas');
        sampleCanvas.width = 24;
        sampleCanvas.height = 24;
        const context = sampleCanvas.getContext('2d', { willReadFrequently: true });

        if (!context || video.videoWidth < cropSize || video.videoHeight < cropSize) {
            return true;
        }

        function sampleRegion(normX, normY, size) {
            const centerX = normX * video.videoWidth;
            const centerY = normY * video.videoHeight;
            const left = Math.max(0, Math.min(video.videoWidth - size, centerX - size / 2));
            const top = Math.max(0, Math.min(video.videoHeight - size, centerY - size / 2));

            context.drawImage(video, left, top, size, size, 0, 0, sampleCanvas.width, sampleCanvas.height);
            const pixels = context.getImageData(0, 0, sampleCanvas.width, sampleCanvas.height).data;

            let totalLum = 0;
            let totalLumSq = 0;
            let totalR = 0;
            let totalG = 0;
            let totalB = 0;
            let skinToneCount = 0;
            const count = pixels.length / 4;

            for (let i = 0; i < pixels.length; i += 4) {
                const r = pixels[i];
                const g = pixels[i + 1];
                const b = pixels[i + 2];
                const lum = 0.299 * r + 0.587 * g + 0.114 * b;
                totalLum += lum;
                totalLumSq += lum * lum;
                totalR += r;
                totalG += g;
                totalB += b;

                // Karakteristik pigmen alami wajah/bibir (melanin & hemoglobin):
                // Spektrum merah lebih kuat daripada hijau dan biru, dengan kecerahan wajar
                if (r > (g - 8) && r > (b - 12) && (r + g + b) > 70 && (r + g + b) < 700) {
                    skinToneCount++;
                }
            }

            const meanLum = totalLum / count;
            const variance = Math.max(0, (totalLumSq / count) - (meanLum * meanLum));
            const stdDev = Math.sqrt(variance);

            return {
                meanLum,
                stdDev,
                avgR: totalR / count,
                avgG: totalG / count,
                avgB: totalB / count,
                skinRatio: skinToneCount / count
            };
        }

        // 1. CEK SEMUA 4 FITUR UTAMA (Mata Kanan, Mata Kiri, Hidung, Mulut)
        // SEMUA HARUS TERLIHAT DAN TIDAK TERTUTUP (Kertas, Masker, Tangan, dll.)
        const features = [firstEye, secondEye, nose, mouth];
        const featureStats = [];

        for (const pt of features) {
            const stats = sampleRegion(pt.x, pt.y, cropSize);
            featureStats.push(stats);

            // Jika area fitur terlalu terang/putih (misal kertas putih atau layar HP)
            if (stats.meanLum > 215) {
                return false;
            }

            // Jika area fitur terlalu gelap (tertutup kain hitam / bayangan pekat)
            if (stats.meanLum < 25) {
                return false;
            }

            // Fitur asli memiliki detail/kontras tekstur alami
            if (stats.stdDev < 7) {
                return false;
            }

            // Harus memiliki pigmen alami wajah (kertas putih/biru, kain penutup, masker memiliki skinRatio sangat rendah)
            if (stats.skinRatio < 0.30) {
                return false;
            }
        }

        // 2. CEK SIMETRI KEDUA MATA
        // Jika salah satu mata ditutup kertas atau tangan
        const eye1 = featureStats[0];
        const eye2 = featureStats[1];
        const eyeLumDiff = Math.abs(eye1.meanLum - eye2.meanLum) / Math.max(eye1.meanLum, eye2.meanLum);
        if (eyeLumDiff > 0.40) {
            return false;
        }

        // 3. CEK SIMETRI KEDUA PIPI (Mendeteksi jika setengah wajah ditutup kertas/buku/tangan/hp)
        const normEyeDistance = Math.hypot(firstEye.x - secondEye.x, firstEye.y - secondEye.y);
        const leftCheekX = nose.x - 0.45 * normEyeDistance;
        const rightCheekX = nose.x + 0.45 * normEyeDistance;
        const cheekY = nose.y;

        const leftCheekStats = sampleRegion(leftCheekX, cheekY, cropSize);
        const rightCheekStats = sampleRegion(rightCheekX, cheekY, cropSize);

        // Jika salah satu pipi tertutup benda, perbedaan kecerahannya drastis (> 38%)
        const cheekLumDiff = Math.abs(leftCheekStats.meanLum - rightCheekStats.meanLum) / Math.max(leftCheekStats.meanLum, rightCheekStats.meanLum);
        if (cheekLumDiff > 0.38) {
            return false;
        }

        // Benda putih atau biru (kertas / masker) memiliki nilai Blue lebih tinggi dari Red
        if ((leftCheekStats.avgB - leftCheekStats.avgR > 10) || (rightCheekStats.avgB - rightCheekStats.avgR > 10)) {
            return false;
        }

        return true;
    }

    // 1. Inisialisasi Kanvas Tanda Tangan
    function initSignaturePad() {
        const canvas = document.getElementById('signaturePad');
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
        }
        resizeCanvas();
        window.addEventListener("resize", resizeCanvas);

        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255, 255, 255, 1)',
            penColor: 'rgb(14, 34, 56)'
        });

        document.getElementById('btnClearSignature').addEventListener('click', function() {
            signaturePad.clear();
        });
    }

    // 2. Inisialisasi Kamera Depan (Selfie)
    async function initCamera() {
        const video = document.getElementById('cameraStream');
        const cameraLoading = document.getElementById('cameraLoading');
        const btnSnap = document.getElementById('btnSnapPhoto');

        try {
            // Meminta stream kamera depan
            streamCamera = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user', // Kamera depan
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: false
            });

            video.srcObject = streamCamera;
            video.onloadedmetadata = () => {
                video.play();
                cameraLoading.classList.add('d-none');
                cameraReady = true;
                updateCaptureButton();
            };
        } catch (err) {
            console.error('Kamera gagal diakses:', err);
            cameraLoading.innerHTML = `
                <i class="bi bi-camera-video-off fs-3 d-block mb-1 text-danger"></i>
                <div class="text-danger small">Kamera tidak dapat diakses. Pastikan izin kamera telah diberikan di peramban.</div>
            `;
        }
    }

    // 3. Deteksi Geolocation GPS & Rumus Haversine Client-Side
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

    // 4. Pengaturan Tombol & Aksi Form
    function setupEventListeners() {
        // Tombol Snapshot Foto
        const btnSnap = document.getElementById('btnSnapPhoto');
        const btnRetake = document.getElementById('btnRetakePhoto');
        const video = document.getElementById('cameraStream');
        const imgPreview = document.getElementById('photoPreview');
        const canvas = document.getElementById('photoCanvas');
        const fotoSelfieInput = document.getElementById('fotoSelfie');

        btnSnap.addEventListener('click', async function() {
            if (!faceDetector || !cameraReady) {
                setFaceCheckStatus('danger', 'Kamera atau pemeriksaan wajah belum siap. Muat ulang halaman dan coba lagi.');
                return;
            }

            btnSnap.disabled = true;
            btnSnap.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memeriksa wajah...';

            try {
                const result = faceDetector.detect(video);

                let evaluation = {
                    valid: false,
                    message: 'Wajah tidak terdeteksi. Pastikan wajah berada di dalam bingkai kamera dengan pencahayaan yang cukup.'
                };

                if (result.detections && result.detections.length > 0) {
                    for (const detection of result.detections) {
                        const score = detection.categories?.[0]?.score ?? 0;
                        if (score < 0.5) {
                            evaluation = {
                                valid: false,
                                message: 'Wajah kurang terlihat jelas. Pastikan pencahayaan cukup dan wajah menghadap kamera.'
                            };
                            continue;
                        }

                        if (!isFrontFacingFace(detection)) {
                            evaluation = {
                                valid: false,
                                message: 'Wajah harus lurus menghadap kamera. Tidak boleh menoleh ke kanan, ke kiri, atau miring.'
                            };
                            continue;
                        }

                        if (!hasVisibleFacialFeatures(video, detection)) {
                            evaluation = {
                                valid: false,
                                message: 'Wajah terhalang atau tertutup (sebagian atau seluruhnya). Pastikan kedua mata, hidung, dan mulut terlihat jelas tanpa masker, kertas, atau tangan.'
                            };
                            continue;
                        }

                        evaluation = {
                            valid: true,
                            message: 'Wajah menghadap kamera dan terlihat jelas. Foto selfie siap digunakan untuk presensi.'
                        };
                        break;
                    }
                }

                if (!evaluation.valid) {
                    faceDetectedForCurrentPhoto = false;
                    fotoSelfieInput.value = '';
                    setFaceCheckStatus('warning', evaluation.message);
                    updateCaptureButton();
                    return;
                }

                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;
                const ctx = canvas.getContext('2d');

                // Mirror snapshot kamera depan
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                fotoSelfieInput.value = dataUrl;
                faceDetectedForCurrentPhoto = true;

                imgPreview.src = dataUrl;
                imgPreview.style.display = 'block';
                video.style.display = 'none';

                btnSnap.classList.add('d-none');
                btnRetake.classList.remove('d-none');
                setFaceCheckStatus('success', 'Wajah menghadap kamera dan terlihat jelas. Foto selfie siap digunakan untuk presensi.');
            } catch (error) {
                console.error('Pemeriksaan wajah gagal:', error);
                faceDetectedForCurrentPhoto = false;
                fotoSelfieInput.value = '';
                setFaceCheckStatus('danger', 'Foto gagal diperiksa. Coba ambil ulang selfie.');
                updateCaptureButton();
            }
        });

        btnRetake.addEventListener('click', function() {
            fotoSelfieInput.value = '';
            faceDetectedForCurrentPhoto = false;
            imgPreview.style.display = 'none';
            video.style.display = 'block';

            btnRetake.classList.add('d-none');
            btnSnap.classList.remove('d-none');
            updateCaptureButton();
            setFaceCheckStatus('info', 'Ambil foto selfie untuk memeriksa wajah kembali.');
        });

        // Tombol Cek NRP (hanya jika elemen tersedia)
        const btnCekNrp = document.getElementById('btnCekNrp');
        const nrpInput = document.getElementById('nrp');
        if (btnCekNrp && nrpInput) {
            btnCekNrp.addEventListener('click', () => verifikasiNrp(nrpInput.value.trim()));
            nrpInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    verifikasiNrp(nrpInput.value.trim());
                }
            });
        }

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

    // 5. Fungsi Verifikasi Status Presensi via AJAX
    async function verifikasiNrp(nrp) {
        const btnCek = document.getElementById('btnCekNrp');
        if (btnCek) {
            btnCek.disabled = true;
            btnCek.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        }

        hideAlert();

        try {
            const res = await fetch("{{ route('presensi.cek_nrp') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ nrp: nrp || '{{ $anggota->nrp }}' })
            });

            const result = await res.json();
            if (btnCek) {
                btnCek.disabled = false;
                btnCek.innerHTML = '<i class="bi bi-search me-1"></i> Cek';
            }
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ nrp: nrp })
            });

            const result = await res.json();
            btnCek.disabled = false;
            btnCek.innerHTML = '<i class="bi bi-search me-1"></i> Cek';

            if (res.ok && result.sukses) {
                const data = result.data;
                document.getElementById('personelNama').textContent = data.nama;
                document.getElementById('personelPangkat').textContent = data.jabatan;
                document.getElementById('personelSatuan').textContent = data.unit_kerja;

                document.getElementById('statusMasukTeks').textContent = data.sudah_masuk ? `Sudah (${data.waktu_masuk} WITA)` : 'Belum';
                document.getElementById('statusPulangTeks').textContent = data.sudah_pulang ? `Sudah (${data.waktu_pulang} WITA)` : 'Belum';

                document.getElementById('identityCard').classList.remove('d-none');

                // Otomatis arahkan jenis presensi
                if (data.rekomendasi === 'pulang') {
                    document.getElementById('jenisPulang').checked = true;
                } else if (data.rekomendasi === 'masuk') {
                    document.getElementById('jenisMasuk').checked = true;
                } else if (data.rekomendasi === 'lengkap') {
                    showAlert('info', 'Anda sudah menyelesaikan presensi Masuk dan Pulang untuk hari ini.');
                }
            } else {
                document.getElementById('identityCard').classList.add('d-none');
                showAlert('danger', result.pesan || 'NRP tidak terdaftar.');
            }
        } catch (e) {
            btnCek.disabled = false;
            btnCek.innerHTML = '<i class="bi bi-search me-1"></i> Cek';
            showAlert('danger', 'Gagal menghubungi server untuk memverifikasi NRP.');
        }
    }

    // 6. Fungsi Kirim Presensi
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
            showAlert('danger', 'Presensi tidak dapat dikirim sebelum wajah terdeteksi pada foto selfie.');
            return;
        }
        if (signaturePad.isEmpty()) {
            showAlert('danger', 'Silakan bubuhkan tanda tangan digital Anda pada kanvas.');
            return;
        }

        // Ambil tanda tangan base64
        const ttd = signaturePad.toDataURL('image/png');

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
                    foto_selfie: foto,
                    tanda_tangan: ttd
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

                // ATURAN BISNIS: Izin notifikasi diminta HANYA saat presensi pertama kali
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

    // 7. Penanganan Pendaftaran Web Push (VAPID)
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

            // Tunggu Service Worker aktif dengan batas waktu 5 detik
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

            // Kirim subscription ke server via AJAX
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

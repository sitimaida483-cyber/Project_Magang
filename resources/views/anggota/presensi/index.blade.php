@extends('layouts.anggota')

@section('title', 'Presensi Kehadiran Personel')

@push('styles')
<style>
    /* Styling Kamera & Pratinjau */
    .camera-container {
        position: relative;
        width: 100%;
        background-color: #000000;
        border-radius: 12px;
        overflow: hidden;
        aspect-ratio: 4 / 3;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #cameraStream {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1); /* Efek cermin untuk kamera depan selfie */
    }
    #photoPreview {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
    }
    /* Styling Kanvas Tanda Tangan */
    .signature-container {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        background-color: #ffffff;
        position: relative;
        touch-action: none;
    }
    #signaturePad {
        width: 100%;
        height: 180px;
        display: block;
        border-radius: 10px;
    }
    /* Indikator Status Langkah */
    .step-badge {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #0e2238;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div class="card card-custom p-4">
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

    <!-- Alert Notifikasi Global -->
    <div id="alertBox" class="alert d-none small mb-4" role="alert"></div>

    <!-- FORM PRESENSI -->
    <form id="formPresensi" onsubmit="return false;">
        @csrf

        <!-- IDENTIFIKASI NRP -->
        <div class="mb-4">
            <label for="nrp" class="form-label fw-bold text-dark small mb-2">
                Masukkan NRP Anda
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-person-vcard"></i></span>
                <input type="text" class="form-control font-monospace" id="nrp" name="nrp" 
                       placeholder="Masukkan NRP (Contoh: 80010001)" required autofocus autocomplete="off">
                <button class="btn btn-primary" type="button" id="btnCekNrp" style="background-color: #0e2238; border-color: #0e2238;">
                    <i class="bi bi-search me-1"></i> Cek
                </button>
            </div>

            <!-- Kartu Identitas Personel Hasil Cek -->
            <div id="identityCard" class="d-none mt-3 p-3 bg-light rounded-3 border">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 fs-3">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark" id="personelNama">-</div>
                        <div class="small text-secondary" id="personelPangkat">-</div>
                        <div class="small text-muted" id="personelSatuan">-</div>
                    </div>
                </div>
                <!-- Status Presensi Hari Ini -->
                <div class="mt-2 pt-2 border-top d-flex justify-content-between small text-muted">
                    <span>Masuk: <strong id="statusMasukTeks" class="text-dark">-</strong></span>
                    <span>Pulang: <strong id="statusPulangTeks" class="text-dark">-</strong></span>
                </div>
            </div>
        </div>

        <!-- PILIH JENIS PRESENSI -->
        <div class="mb-4">
            <label class="form-label fw-bold text-dark small mb-2">
                Jenis Presensi
            </label>
            <div class="row g-2">
                <div class="col-6">
                    <input type="radio" class="btn-check" name="jenis_presensi" id="jenisMasuk" value="masuk" checked>
                    <label class="btn btn-outline-success w-100 py-2 d-flex flex-column align-items-center justify-content-center" for="jenisMasuk">
                        <i class="bi bi-box-arrow-in-right fs-4 mb-1"></i>
                        <span class="fw-bold small">Presensi Masuk</span>
                    </label>
                </div>
                <div class="col-6">
                    <input type="radio" class="btn-check" name="jenis_presensi" id="jenisPulang" value="pulang">
                    <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center" for="jenisPulang">
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
        <button type="button" id="btnSubmitPresensi" class="btn btn-primary w-100 py-3 fw-bold shadow" style="background-color: #0e2238; border-color: #0e2238;">
            <i class="bi bi-send-fill me-1"></i> KIRIM PRESENSI SEKARANG
        </button>
    </form>
</div>

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

                <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" onclick="location.reload();" style="background-color: #0e2238; border-color: #0e2238;">
                    Selesai & Muat Ulang
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

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
        if (eyeDistance === 0) {
            return false;
        }

        const eyeCenterX = (firstEye.x + secondEye.x) / 2;
        const noseOffset = Math.abs(nose.x - eyeCenterX) / eyeDistance;
        const mouthOffset = Math.abs(mouth.x - eyeCenterX) / eyeDistance;
        const eyeTilt = Math.abs(firstEye.y - secondEye.y) / eyeDistance;

        return noseOffset <= 0.3
            && mouthOffset <= 0.5
            && eyeTilt <= 0.35
            && nose.y > Math.max(firstEye.y, secondEye.y)
            && mouth.y > nose.y;
    }

    function hasEnoughFacialDetail(imageData) {
        const { data, width, height } = imageData;
        const luminance = [];
        let total = 0;
        let totalSquared = 0;

        for (let index = 0; index < data.length; index += 4) {
            const red = data[index];
            const green = data[index + 1];
            const blue = data[index + 2];
            const value = 0.299 * red + 0.587 * green + 0.114 * blue;

            luminance.push(value);
            total += value;
            totalSquared += value * value;
        }

        const mean = total / luminance.length;
        const standardDeviation = Math.sqrt(Math.max(0, totalSquared / luminance.length - mean * mean));
        let strongEdges = 0;
        let edgeCount = 0;

        for (let y = 0; y < height; y++) {
            for (let x = 0; x < width; x++) {
                const index = y * width + x;
                if (x + 1 < width) {
                    edgeCount++;
                    if (Math.abs(luminance[index] - luminance[index + 1]) >= 28) {
                        strongEdges++;
                    }
                }
                if (y + 1 < height) {
                    edgeCount++;
                    if (Math.abs(luminance[index] - luminance[index + width]) >= 28) {
                        strongEdges++;
                    }
                }
            }
        }

        return standardDeviation >= 10
            && strongEdges / edgeCount >= 0.02;
    }

    function hasVisibleFacialFeatures(video, detection) {
        const [firstEye, secondEye, nose, mouth] = detection.keypoints || [];
        if (!firstEye || !secondEye || !nose || !mouth) {
            return false;
        }

        const eyeDistance = Math.hypot(
            (firstEye.x - secondEye.x) * video.videoWidth,
            (firstEye.y - secondEye.y) * video.videoHeight
        );
        const cropSize = Math.max(12, eyeDistance * 0.45);
        const sampleCanvas = document.createElement('canvas');
        sampleCanvas.width = 32;
        sampleCanvas.height = 32;
        const context = sampleCanvas.getContext('2d', { willReadFrequently: true });

        if (!context || video.videoWidth < cropSize || video.videoHeight < cropSize) {
            return false;
        }

        return [firstEye, secondEye, nose, mouth].every((point) => {
            const centerX = point.x * video.videoWidth;
            const centerY = point.y * video.videoHeight;
            const left = Math.max(0, Math.min(video.videoWidth - cropSize, centerX - cropSize / 2));
            const top = Math.max(0, Math.min(video.videoHeight - cropSize, centerY - cropSize / 2));

            context.clearRect(0, 0, sampleCanvas.width, sampleCanvas.height);
            context.drawImage(
                video,
                left,
                top,
                cropSize,
                cropSize,
                0,
                0,
                sampleCanvas.width,
                sampleCanvas.height
            );

            return hasEnoughFacialDetail(context.getImageData(0, 0, sampleCanvas.width, sampleCanvas.height));
        });
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

                const distanceFormatted = Math.round(distance);
                if (distance <= KANTOR_CONFIG.radius) {
                    radiusBadge.className = 'badge bg-success';
                    radiusBadge.innerHTML = `<i class="bi bi-check-circle me-1"></i> Dalam Radius (${distanceFormatted}m)`;
                    gpsInfo.innerHTML = `Lokasi terverifikasi di kantor <strong>${KANTOR_CONFIG.nama}</strong>. Koordinat: <span class="font-monospace">${lat.toFixed(6)}, ${lng.toFixed(6)}</span>`;
                } else {
                    radiusBadge.className = 'badge bg-danger';
                    radiusBadge.innerHTML = `<i class="bi bi-x-circle me-1"></i> Luar Radius (${distanceFormatted}m)`;
                    gpsInfo.innerHTML = `<span class="text-danger fw-semibold">Jarak Anda ${distanceFormatted} meter dari kantor!</span> Batas radius presensi adalah ${KANTOR_CONFIG.radius} meter.`;
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
                const frontFacingFaceFound = result.detections.some((detection) =>
                    detection.categories?.some((category) => category.score >= 0.6)
                    && isFrontFacingFace(detection)
                    && hasVisibleFacialFeatures(video, detection)
                );

                if (!frontFacingFaceFound) {
                    faceDetectedForCurrentPhoto = false;
                    fotoSelfieInput.value = '';
                    setFaceCheckStatus('warning', 'Wajah harus menghadap kamera dan tidak tertutup. Pastikan kedua mata, hidung, dan mulut terlihat jelas, lalu coba lagi.');
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

        // Tombol Cek NRP
        const btnCekNrp = document.getElementById('btnCekNrp');
        const nrpInput = document.getElementById('nrp');
        btnCekNrp.addEventListener('click', () => verifikasiNrp(nrpInput.value.trim()));
        nrpInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                verifikasiNrp(nrpInput.value.trim());
            }
        });

        // Tombol Refresh GPS
        document.getElementById('btnRefreshGps').addEventListener('click', detectGeolocation);

        // Tombol Submit Presensi
        document.getElementById('btnSubmitPresensi').addEventListener('click', submitPresensi);
    }

    // 5. Fungsi Verifikasi NRP via AJAX
    async function verifikasiNrp(nrp) {
        if (!nrp) {
            showAlert('danger', 'Silakan masukkan NRP Anda terlebih dahulu.');
            return;
        }

        const btnCek = document.getElementById('btnCekNrp');
        btnCek.disabled = true;
        btnCek.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        hideAlert();

        try {
            const res = await fetch("{{ route('presensi.cek_nrp') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
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

        const nrp = document.getElementById('nrp').value.trim();
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
                document.getElementById('modalPersonel').textContent = document.getElementById('personelNama').textContent || nrp;
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
                const pushPromptSection = document.getElementById('pushPromptSection');
                if (result.minta_izin_notifikasi && ('Notification' in window) && ('serviceWorker' in navigator)) {
                    pushPromptSection.classList.remove('d-none');
                    document.getElementById('btnAktifkanPush').onclick = function() {
                        requestPushSubscription(result.id_anggota);
                    };
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
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengaktifkan...';

        try {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                statusInfo.className = 'text-danger small mt-2 d-block';
                statusInfo.textContent = 'Izin notifikasi ditolak pada peramban ini.';
                btn.classList.add('d-none');
                return;
            }

            const registration = await navigator.serviceWorker.ready;
            const vapidPublicKey = "{{ config('presensi.vapid.public_key') }}";
            
            if (!vapidPublicKey) {
                statusInfo.className = 'text-warning small mt-2 d-block';
                statusInfo.textContent = 'Kunci VAPID belum dikonfigurasi.';
                return;
            }

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
                statusInfo.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Pengingat otomatis berhasil diaktifkan!';
            } else {
                btn.disabled = false;
                btn.innerHTML = 'Coba Lagi';
                statusInfo.className = 'text-danger small mt-2 d-block';
                statusInfo.textContent = resData.pesan || 'Gagal menyimpan perangkat.';
            }
        } catch (e) {
            btn.disabled = false;
            btn.innerHTML = 'Coba Lagi';
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

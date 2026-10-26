@extends('layouts.admin')

@section('title', 'Pengaturan Kantor')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #map {
        height: 520px;
        width: 100%;
        border-radius: 12px;
        z-index: 1;
        box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
    }
    .leaflet-popup-content-wrapper {
        border-radius: 8px;
    }
    .badge-coordinate {
        font-family: monospace;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold text-dark mb-0">Pengaturan Kantor & Jam Kerja</h3>
        <p class="text-muted small mb-0">
            Tentukan koordinat titik pusat kantor, batas radius presensi kehadiran, serta jam operasional masuk dan pulang.
        </p>
    </div>
    <div>
        <button type="button" class="btn btn-outline-primary btn-sm" id="btnLokasiSaya">
            <i class="bi bi-geo-fill me-1"></i> Deteksi Lokasi Saya Saat Ini
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Formulir Pengaturan -->
    <div class="col-12 col-xl-5">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-sliders me-1 text-primary"></i> Parameter Presensi
                </h5>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('admin.pengaturan.update') }}" method="POST" id="formPengaturan">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="nama_lokasi" class="form-label fw-semibold text-secondary small">Nama Lokasi / Kantor</label>
                        <input type="text" class="form-control @error('nama_lokasi') is-invalid @enderror" 
                               id="nama_lokasi" name="nama_lokasi" 
                               value="{{ old('nama_lokasi', $pengaturan->nama_lokasi) }}" 
                               placeholder="Contoh: Bidtik Polda Kalsel" required>
                    </div>

                    <div class="mb-3">
                        <label for="radius_meter" class="form-label fw-semibold text-secondary small">
                            Batas Radius Presensi (Meter)
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('radius_meter') is-invalid @enderror" 
                                   id="radius_meter" name="radius_meter" 
                                   value="{{ old('radius_meter', $pengaturan->radius_meter) }}" 
                                   min="5" max="50000" step="5" required>
                            <span class="input-group-text bg-light text-muted">meter</span>
                        </div>
                        <small class="text-muted" style="font-size: 0.75rem;">
                            Anggota di luar radius ini tidak dapat melakukan presensi.
                        </small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="jam_masuk" class="form-label fw-semibold text-secondary small">Jam Batas Masuk</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-clock"></i></span>
                                <input type="time" class="form-control @error('jam_masuk') is-invalid @enderror" 
                                       id="jam_masuk" name="jam_masuk" 
                                       value="{{ old('jam_masuk', substr($pengaturan->jam_masuk, 0, 5)) }}" required>
                            </div>
                            <small class="text-muted" style="font-size: 0.75rem;">Lewat jam ini = Terlambat</small>
                        </div>
                        <div class="col-6">
                            <label for="jam_keluar" class="form-label fw-semibold text-secondary small">Jam Mulai Pulang</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-clock-history"></i></span>
                                <input type="time" class="form-control @error('jam_keluar') is-invalid @enderror" 
                                       id="jam_keluar" name="jam_keluar" 
                                       value="{{ old('jam_keluar', substr($pengaturan->jam_keluar, 0, 5)) }}" required>
                            </div>
                            <small class="text-muted" style="font-size: 0.75rem;">Presensi pulang aktif</small>
                        </div>
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <label for="latitude" class="form-label fw-semibold text-secondary small">Latitude (Lintang)</label>
                            <input type="text" class="form-control badge-coordinate @error('latitude') is-invalid @enderror" 
                                   id="latitude" name="latitude" 
                                   value="{{ old('latitude', $pengaturan->latitude) }}" placeholder="-3.481988" required>
                        </div>
                        <div class="col-6">
                            <label for="longitude" class="form-label fw-semibold text-secondary small">Longitude (Bujur)</label>
                            <input type="text" class="form-control badge-coordinate @error('longitude') is-invalid @enderror" 
                                   id="longitude" name="longitude" 
                                   value="{{ old('longitude', $pengaturan->longitude) }}" placeholder="114.827147" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm" style="background-color: #0e2238; border-color: #0e2238;">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan Pengaturan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Peta Leaflet Interaktif -->
    <div class="col-12 col-xl-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-map-fill me-1 text-primary"></i> Titik Lokasi & Visualisasi Radius
                </h5>
                <span class="badge bg-light text-secondary border">Klik atau geser pin</span>
            </div>
            <div class="card-body p-3">
                <div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div>
                        <strong>Petunjuk:</strong> Klik area mana saja pada peta atau <strong>geser (drag)</strong> pin merah untuk memindahkan koordinat kantor. Lingkaran biru menunjukkan jangkauan absensi anggota.
                    </div>
                </div>

                <!-- Kontainer Peta -->
                <div id="map"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ambil nilai koordinat dan radius awal dari input form
        let latInput = document.getElementById('latitude');
        let lngInput = document.getElementById('longitude');
        let radiusInput = document.getElementById('radius_meter');
        let namaLokasiInput = document.getElementById('nama_lokasi');

        let defaultLat = parseFloat(latInput.value) || -3.3194000;
        let defaultLng = parseFloat(lngInput.value) || 114.5925000;
        let defaultRadius = parseInt(radiusInput.value) || 100;

        // Inisialisasi Peta Leaflet
        const map = L.map('map').setView([defaultLat, defaultLng], 16);

        // Pasang Tile OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> kontributor'
        }).addTo(map);

        // Marker Titik Kantor (Dapat digeser / draggable)
        const marker = L.marker([defaultLat, defaultLng], {
            draggable: true
        }).addTo(map);

        marker.bindPopup(`<strong>${namaLokasiInput.value || 'Pusat Kantor'}</strong><br>Titik Koordinat Presensi`).openPopup();

        // Lingkaran Radius Presensi (Circle)
        const circle = L.circle([defaultLat, defaultLng], {
            color: '#1e40af',       // Border warna navy/biru
            fillColor: '#3b82f6',   // Warna isi biru terang
            fillOpacity: 0.25,
            radius: defaultRadius
        }).addTo(map);

        // Fungsi memperbarui koordinat pada form & peta
        function updateCoordinates(lat, lng) {
            const formattedLat = parseFloat(lat).toFixed(7);
            const formattedLng = parseFloat(lng).toFixed(7);

            latInput.value = formattedLat;
            lngInput.value = formattedLng;

            marker.setLatLng([formattedLat, formattedLng]);
            circle.setLatLng([formattedLat, formattedLng]);

            marker.getPopup().setContent(`<strong>${namaLokasiInput.value || 'Pusat Kantor'}</strong><br>Lat: ${formattedLat}<br>Lng: ${formattedLng}`);
        }

        // Event: Saat marker selesai digeser (dragend)
        marker.on('dragend', function(e) {
            const position = marker.getLatLng();
            updateCoordinates(position.lat, position.lng);
        });

        // Event: Saat peta diklik
        map.on('click', function(e) {
            updateCoordinates(e.latlng.lat, e.latlng.lng);
        });

        // Event: Saat angka radius diubah
        radiusInput.addEventListener('input', function() {
            const newRadius = parseInt(this.value) || 0;
            if (newRadius >= 0) {
                circle.setRadius(newRadius);
            }
        });

        // Event: Saat nama kantor diketik, sesuaikan popup
        namaLokasiInput.addEventListener('input', function() {
            marker.getPopup().setContent(`<strong>${this.value || 'Pusat Kantor'}</strong><br>Lat: ${latInput.value}<br>Lng: ${lngInput.value}`);
        });

        // Event: Saat latitude atau longitude diketik manual
        function handleManualCoordinateInput() {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                marker.setLatLng([lat, lng]);
                circle.setLatLng([lat, lng]);
                map.panTo([lat, lng]);
                marker.getPopup().setContent(`<strong>${namaLokasiInput.value || 'Pusat Kantor'}</strong><br>Lat: ${lat}<br>Lng: ${lng}`);
            }
        }
        latInput.addEventListener('input', handleManualCoordinateInput);
        lngInput.addEventListener('input', handleManualCoordinateInput);

        // Tombol: Deteksi Lokasi Saya Saat Ini via Browser Geolocation
        const btnLokasiSaya = document.getElementById('btnLokasiSaya');
        btnLokasiSaya.addEventListener('click', function() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung deteksi lokasi (Geolocation).');
                return;
            }

            btnLokasiSaya.disabled = true;
            btnLokasiSaya.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mendeteksi...';

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const currentLat = position.coords.latitude;
                    const currentLng = position.coords.longitude;

                    updateCoordinates(currentLat, currentLng);
                    map.setView([currentLat, currentLng], 17);

                    btnLokasiSaya.disabled = false;
                    btnLokasiSaya.innerHTML = '<i class="bi bi-geo-fill me-1"></i> Deteksi Lokasi Saya Saat Ini';
                },
                function(error) {
                    btnLokasiSaya.disabled = false;
                    btnLokasiSaya.innerHTML = '<i class="bi bi-geo-fill me-1"></i> Deteksi Lokasi Saya Saat Ini';

                    let errorMsg = 'Gagal mendeteksi lokasi.';
                    if (error.code === error.PERMISSION_DENIED) {
                        errorMsg = 'Izin akses lokasi ditolak oleh pengguna/browser.';
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        errorMsg = 'Informasi lokasi GPS tidak tersedia.';
                    } else if (error.code === error.TIMEOUT) {
                        errorMsg = 'Waktu permintaan lokasi habis.';
                    }
                    alert(errorMsg);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000
                }
            );
        });

        // Refresh ukuran peta agar tidak terpotong
        setTimeout(() => {
            map.invalidateSize();
        }, 300);
    });
</script>
@endpush

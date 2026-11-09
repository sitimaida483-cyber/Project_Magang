@extends('layouts.admin')

@section('title', 'Pengaturan Kantor')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #map {
        height: 520px;
        width: 100%;
        border-radius: var(--np-radius-card);
        z-index: 1;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(23,38,63,0.1);
        font-family: var(--np-font);
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="mb-4">
    <h1 class="h1-navy">Pengaturan Kantor</h1>
    <p class="subtitle-navy mb-0">Presensi Digital Bidtik Polda Kalimantan Selatan</p>
</div>

<!-- Alert Sukses / Error -->
@if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show mb-4 border-0" role="alert" style="background-color: var(--np-success-soft); color: var(--np-success); border-left: 4px solid var(--np-success) !important; border-radius: 8px;">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('sukses') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4 border-0" role="alert" style="background-color: var(--np-danger-soft); color: var(--np-danger); border-left: 4px solid var(--np-danger) !important; border-radius: 8px;">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Kolom Kiri: Formulir Pengaturan Parameter (Pages 35-36) -->
    <div class="col-12 col-lg-5">
        <div class="np-card p-4">
            <form action="{{ route('admin.pengaturan.update') }}" method="POST" id="formPengaturan">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="nama_lokasi" class="form-label mb-1">Nama Lokasi *</label>
                    <input type="text" class="form-control @error('nama_lokasi') is-invalid @enderror" 
                           id="nama_lokasi" name="nama_lokasi" 
                           value="{{ old('nama_lokasi', $pengaturan->nama_lokasi) }}" 
                           placeholder="Kantor Bid TIK Polda Kalsel" required>
                </div>

                <div class="mb-3">
                    <label for="radius_meter" class="form-label mb-1">Radius Presensi (m) *</label>
                    <input type="number" class="form-control @error('radius_meter') is-invalid @enderror" 
                           id="radius_meter" name="radius_meter" 
                           value="{{ old('radius_meter', $pengaturan->radius_meter) }}" 
                           min="5" max="50000" step="5" placeholder="100" required>
                    <small class="text-muted" style="font-size: 12px;">Batas jarak maksimal personel dari titik pusat kantor.</small>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="jam_masuk" class="form-label mb-1">Jam Masuk *</label>
                        <input type="time" class="form-control @error('jam_masuk') is-invalid @enderror" 
                               id="jam_masuk" name="jam_masuk" 
                               value="{{ old('jam_masuk', substr($pengaturan->jam_masuk, 0, 5)) }}" required>
                    </div>
                    <div class="col-6">
                        <label for="jam_keluar" class="form-label mb-1">Jam Pulang *</label>
                        <input type="time" class="form-control @error('jam_keluar') is-invalid @enderror" 
                               id="jam_keluar" name="jam_keluar" 
                               value="{{ old('jam_keluar', substr($pengaturan->jam_keluar, 0, 5)) }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="latitude" class="form-label mb-1">Latitude *</label>
                    <input type="text" class="form-control font-monospace @error('latitude') is-invalid @enderror" 
                           id="latitude" name="latitude" 
                           value="{{ old('latitude', $pengaturan->latitude) }}" placeholder="-3.316694" required>
                </div>

                <div class="mb-4">
                    <label for="longitude" class="form-label mb-1">Longitude *</label>
                    <input type="text" class="form-control font-monospace @error('longitude') is-invalid @enderror" 
                           id="longitude" name="longitude" 
                           value="{{ old('longitude', $pengaturan->longitude) }}" placeholder="114.590111" required>
                </div>

                <button type="submit" class="btn np-btn-primary w-100 fw-bold" style="height: 48px;">
                    Simpan Pengaturan
                </button>
            </form>
        </div>
    </div>

    <!-- Kolom Kanan: Peta Leaflet Geofence (Pages 35-36) -->
    <div class="col-12 col-lg-7">
        <div class="np-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <span class="text-muted small">Marker dapat digeser dan radius mengikuti input</span>
                <button type="button" class="btn np-btn-outline btn-sm d-inline-flex align-items-center" id="btnLokasiSaya" style="height: 38px;">
                    <i class="bi bi-geo-alt me-1"></i> Deteksi Lokasi Saya
                </button>
            </div>

            <!-- Peta Leaflet -->
            <div id="map" class="border" style="border-color: var(--np-line) !important;"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const radiusInput = document.getElementById('radius_meter');
        const namaLokasiInput = document.getElementById('nama_lokasi');

        let defaultLat = parseFloat(latInput.value) || -3.316694;
        let defaultLng = parseFloat(lngInput.value) || 114.590111;
        let defaultRadius = parseInt(radiusInput.value) || 100;

        // Custom DivIcon for Office Pin (Navy Precision Style)
        const officeIcon = L.divIcon({
            className: 'custom-office-pin',
            html: '<div style="width: 20px; height: 20px; background-color: #254BD6; border: 3px solid #FFFFFF; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });

        // Initialize Map
        const map = L.map('map').setView([defaultLat, defaultLng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        // Marker (Draggable)
        const marker = L.marker([defaultLat, defaultLng], {
            draggable: true,
            icon: officeIcon
        }).addTo(map);

        marker.bindPopup(`<strong>${namaLokasiInput.value || 'Pusat Kantor'}</strong><br>Titik Koordinat Presensi`).openPopup();

        // Circle Radius Geofence
        const circle = L.circle([defaultLat, defaultLng], {
            color: '#254BD6',
            fillColor: '#254BD6',
            fillOpacity: 0.15,
            weight: 2,
            radius: defaultRadius
        }).addTo(map);

        function updateCoordinates(lat, lng) {
            const formattedLat = parseFloat(lat).toFixed(7);
            const formattedLng = parseFloat(lng).toFixed(7);

            latInput.value = formattedLat;
            lngInput.value = formattedLng;

            marker.setLatLng([formattedLat, formattedLng]);
            circle.setLatLng([formattedLat, formattedLng]);

            marker.getPopup().setContent(`<strong>${namaLokasiInput.value || 'Pusat Kantor'}</strong><br>Lat: ${formattedLat}<br>Lng: ${formattedLng}`);
        }

        marker.on('dragend', function(e) {
            const pos = marker.getLatLng();
            updateCoordinates(pos.lat, pos.lng);
        });

        map.on('click', function(e) {
            updateCoordinates(e.latlng.lat, e.latlng.lng);
        });

        radiusInput.addEventListener('input', function() {
            const newRadius = parseInt(this.value) || 0;
            if (newRadius >= 0) {
                circle.setRadius(newRadius);
            }
        });

        namaLokasiInput.addEventListener('input', function() {
            marker.getPopup().setContent(`<strong>${this.value || 'Pusat Kantor'}</strong><br>Lat: ${latInput.value}<br>Lng: ${lngInput.value}`);
        });

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

        // Geolocation Button
        const btnLokasiSaya = document.getElementById('btnLokasiSaya');
        if (btnLokasiSaya) {
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
                        btnLokasiSaya.innerHTML = '<i class="bi bi-geo-alt me-1"></i> Deteksi Lokasi Saya';
                    },
                    function(error) {
                        btnLokasiSaya.disabled = false;
                        btnLokasiSaya.innerHTML = '<i class="bi bi-geo-alt me-1"></i> Deteksi Lokasi Saya';

                        let errorMsg = 'Gagal mendeteksi lokasi.';
                        if (error.code === error.PERMISSION_DENIED) {
                            errorMsg = 'Izin akses lokasi ditolak oleh browser.';
                        } else if (error.code === error.POSITION_UNAVAILABLE) {
                            errorMsg = 'Informasi lokasi GPS tidak tersedia.';
                        } else if (error.code === error.TIMEOUT) {
                            errorMsg = 'Waktu permintaan lokasi habis.';
                        }
                        alert(errorMsg);
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            });
        }

        setTimeout(() => {
            map.invalidateSize();
        }, 300);
    });
</script>
@endpush

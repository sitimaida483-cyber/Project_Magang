@extends('layouts.admin')

@section('title', 'Peta Lokasi Presensi')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #mapPresensi {
        height: 600px;
        width: 100%;
        border-radius: 12px;
        z-index: 1;
    }
    .custom-pin {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        color: #ffffff;
        font-size: 15px;
        box-shadow: 0 3px 8px rgba(0,0,0,0.35);
        border: 2.5px solid #ffffff;
        transition: transform 0.15s ease-in-out;
    }
    .custom-pin:hover {
        transform: scale(1.15);
    }
    .pin-office {
        background: linear-gradient(135deg, #0e2238 0%, #1e3a8a 100%);
        border-color: #f59e0b;
        color: #f59e0b;
    }
    .pin-tepat {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    }
    .pin-terlambat {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    }
    .pin-pulang {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    }
    .popup-content {
        min-width: 230px;
        max-width: 270px;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 10px;
        padding: 4px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
    }
    .leaflet-popup-content {
        margin: 10px 12px;
        line-height: 1.4;
    }
    .legend-box {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(4px);
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 12px;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold text-dark mb-0">Peta Sebaran Lokasi Presensi</h3>
        <p class="text-muted small mb-0">
            Monitoring spasial titik koordinat kehadiran personel Bidtik Polda Kalsel secara interaktif.
        </p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" id="btnFokusKantor">
            <i class="bi bi-building me-1"></i> Fokus Titik Kantor
        </button>
        <button type="button" class="btn btn-primary btn-sm" id="btnFitMarkers" style="background-color: #0e2238; border-color: #0e2238;">
            <i class="bi bi-arrows-fullscreen me-1"></i> Tampilkan Semua Titik
        </button>
    </div>
</div>

<!-- Kartu Statistik Titik -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card card-stat bg-white p-3 border-start border-4 border-dark">
            <small class="text-muted text-uppercase fw-semibold">Titik Terdeteksi</small>
            <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalTitik }}</h3>
            <small class="text-muted">Koordinat di peta</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat bg-white p-3 border-start border-4 border-success">
            <small class="text-muted text-uppercase fw-semibold">Masuk Tepat Waktu</small>
            <h3 class="fw-bold text-success mb-0 mt-1">{{ $totalMasuk - $totalTerlambat > 0 ? $totalMasuk - $totalTerlambat : 0 }}</h3>
            <small class="text-muted">Titik hijau</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat bg-white p-3 border-start border-4 border-danger">
            <small class="text-muted text-uppercase fw-semibold">Terlambat</small>
            <h3 class="fw-bold text-danger mb-0 mt-1">{{ $totalTerlambat }}</h3>
            <small class="text-muted">Titik merah</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat bg-white p-3 border-start border-4 border-primary">
            <small class="text-muted text-uppercase fw-semibold">Presensi Pulang</small>
            <h3 class="fw-bold text-primary mb-0 mt-1">{{ $totalPulang }}</h3>
            <small class="text-muted">Titik biru</small>
        </div>
    </div>
</div>

<!-- Form Filter Peta -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.peta.index') }}" class="row g-2 align-items-end">
            <!-- Filter Tanggal -->
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">
                    <i class="bi bi-calendar-event me-1"></i> Tanggal Presensi
                </label>
                <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ $tanggalAktif }}">
            </div>

            <!-- Filter Anggota -->
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">
                    <i class="bi bi-person me-1"></i> Personel / Anggota
                </label>
                <select name="id_anggota" class="form-select form-select-sm">
                    <option value="">-- Semua Personel --</option>
                    @foreach($anggotaList as $a)
                        <option value="{{ $a->id_anggota }}" {{ request('id_anggota') == $a->id_anggota ? 'selected' : '' }}>
                            {{ $a->nrp }} - {{ $a->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Jenis Presensi -->
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">
                    <i class="bi bi-tag me-1"></i> Jenis Presensi
                </label>
                <select name="jenis_presensi" class="form-select form-select-sm">
                    <option value="">-- Masuk & Pulang --</option>
                    <option value="masuk" {{ request('jenis_presensi') == 'masuk' ? 'selected' : '' }}>Masuk</option>
                    <option value="pulang" {{ request('jenis_presensi') == 'pulang' ? 'selected' : '' }}>Pulang</option>
                </select>
            </div>

            <!-- Tombol Submit & Reset -->
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold" style="background-color: #0e2238; border-color: #0e2238;">
                    <i class="bi bi-filter me-1"></i> Terapkan
                </button>
                <a href="{{ route('admin.peta.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Container Peta Leaflet & Legenda -->
<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="fw-bold text-dark mb-0">
            <i class="bi bi-map-fill me-1 text-primary"></i> Peta Interaktif Presensi
            <span class="badge bg-light text-dark border ms-2">
                {{ \Carbon\Carbon::parse($tanggalAktif)->translatedFormat('l, d F Y') }}
            </span>
        </h6>
        <!-- Legenda Indikator -->
        <div class="d-none d-md-flex align-items-center gap-3 small">
            <span class="d-flex align-items-center gap-1">
                <span class="badge rounded-circle p-1" style="background-color: #0e2238; border: 1px solid #f59e0b;">&nbsp;</span>
                Kantor Polda (Radius {{ $pengaturan->radius_meter ?? 100 }}m)
            </span>
            <span class="d-flex align-items-center gap-1">
                <span class="badge rounded-circle p-1 bg-success">&nbsp;</span> Tepat Waktu
            </span>
            <span class="d-flex align-items-center gap-1">
                <span class="badge rounded-circle p-1 bg-danger">&nbsp;</span> Terlambat
            </span>
            <span class="d-flex align-items-center gap-1">
                <span class="badge rounded-circle p-1 bg-primary">&nbsp;</span> Pulang
            </span>
        </div>
    </div>
    <div class="card-body p-0 position-relative">
        <div id="mapPresensi"></div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Data Konfigurasi Kantor
    const kantorLat = {{ $pengaturan->latitude ?? -3.3194 }};
    const kantorLng = {{ $pengaturan->longitude ?? 114.5908 }};
    const radiusMeter = {{ $pengaturan->radius_meter ?? 100 }};
    const namaKantor = @json($pengaturan->nama_kantor ?? 'Kantor Bidtik Polda Kalsel');
    const alamatKantor = @json($pengaturan->alamat_kantor ?? 'Banjarmasin');

    // 2. Data Presensi Personel dari Backend
    const dataPresensi = @json($dataPresensiList);

    // 3. Inisialisasi Peta
    const map = L.map('mapPresensi').setView([kantorLat, kantorLng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // 4. Marker Kantor Polisi
    const officeIcon = L.divIcon({
        className: 'custom-pin-container',
        html: `<div class="custom-pin pin-office" title="${namaKantor}"><i class="bi bi-shield-lock-fill"></i></div>`,
        iconSize: [34, 34],
        iconAnchor: [17, 17],
        popupAnchor: [0, -18]
    });

    const officeMarker = L.marker([kantorLat, kantorLng], { icon: officeIcon }).addTo(map);
    officeMarker.bindPopup(`
        <div class="popup-content">
            <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
                <div class="rounded-circle bg-warning text-dark p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-building fw-bold"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">${namaKantor}</h6>
                    <small class="text-muted">Titik Acuan Presensi</small>
                </div>
            </div>
            <p class="small text-muted mb-2">${alamatKantor}</p>
            <div class="p-2 bg-light rounded small">
                <strong>Radius Sah:</strong> ${radiusMeter} Meter dari titik ini.
            </div>
        </div>
    `);

    // Lingkaran Radius Kantor
    const officeCircle = L.circle([kantorLat, kantorLng], {
        color: '#1e3a8a',
        fillColor: '#3b82f6',
        fillOpacity: 0.12,
        weight: 2,
        dashArray: '5, 8',
        radius: radiusMeter
    }).addTo(map);

    // 5. Marker Presensi Anggota
    const markersGroup = L.featureGroup();
    markersGroup.addLayer(officeMarker);

    dataPresensi.forEach(p => {
        let pinClass = 'pin-tepat';
        let pinIcon = 'bi-box-arrow-in-right';
        let statusBadge = '<span class="badge bg-success">Tepat Waktu</span>';

        if (p.jenis_presensi === 'pulang') {
            pinClass = 'pin-pulang';
            pinIcon = 'bi-box-arrow-right';
            statusBadge = '<span class="badge bg-primary">Pulang</span>';
        } else if (p.status_kehadiran === 'terlambat') {
            pinClass = 'pin-terlambat';
            pinIcon = 'bi-exclamation-triangle-fill';
            statusBadge = '<span class="badge bg-danger">Terlambat</span>';
        }

        const personIcon = L.divIcon({
            className: 'custom-pin-container',
            html: `<div class="custom-pin ${pinClass}" title="${p.nama} (${p.nrp})"><i class="bi ${pinIcon}"></i></div>`,
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            popupAnchor: [0, -18]
        });

        const marker = L.marker([p.latitude, p.longitude], { icon: personIcon });

        // Foto preview di popup
        let fotoHtml = '';
        if (p.foto_url) {
            fotoHtml = `
                <div class="text-center mb-2">
                    <img src="${p.foto_url}" class="rounded-3 border shadow-sm" style="width: 100%; max-height: 120px; object-fit: cover;" alt="Foto Selfie">
                </div>
            `;
        }

        const popupHtml = `
            <div class="popup-content">
                ${fotoHtml}
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <h6 class="fw-bold mb-0 text-dark">${p.nama}</h6>
                </div>
                <div class="text-muted small mb-2">NRP: <strong>${p.nrp}</strong> &bull; ${p.pangkat}</div>
                <div class="p-2 bg-light rounded small mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Jenis:</span>
                        <strong class="text-uppercase">${p.jenis_presensi}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Waktu:</span>
                        <span>${p.waktu}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Status:</span>
                        <span>${statusBadge}</span>
                    </div>
                </div>
                <a href="${p.detail_url}" class="btn btn-outline-primary btn-sm w-100 py-1" style="font-size: 11px;">
                    <i class="bi bi-eye me-1"></i> Buka Detail Lengkap
                </a>
            </div>
        `;

        marker.bindPopup(popupHtml);
        marker.addTo(map);
        markersGroup.addLayer(marker);
    });

    // 6. Tombol Aksi Kontrol
    document.getElementById('btnFokusKantor').addEventListener('click', function () {
        map.flyTo([kantorLat, kantorLng], 17, { duration: 1.2 });
        officeMarker.openPopup();
    });

    document.getElementById('btnFitMarkers').addEventListener('click', function () {
        if (markersGroup.getLayers().length > 0) {
            map.fitBounds(markersGroup.getBounds().pad(0.15));
        }
    });

    // Auto fit jika ada data presensi selain kantor
    if (dataPresensi.length > 0) {
        map.fitBounds(markersGroup.getBounds().pad(0.2));
    }
});
</script>
@endpush

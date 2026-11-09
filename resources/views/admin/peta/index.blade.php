@extends('layouts.admin')

@section('title', 'Peta Lokasi Presensi')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #mapPresensi {
        height: 560px;
        width: 100%;
        border-radius: 8px;
        z-index: 1;
    }
    .custom-pin {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid #FFFFFF;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }
    .pin-tepat { background-color: #15803D; }
    .pin-terlambat { background-color: #DC2626; }
    .pin-pulang { background-color: #254BD6; }
    .pin-office { background-color: #17263F; border-color: #F59E0B; }
</style>
@endpush

@section('content')
<div class="mb-4">
    <h1 class="h1-navy">Peta Lokasi Presensi</h1>
    <div class="subtitle-navy">Presensi Digital Bidtik Polda Kalimantan Selatan</div>
</div>

<!-- 4 Stat Cards -->
<div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
    <div class="col">
        <div class="np-stat-card">
            <span class="np-stat-label">Total Titik</span>
            <span class="np-stat-value text-dark">{{ $totalTitik ?? 0 }}</span>
        </div>
    </div>
    <div class="col">
        <div class="np-stat-card">
            <span class="np-stat-label">Tepat Waktu</span>
            <span class="np-stat-value text-dark">{{ max(0, ($totalMasuk ?? 0) - ($totalTerlambat ?? 0)) }}</span>
        </div>
    </div>
    <div class="col">
        <div class="np-stat-card">
            <span class="np-stat-label">Terlambat</span>
            <span class="np-stat-value text-dark">{{ $totalTerlambat ?? 0 }}</span>
        </div>
    </div>
    <div class="col">
        <div class="np-stat-card">
            <span class="np-stat-label">Pulang</span>
            <span class="np-stat-value text-dark">{{ $totalPulang ?? 0 }}</span>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card np-card mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.peta.index') }}" method="GET">
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal', date('Y-m-d')) }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Personel</label>
                    <select name="id_anggota" class="form-select">
                        <option value="">Semua personel</option>
                        @foreach($anggotaList as $a)
                            <option value="{{ $a->id_anggota }}" {{ request('id_anggota') == $a->id_anggota ? 'selected' : '' }}>
                                {{ $a->nrp }} - {{ $a->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Jenis Presensi</label>
                    <select name="jenis_presensi" class="form-select">
                        <option value="">Semua jenis</option>
                        <option value="masuk" {{ request('jenis_presensi') === 'masuk' ? 'selected' : '' }}>Masuk</option>
                        <option value="pulang" {{ request('jenis_presensi') === 'pulang' ? 'selected' : '' }}>Pulang</option>
                    </select>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary fw-bold">
                    Tampilkan Peta
                </button>
                <button type="button" class="btn btn-outline-secondary fw-bold" id="btnFokusKantor">
                    Fokus Kantor
                </button>
                <button type="button" class="btn btn-outline-secondary fw-bold" id="btnFitMarkers">
                    Semua Penanda
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Map Container -->
<div class="card np-card mb-4">
    <div class="card-body p-3">
        <div id="mapPresensi"></div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const kantorLat = {{ !empty($pengaturan->latitude) ? (float)$pengaturan->latitude : -3.3194 }};
    const kantorLng = {{ !empty($pengaturan->longitude) ? (float)$pengaturan->longitude : 114.5908 }};
    const radiusMeter = {{ !empty($pengaturan->radius_meter) ? (int)$pengaturan->radius_meter : 100 }};
    const namaKantor = @json($pengaturan->nama_kantor ?? 'Kantor Bidtik Polda Kalsel');

    const dataPresensi = @json($dataPresensiList);

    const map = L.map('mapPresensi').setView([kantorLat, kantorLng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // Marker Kantor
    const officeIcon = L.divIcon({
        className: 'custom-pin-wrapper',
        html: `<div class="custom-pin pin-office" title="${namaKantor}"></div>`,
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });

    const officeMarker = L.marker([kantorLat, kantorLng], { icon: officeIcon }).addTo(map);
    officeMarker.bindPopup(`<strong>${namaKantor}</strong><br>Radius: ${radiusMeter} m`);

    L.circle([kantorLat, kantorLng], {
        color: '#254BD6',
        fillColor: '#254BD6',
        fillOpacity: 0.15,
        radius: radiusMeter
    }).addTo(map);

    const markersGroup = L.featureGroup();
    markersGroup.addLayer(officeMarker);

    // Marker Presensi
    dataPresensi.forEach(p => {
        let pinClass = 'pin-tepat';
        let statusBadge = '<span class="badge badge-np-success">Tepat Waktu</span>';

        if (p.jenis_presensi === 'pulang') {
            pinClass = 'pin-pulang';
            statusBadge = '<span class="badge badge-np-info">Pulang</span>';
        } else if (p.status_kehadiran === 'terlambat') {
            pinClass = 'pin-terlambat';
            statusBadge = '<span class="badge badge-np-danger">Terlambat</span>';
        }

        const personIcon = L.divIcon({
            className: 'custom-pin-wrapper',
            html: `<div class="custom-pin ${pinClass}"></div>`,
            iconSize: [18, 18],
            iconAnchor: [9, 9]
        });

        const marker = L.marker([p.latitude, p.longitude], { icon: personIcon });

        const popupHtml = `
            <div style="min-width: 180px; padding: 4px;">
                <div class="fw-bold text-dark fs-6 mb-1">${p.nama}</div>
                <div class="text-secondary small mb-2 text-capitalize">${p.jenis_presensi} / ${p.waktu} WITA</div>
                <div class="mb-2">${statusBadge}</div>
                <a href="${p.detail_url}" class="fw-bold text-decoration-none" style="font-size: 13px; color: var(--np-primary);">
                    Lihat Detail Presensi
                </a>
            </div>
        `;

        marker.bindPopup(popupHtml);
        marker.addTo(map);
        markersGroup.addLayer(marker);
    });

    document.getElementById('btnFokusKantor')?.addEventListener('click', function () {
        map.flyTo([kantorLat, kantorLng], 17, { duration: 1 });
        officeMarker.openPopup();
    });

    document.getElementById('btnFitMarkers')?.addEventListener('click', function () {
        if (markersGroup.getLayers().length > 0) {
            map.fitBounds(markersGroup.getBounds().pad(0.15));
        }
    });

    if (dataPresensi.length > 0) {
        map.fitBounds(markersGroup.getBounds().pad(0.2));
    }
});
</script>
@endpush

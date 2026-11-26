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
    .legend-pin {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: inline-block;
        border: 2px solid #FFFFFF;
        box-shadow: 0 1px 3px rgba(0,0,0,0.25);
        margin-right: 6px;
        vertical-align: -2px;
    }
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
            <span class="np-stat-value text-primary">{{ $totalTitik ?? 0 }}</span>
        </div>
    </div>
    <div class="col">
        <div class="np-stat-card">
            <span class="np-stat-label">Tepat Waktu</span>
            <span class="np-stat-value text-primary">{{ max(0, ($totalMasuk ?? 0) - ($totalTerlambat ?? 0)) }}</span>
        </div>
    </div>
    <div class="col">
        <div class="np-stat-card">
            <span class="np-stat-label">Terlambat</span>
            <span class="np-stat-value text-primary">{{ $totalTerlambat ?? 0 }}</span>
        </div>
    </div>
    <div class="col">
        <div class="np-stat-card">
            <span class="np-stat-label">Pulang</span>
            <span class="np-stat-value text-primary">{{ $totalPulang ?? 0 }}</span>
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

            <!-- Tombol Aksi Filter -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="submit" class="btn btn-primary fw-bold px-4">
                    <i class="bi bi-funnel-fill me-1"></i> Terapkan Filter
                </button>
                <a href="{{ route('admin.peta.index') }}" class="btn btn-outline-secondary fw-bold px-3">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Map Container -->
<div class="card np-card mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-geo-alt-fill text-primary fs-5"></i>
            <span class="fw-bold text-dark">Sebaran Spasial Presensi Personel</span>
            @if(count($dataPresensiList) > 0)
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 ms-1">
                    {{ count($dataPresensiList) }} Titik Terdeteksi
                </span>
            @else
                <span class="badge bg-light text-muted border rounded-pill px-2 py-1 ms-1">
                    0 Titik Presensi
                </span>
            @endif
        </div>
        
        <!-- Tombol Kontrol Peta -->
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary fw-semibold px-3" id="btnFokusKantor" title="Pusatkan peta ke lokasi kantor">
                <i class="bi bi-building me-1"></i> Fokus Kantor
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold px-3" id="btnFitMarkers" title="Lihat cakupan semua titik penanda">
                <i class="bi bi-arrows-fullscreen me-1"></i> Semua Penanda
            </button>
        </div>
    </div>
    
    <!-- Legenda Peta (Keterangan Warna) -->
    <div class="bg-light px-3 py-2 border-bottom">
        <div class="d-flex flex-wrap align-items-center justify-content-start gap-3 small text-secondary">
            <span class="fw-bold text-dark me-1"><i class="bi bi-info-circle me-1"></i> Legenda:</span>
            <span class="d-inline-flex align-items-center">
                <span class="legend-pin pin-office"></span>
                <strong class="text-dark">Kantor Bidtik</strong>
            </span>
            <span class="d-inline-flex align-items-center">
                <span class="legend-pin pin-tepat"></span>
                Tepat Waktu (Masuk)
            </span>
            <span class="d-inline-flex align-items-center">
                <span class="legend-pin pin-terlambat"></span>
                Terlambat (Masuk)
            </span>
            <span class="d-inline-flex align-items-center">
                <span class="legend-pin pin-pulang"></span>
                Presensi Pulang
            </span>
            <span class="d-inline-flex align-items-center">
                <span class="legend-pin" style="background: rgba(37, 75, 214, 0.25); border-color: #254BD6;"></span>
                Radius Presensi ({{ !empty($pengaturan->radius_meter) ? (int)$pengaturan->radius_meter : 100 }} m)
            </span>
        </div>
    </div>

    <div class="card-body p-3">
        @if(count($dataPresensiList) === 0)
            <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center gap-2" role="alert" style="font-size: 13.5px;">
                <i class="bi bi-info-circle-fill text-primary"></i>
                <div>Tidak ada data titik presensi pada tanggal dan kriteria filter yang dipilih. Peta menampilkan posisi kantor default.</div>
            </div>
        @endif
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

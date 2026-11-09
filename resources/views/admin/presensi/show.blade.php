@extends('layouts.admin')

@section('title', 'Detail Berkas Presensi')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #mapDetail {
        height: 340px;
        width: 100%;
        border-radius: 8px;
        z-index: 1;
    }
    .preview-box {
        background-color: #0F172A;
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 240px;
    }
    .preview-box img {
        max-width: 100%;
        max-height: 300px;
        object-fit: contain;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h1-navy">Detail Berkas Presensi</h1>
        <div class="subtitle-navy mb-0">Presensi Digital Bidtik Polda Kalimantan Selatan</div>
    </div>
    <div>
        <a href="{{ route('admin.presensi.index') }}" class="btn btn-sm btn-np-outline">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Rekap
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Selfie & Tanda Tangan -->
    <div class="col-12 col-lg-5">
        <!-- Foto Selfie -->
        <div class="card np-card mb-4">
            <div class="card-header border-0 pb-0 pt-3 px-3">
                <span class="fw-bold text-dark fs-6">Berkas Presensi</span>
            </div>
            <div class="card-body p-3">
                <div class="preview-box mb-2">
                    <img src="{{ route('admin.presensi.file', ['type' => 'foto_selfie', 'filename' => basename($presensi->foto_selfie)]) }}" 
                         alt="Foto Selfie {{ $presensi->anggota->nama ?? '-' }}">
                </div>
                <div class="text-center text-muted small" style="font-size: 11px; letter-spacing: 0.5px; text-transform: uppercase;">
                    SELFIE KAMERA DEPAN
                </div>
            </div>
        </div>

        <!-- Tanda Tangan Digital -->
        <div class="card np-card">
            <div class="card-header border-0 pb-0 pt-3 px-3">
                <span class="fw-bold text-dark fs-6">Tanda Tangan Digital</span>
            </div>
            <div class="card-body p-3 text-center">
                @if($presensi->tanda_tangan && trim($presensi->tanda_tangan) !== '')
                    <div class="p-3 bg-white rounded border mb-2 d-flex align-items-center justify-content-center" style="min-height: 140px;">
                        <img src="{{ route('admin.presensi.file', ['type' => 'tanda_tangan', 'filename' => basename($presensi->tanda_tangan)]) }}" 
                             alt="Tanda Tangan" style="max-height: 120px; max-width: 100%;">
                    </div>
                @else
                    <div class="py-4 text-muted small">
                        Tanda tangan tidak tersedia pada rekaman ini.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Rincian Kehadiran & Peta GPS -->
    <div class="col-12 col-lg-7">
        <!-- Rincian Personel -->
        <div class="card np-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">{{ $presensi->anggota->nama ?? '-' }}</h4>
                        <div class="text-muted small">NRP {{ $presensi->anggota->nrp ?? '-' }} / {{ $presensi->anggota->unit_kerja ?? 'Bid TIK' }}</div>
                    </div>
                    <div>
                        @if($presensi->status_kehadiran === 'tepat_waktu')
                            <span class="badge badge-np-success">Tepat Waktu</span>
                        @else
                            <span class="badge badge-np-danger">Terlambat</span>
                        @endif
                    </div>
                </div>

                <div class="pt-2 border-top text-secondary small">
                    {{ \Carbon\Carbon::parse($presensi->tanggal)->translatedFormat('d M Y') }} / {{ \Carbon\Carbon::parse($presensi->waktu)->format('H:i:s') }} WITA / <span class="text-capitalize">{{ $presensi->jenis_presensi }}</span>
                </div>
            </div>
        </div>

        <!-- Peta Leaflet -->
        <div class="card np-card">
            <div class="card-body p-3">
                <div id="mapDetail"></div>
                <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                    <span class="small text-muted font-monospace" style="font-size: 12px;">{{ $presensi->latitude }}, {{ $presensi->longitude }}</span>
                    @if(!is_null($jarakKeKantor))
                        <span class="small fw-bold {{ $jarakKeKantor <= ($pengaturan->radius_meter ?? 100) ? 'text-success' : 'text-danger' }}" style="font-size: 12px;">
                            Jarak: {{ number_format($jarakKeKantor, 1, ',', '.') }} m (Radius: {{ $pengaturan->radius_meter ?? 100 }}m)
                        </span>
                    @endif
                </div>
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
        const presensiLat = parseFloat("{{ $presensi->latitude }}");
        const presensiLng = parseFloat("{{ $presensi->longitude }}");

        const kantorLat = parseFloat("{{ $pengaturan->latitude ?? -3.3194000 }}");
        const kantorLng = parseFloat("{{ $pengaturan->longitude ?? 114.5925000 }}");
        const radiusKantor = parseInt("{{ $pengaturan->radius_meter ?? 100 }}");

        const map = L.map('mapDetail').setView([presensiLat, presensiLng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const personelMarker = L.marker([presensiLat, presensiLng]).addTo(map);
        personelMarker.bindPopup(`
            <strong>{{ $presensi->anggota->nama ?? 'Personel' }}</strong><br>
            Jenis: {{ strtoupper($presensi->jenis_presensi) }}<br>
            Waktu: {{ $presensi->waktu }} WITA
        `).openPopup();

        const kantorMarker = L.circleMarker([kantorLat, kantorLng], {
            radius: 8,
            color: '#17263F',
            fillColor: '#254BD6',
            fillOpacity: 1
        }).addTo(map);
        kantorMarker.bindPopup('<strong>Kantor {{ $pengaturan->nama_lokasi ?? 'Bidtik Polda Kalsel' }}</strong>');

        L.circle([kantorLat, kantorLng], {
            color: '#254BD6',
            fillColor: '#254BD6',
            fillOpacity: 0.15,
            radius: radiusKantor
        }).addTo(map);

        const polyline = L.polyline([
            [presensiLat, presensiLng],
            [kantorLat, kantorLng]
        ], {
            color: '#DC2626',
            dashArray: '4, 8',
            weight: 2
        }).addTo(map);

        const bounds = L.latLngBounds([
            [presensiLat, presensiLng],
            [kantorLat, kantorLng]
        ]);
        map.fitBounds(bounds, { padding: [40, 40] });

        setTimeout(() => map.invalidateSize(), 300);
    });
</script>
@endpush

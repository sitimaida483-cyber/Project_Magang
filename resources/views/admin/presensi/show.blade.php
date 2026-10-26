@extends('layouts.admin')

@section('title', 'Detail Presensi - ' . ($presensi->anggota->nama ?? 'Personel'))

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #mapDetail {
        height: 380px;
        width: 100%;
        border-radius: 12px;
        z-index: 1;
    }
    .preview-box {
        background-color: #000;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 260px;
    }
    .preview-box img {
        max-width: 100%;
        max-height: 320px;
        object-fit: contain;
    }
    .signature-preview {
        background-color: #ffffff;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 180px;
        padding: 10px;
    }
    .signature-preview img {
        max-width: 100%;
        max-height: 160px;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold text-dark mb-0">Detail Berkas Presensi</h3>
        <p class="text-muted small mb-0">
            Verifikasi keaslian kehadiran personel: foto selfie, tanda tangan digital, dan koordinat GPS.
        </p>
    </div>
    <div>
        <a href="{{ route('admin.presensi.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Rekap
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Foto Selfie & Tanda Tangan -->
    <div class="col-12 col-lg-5">
        <!-- Foto Selfie -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-camera-fill me-1 text-primary"></i> Foto Selfie Kamera Depan
                </h6>
                <span class="badge bg-light text-secondary border">Bukti Visual</span>
            </div>
            <div class="card-body p-3 text-center">
                <div class="preview-box mb-2">
                    <img src="{{ route('admin.presensi.file', ['type' => 'foto_selfie', 'filename' => basename($presensi->foto_selfie)]) }}" 
                         alt="Foto Selfie {{ $presensi->anggota->nama ?? '-' }}">
                </div>
                <small class="text-muted">
                    <i class="bi bi-shield-check text-success me-1"></i> Diambil saat pengiriman presensi
                </small>
            </div>
        </div>

        <!-- Tanda Tangan Digital -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-pen-fill me-1 text-primary"></i> Tanda Tangan Digital
                </h6>
                <span class="badge bg-light text-secondary border">Signature Pad</span>
            </div>
            <div class="card-body p-3 text-center">
                <div class="signature-preview mb-2">
                    <img src="{{ route('admin.presensi.file', ['type' => 'tanda_tangan', 'filename' => basename($presensi->tanda_tangan)]) }}" 
                         alt="Tanda Tangan {{ $presensi->anggota->nama ?? '-' }}">
                </div>
                <small class="text-muted">
                    <i class="bi bi-check-circle text-success me-1"></i> Dibubuhkan langsung oleh personel
                </small>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Rincian Kehadiran & Peta GPS -->
    <div class="col-12 col-lg-7">
        <!-- Kartu Rincian -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-person-vcard-fill me-1 text-primary"></i> Rincian Personel & Kehadiran
                </h6>
                @if($presensi->jenis_presensi === 'masuk')
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                        <i class="bi bi-box-arrow-in-right me-1"></i> PRESENSI MASUK
                    </span>
                @else
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">
                        <i class="bi bi-box-arrow-right me-1"></i> PRESENSI PULANG
                    </span>
                @endif
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <label class="text-muted small d-block">Nama Lengkap</label>
                        <span class="fw-bold fs-6 text-dark">{{ $presensi->anggota->nama ?? '-' }}</span>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="text-muted small d-block">NRP Personel</label>
                        <span class="badge bg-light text-secondary border font-monospace fs-6">
                            {{ $presensi->anggota->nrp ?? '-' }}
                        </span>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="text-muted small d-block">Pangkat / Jabatan</label>
                        <span class="fw-semibold text-secondary">{{ $presensi->anggota->jabatan ?? '-' }}</span>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="text-muted small d-block">Unit Kerja / Satuan</label>
                        <span class="fw-semibold text-secondary">{{ $presensi->anggota->unit_kerja ?? '-' }}</span>
                    </div>

                    <div class="col-12"><hr class="my-1"></div>

                    <div class="col-6 col-sm-4">
                        <label class="text-muted small d-block">Tanggal Presensi</label>
                        <strong class="text-dark">{{ \Carbon\Carbon::parse($presensi->tanggal)->translatedFormat('l, d M Y') }}</strong>
                    </div>
                    <div class="col-6 col-sm-4">
                        <label class="text-muted small d-block">Waktu Tercatat</label>
                        <strong class="text-dark">{{ \Carbon\Carbon::parse($presensi->waktu)->format('H:i:s') }} WITA</strong>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label class="text-muted small d-block">Status Ketepatan</label>
                        @if($presensi->status_kehadiran === 'tepat_waktu')
                            <span class="badge bg-success text-white px-2 py-1">Tepat Waktu</span>
                        @else
                            <span class="badge bg-danger text-white px-2 py-1">Terlambat</span>
                        @endif
                    </div>

                    <div class="col-12 col-sm-6">
                        <label class="text-muted small d-block">Koordinat GPS Personel</label>
                        <span class="font-monospace small text-dark">{{ $presensi->latitude }}, {{ $presensi->longitude }}</span>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="text-muted small d-block">Jarak ke Titik Kantor</label>
                        @if(!is_null($jarakKeKantor))
                            <span class="fw-bold {{ $jarakKeKantor <= ($pengaturan->radius_meter ?? 100) ? 'text-success' : 'text-danger' }}">
                                {{ number_format($jarakKeKantor, 1, ',', '.') }} meter
                            </span>
                            <small class="text-muted">(Radius: {{ $pengaturan->radius_meter ?? 100 }}m)</small>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Peta Lokasi GPS Leaflet -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-geo-alt-fill me-1 text-danger"></i> Peta Posisi Presensi vs Kantor
                </h6>
                <span class="badge bg-light text-secondary border">OpenStreetMap</span>
            </div>
            <div class="card-body p-3">
                <div id="mapDetail"></div>
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

        // Inisialisasi Peta
        const map = L.map('mapDetail').setView([presensiLat, presensiLng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        // Marker Posisi Personel saat Presensi
        const personelMarker = L.marker([presensiLat, presensiLng]).addTo(map);
        personelMarker.bindPopup(`
            <strong>{{ $presensi->anggota->nama ?? 'Personel' }}</strong><br>
            Jenis: {{ strtoupper($presensi->jenis_presensi) }}<br>
            Waktu: {{ $presensi->waktu }} WITA
        `).openPopup();

        // Marker Kantor & Circle Radius
        const kantorMarker = L.circleMarker([kantorLat, kantorLng], {
            radius: 8,
            color: '#1e3a8a',
            fillColor: '#3b82f6',
            fillOpacity: 1
        }).addTo(map);
        kantorMarker.bindPopup('<strong>Kantor {{ $pengaturan->nama_lokasi ?? 'Bidtik Polda Kalsel' }}</strong>');

        L.circle([kantorLat, kantorLng], {
            color: '#1e40af',
            fillColor: '#60a5fa',
            fillOpacity: 0.2,
            radius: radiusKantor
        }).addTo(map);

        // Garis hubung jarak
        const polyline = L.polyline([
            [presensiLat, presensiLng],
            [kantorLat, kantorLng]
        ], {
            color: 'red',
            dashArray: '5, 10',
            weight: 2
        }).addTo(map);

        // Sesuaikan pandangan peta agar mencakup kedua titik
        const bounds = L.latLngBounds([
            [presensiLat, presensiLng],
            [kantorLat, kantorLng]
        ]);
        map.fitBounds(bounds, { padding: [50, 50] });

        setTimeout(() => map.invalidateSize(), 300);
    });
</script>
@endpush

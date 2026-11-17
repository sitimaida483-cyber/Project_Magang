@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="mb-4">
    <h1 class="h1-navy">Dashboard Ringkasan</h1>
    <div class="subtitle-navy">Presensi Digital Bidtik Polda Kalimantan Selatan</div>
</div>

<!-- 5 Stat Cards (Desktop 5 Kolom, Mobile 2 Kolom) -->
<div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
    <div class="col">
        <a href="{{ route('admin.presensi.index', ['tanggal_mulai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'tanggal_selesai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'jenis_presensi' => 'masuk']) }}" class="text-decoration-none">
            <div class="np-stat-card">
                <span class="np-stat-label">Masuk Hari Ini</span>
                <span class="np-stat-value text-primary">{{ $total_masuk }}</span>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="{{ route('admin.presensi.index', ['tanggal_mulai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'tanggal_selesai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'jenis_presensi' => 'pulang']) }}" class="text-decoration-none">
            <div class="np-stat-card">
                <span class="np-stat-label">Pulang Hari Ini</span>
                <span class="np-stat-value text-primary">{{ $total_pulang }}</span>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="{{ route('admin.presensi.index', ['tanggal_mulai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'tanggal_selesai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'status_kehadiran' => 'terlambat']) }}" class="text-decoration-none">
            <div class="np-stat-card">
                <span class="np-stat-label">Terlambat</span>
                <span class="np-stat-value text-primary">{{ $total_terlambat }}</span>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="{{ route('admin.surat.index', ['status' => 'menunggu']) }}" class="text-decoration-none">
            <div class="np-stat-card">
                <span class="np-stat-label">Surat Menunggu</span>
                <span class="np-stat-value text-primary">{{ $total_pengajuan_menunggu ?? 0 }}</span>
            </div>
        </a>
    </div>

    <div class="col col-12 col-sm-6 col-md-4 col-xl">
        <a href="{{ route('admin.anggota.index', ['status' => 'aktif']) }}" class="text-decoration-none">
            <div class="np-stat-card">
                <span class="np-stat-label">Personel Aktif</span>
                <span class="np-stat-value text-primary">{{ $total_anggota_aktif }}</span>
            </div>
        </a>
    </div>
</div>

@if(($total_perlu_ditinjau ?? 0) > 0)
    <div class="alert alert-warning border-0 rounded-3 mb-4 d-flex align-items-center justify-content-between p-3 flex-wrap gap-2" style="background-color: #FEF3C7; border: 1px solid #FCD34D !important; color: #92400E;">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-exclamation fs-5 text-warning flex-shrink-0"></i>
            <div>
                <strong>Perhatian:</strong> Terdapat <strong>{{ $total_perlu_ditinjau }}</strong> presensi hari ini yang ditandai <strong>Perlu Ditinjau</strong> karena anomali telemetri lokasi atau foto.
            </div>
        </div>
        <a href="{{ route('admin.presensi.index', ['status_anomali' => 'perlu_ditinjau', 'tanggal_mulai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'tanggal_selesai' => \Carbon\Carbon::parse($hari_ini)->toDateString()]) }}" class="btn btn-sm btn-warning text-dark fw-bold px-3">
            <i class="bi bi-eye me-1"></i> Tinjau Sekarang
        </a>
    </div>
@endif

<!-- Presensi Terbaru Hari Ini -->
<div class="mb-4">
    <div class="fw-bold text-dark mb-3 fs-6">Presensi Terbaru Hari Ini</div>

    <!-- Desktop Table (>= 768px) -->
    <div class="table-np-container d-none d-md-block">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th style="width: 30%;">PERSONEL / NRP</th>
                    <th style="width: 25%;">TANGGAL / WAKTU</th>
                    <th style="width: 15%;">JENIS</th>
                    <th style="width: 15%;">STATUS</th>
                    <th class="text-end pe-4" style="width: 15%;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($presensi_terbaru as $p)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $p->anggota->nama ?? '-' }}</div>
                            <small class="text-muted">{{ $p->anggota->nrp ?? '-' }} / {{ $p->anggota->unit_kerja ?? 'Bid TIK' }}</small>
                        </td>
                        <td>
                            <div class="text-dark fw-semibold">
                                {{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}
                            </div>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($p->waktu)->format('H:i:s') }} WITA
                            </small>
                        </td>
                        <td>
                            <span class="text-dark fw-semibold text-capitalize">{{ $p->jenis_presensi }}</span>
                        </td>
                        <td>
                            @if($p->status_kehadiran === 'tepat_waktu')
                                <span class="badge rounded-pill badge-np-success">Tepat Waktu</span>
                            @else
                                <span class="badge rounded-pill badge-np-danger">Terlambat</span>
                            @endif

                            @if($p->flag_anomali)
                                <div class="mt-1">
                                    <span class="badge rounded-pill bg-warning text-dark" style="font-size: 11px;" title="{{ implode('; ', $p->daftar_alasan_anomali) }}">
                                        <i class="bi bi-shield-exclamation me-1"></i>Perlu Ditinjau
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.presensi.show', $p->id_presensi) }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <h6 class="fw-bold mb-1">Belum Ada Presensi Hari Ini</h6>
                            <p class="small mb-0">Data presensi yang masuk hari ini akan ditampilkan di sini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card Reflow (< 768px) -->
    <div class="d-md-none">
        @forelse($presensi_terbaru as $p)
            <div class="card np-card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-bold text-dark fs-6">{{ $p->anggota->nama ?? '-' }}</div>
                    <div class="d-flex flex-column align-items-end gap-1">
                        @if($p->status_kehadiran === 'tepat_waktu')
                            <span class="badge rounded-pill badge-np-success">Tepat Waktu</span>
                        @else
                            <span class="badge rounded-pill badge-np-danger">Terlambat</span>
                        @endif
                        @if($p->flag_anomali)
                            <span class="badge rounded-pill bg-warning text-dark" style="font-size: 11px;">
                                <i class="bi bi-shield-exclamation me-1"></i>Perlu Ditinjau
                            </span>
                        @endif
                    </div>
                </div>
                <div class="text-muted small mb-2">NRP {{ $p->anggota->nrp ?? '-' }} / {{ $p->anggota->unit_kerja ?? 'Bid TIK' }}</div>
                <div class="text-secondary small mb-3">
                    <span class="text-capitalize">{{ $p->jenis_presensi }}</span> / {{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}, {{ \Carbon\Carbon::parse($p->waktu)->format('H:i') }} WITA
                </div>
                <a href="{{ route('admin.presensi.show', $p->id_presensi) }}" class="btn btn-sm btn-outline-secondary rounded-pill w-100">
                    Detail Presensi
                </a>
            </div>
        @empty
            <div class="card np-card p-4 text-center text-muted">
                <h6 class="fw-bold mb-1">Belum Ada Presensi Hari Ini</h6>
                <p class="small mb-0">Data presensi yang masuk hari ini akan ditampilkan di sini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

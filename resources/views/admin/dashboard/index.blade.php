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
                <span class="np-stat-value text-dark">{{ $total_masuk }}</span>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="{{ route('admin.presensi.index', ['tanggal_mulai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'tanggal_selesai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'jenis_presensi' => 'pulang']) }}" class="text-decoration-none">
            <div class="np-stat-card">
                <span class="np-stat-label">Pulang Hari Ini</span>
                <span class="np-stat-value text-dark">{{ $total_pulang }}</span>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="{{ route('admin.presensi.index', ['tanggal_mulai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'tanggal_selesai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'status_kehadiran' => 'terlambat']) }}" class="text-decoration-none">
            <div class="np-stat-card">
                <span class="np-stat-label">Terlambat</span>
                <span class="np-stat-value text-dark">{{ $total_terlambat }}</span>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="{{ route('admin.surat.index', ['status' => 'menunggu']) }}" class="text-decoration-none">
            <div class="np-stat-card">
                <span class="np-stat-label">Surat Menunggu</span>
                <span class="np-stat-value text-dark">{{ $total_pengajuan_menunggu ?? 0 }}</span>
            </div>
        </a>
    </div>

    <div class="col col-12 col-sm-6 col-md-4 col-xl">
        <a href="{{ route('admin.anggota.index', ['status' => 'aktif']) }}" class="text-decoration-none">
            <div class="np-stat-card">
                <span class="np-stat-label">Personel Aktif</span>
                <span class="np-stat-value text-dark">{{ $total_anggota_aktif }}</span>
            </div>
        </a>
    </div>
</div>

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
                    <th class="text-end" style="width: 15%;">AKSI</th>
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
                                <span class="badge badge-np-success">Tepat Waktu</span>
                            @else
                                <span class="badge badge-np-danger">Terlambat</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.presensi.show', $p->id_presensi) }}" class="btn btn-sm btn-np-outline">
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
                    @if($p->status_kehadiran === 'tepat_waktu')
                        <span class="badge badge-np-success">Tepat Waktu</span>
                    @else
                        <span class="badge badge-np-danger">Terlambat</span>
                    @endif
                </div>
                <div class="text-muted small mb-2">NRP {{ $p->anggota->nrp ?? '-' }} / {{ $p->anggota->unit_kerja ?? 'Bid TIK' }}</div>
                <div class="text-secondary small mb-3">
                    <span class="text-capitalize">{{ $p->jenis_presensi }}</span> / {{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}, {{ \Carbon\Carbon::parse($p->waktu)->format('H:i') }} WITA
                </div>
                <a href="{{ route('admin.presensi.show', $p->id_presensi) }}" class="btn btn-sm btn-np-outline w-100">
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

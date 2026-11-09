@extends('layouts.admin')

@section('title', 'Detail Profil Personel - ' . $anggota->nama)

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h1-navy">Detail Profil Personel</h1>
        <p class="subtitle-navy mb-0">Presensi Digital Bidtik Polda Kalimantan Selatan</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.anggota.index') }}" class="btn np-btn-outline btn-sm d-inline-flex align-items-center" style="height: 38px;">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <a href="{{ route('admin.anggota.edit', $anggota->id_anggota) }}" class="btn np-btn-outline btn-sm d-inline-flex align-items-center" style="height: 38px;">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
    </div>
</div>

<!-- Header Identitas Personel (Page 32) -->
<div class="np-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
        <div>
            <h2 class="h2-navy mb-1" style="font-size: 22px;">{{ $anggota->nama }}</h2>
            <div class="text-muted fw-semibold" style="font-size: 14px;">
                NRP {{ $anggota->nrp }} / {{ $anggota->jabatan }} / {{ $anggota->unit_kerja }}
            </div>
        </div>
        <div>
            @if($anggota->status === 'aktif')
                <span class="np-badge np-badge-success">Aktif</span>
            @else
                <span class="np-badge np-badge-muted">Nonaktif</span>
            @endif

            @if(! $anggota->hasAkunLogin())
                <span class="np-badge np-badge-warning ms-1">Belum Punya Akun Login</span>
            @endif
        </div>
    </div>
    <div class="text-muted small mt-2 pt-2 border-top" style="border-color: var(--np-line) !important;">
        {{ $anggota->email ?? 'Belum ada email' }} @if($anggota->no_telepon) / {{ $anggota->no_telepon }} @endif
    </div>
</div>

<!-- 3 Kartu Statistik (Page 32) -->
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="np-stat-card">
            <span class="np-stat-label">Total Masuk</span>
            <span class="np-stat-value" style="color: var(--np-primary);">{{ $totalMasuk }}</span>
        </div>
    </div>
    <div class="col-4">
        <div class="np-stat-card">
            <span class="np-stat-label">Total Pulang</span>
            <span class="np-stat-value" style="color: var(--np-ink);">{{ $totalPulang }}</span>
        </div>
    </div>
    <div class="col-4">
        <div class="np-stat-card">
            <span class="np-stat-label">Terlambat</span>
            <span class="np-stat-value" style="color: var(--np-danger);">{{ $totalTerlambat }}</span>
        </div>
    </div>
</div>

<!-- Perangkat Notifikasi Web Push Box (Page 32) -->
<div class="np-card p-4 mb-4" style="background-color: var(--np-soft) !important; border-color: #C7D7FE !important;">
    <h3 class="h3-navy mb-1" style="font-size: 15px; color: var(--np-primary);">Perangkat Notifikasi Web Push</h3>
    <div class="small" style="color: var(--np-ink);">
        @if($anggota->perangkatNotifikasi->count() > 0)
            {{ $anggota->perangkatNotifikasi->where('status', 'aktif')->count() }} perangkat aktif / {{ $anggota->perangkatNotifikasi->first()->jenis_perangkat ?? 'Browser HP' }}
        @else
            0 perangkat aktif / Belum ada perangkat terdaftar
        @endif
    </div>
</div>

<!-- 10 Presensi Terakhir (Page 32) -->
<div class="np-card overflow-hidden">
    <div class="card-header border-bottom py-3 px-4">
        <h2 class="h2-navy mb-0" style="font-size: 16px;">10 Presensi Terakhir</h2>
    </div>

    <!-- Desktop Table (>= 768px) -->
    <div class="table-responsive d-none d-md-block">
        <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
            <thead style="background-color: #FAFCFE; border-bottom: 1px solid var(--np-line);">
                <tr style="height: 48px;">
                    <th class="ps-4 text-uppercase fw-bold" style="font-size: 12px; color: var(--np-muted); letter-spacing: 0.5px; width: 35%;">PERSONEL / NRP</th>
                    <th class="text-uppercase fw-bold" style="font-size: 12px; color: var(--np-muted); letter-spacing: 0.5px; width: 25%;">TANGGAL / WAKTU</th>
                    <th class="text-uppercase fw-bold" style="font-size: 12px; color: var(--np-muted); letter-spacing: 0.5px; width: 15%;">JENIS</th>
                    <th class="text-uppercase fw-bold" style="font-size: 12px; color: var(--np-muted); letter-spacing: 0.5px; width: 15%;">STATUS</th>
                    <th class="text-end pe-4 text-uppercase fw-bold" style="font-size: 12px; color: var(--np-muted); letter-spacing: 0.5px; width: 10%;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayatPresensi as $presensi)
                    <tr style="height: 72px; border-bottom: 1px solid var(--np-line);">
                        <td class="ps-4 py-3">
                            <div class="fw-bold" style="color: var(--np-ink); font-size: 14px;">{{ $anggota->nama }}</div>
                            <div class="text-muted small font-monospace">{{ $anggota->nrp }} / {{ $anggota->unit_kerja ?? 'Bid TIK' }}</div>
                        </td>
                        <td class="py-3">
                            <div class="fw-semibold" style="color: var(--np-ink); font-size: 14px;">
                                {{ \Carbon\Carbon::parse($presensi->tanggal)->translatedFormat('d M Y') }}
                            </div>
                            <div class="text-muted small">{{ \Carbon\Carbon::parse($presensi->waktu)->format('H:i:s') }} WITA</div>
                        </td>
                        <td class="py-3">
                            <span class="fw-semibold" style="color: var(--np-ink); font-size: 13px;">
                                {{ ucfirst($presensi->jenis_presensi) }}
                            </span>
                        </td>
                        <td class="py-3">
                            @if($presensi->status_kehadiran === 'tepat_waktu')
                                <span class="np-badge np-badge-success">Tepat Waktu</span>
                            @else
                                <span class="np-badge np-badge-danger">Terlambat</span>
                            @endif
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <a href="{{ route('admin.presensi.show', $presensi->id_presensi) }}" class="btn np-btn-outline btn-sm px-3 d-inline-flex align-items-center justify-content-center" style="height: 36px;">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-50"></i>
                            Belum ada riwayat presensi yang tercatat untuk personel ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card Reflow (< 768px) -->
    <div class="d-block d-md-none p-3">
        @forelse($riwayatPresensi as $presensi)
            <div class="border rounded-3 p-3 mb-3 bg-white" style="border-color: var(--np-line) !important;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="fw-bold mb-1" style="color: var(--np-ink); font-size: 15px;">{{ $anggota->nama }}</h6>
                        <div class="text-muted small font-monospace">NRP {{ $anggota->nrp }} / {{ $anggota->unit_kerja ?? 'Bid TIK' }}</div>
                    </div>
                    <div>
                        @if($presensi->status_kehadiran === 'tepat_waktu')
                            <span class="np-badge np-badge-success">Tepat Waktu</span>
                        @else
                            <span class="np-badge np-badge-danger">Terlambat</span>
                        @endif
                    </div>
                </div>

                <div class="text-muted small mb-3">
                    {{ ucfirst($presensi->jenis_presensi) }} / {{ \Carbon\Carbon::parse($presensi->tanggal)->translatedFormat('d M Y') }}, {{ \Carbon\Carbon::parse($presensi->waktu)->format('H:i') }} WITA
                </div>

                <a href="{{ route('admin.presensi.show', $presensi->id_presensi) }}" class="btn np-btn-outline w-100 fw-bold d-flex align-items-center justify-content-center" style="height: 42px;">
                    Detail Presensi
                </a>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-50"></i>
                Belum ada data presensi.
            </div>
        @endforelse
    </div>
</div>
@endsection

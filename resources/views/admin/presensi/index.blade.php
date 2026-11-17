@extends('layouts.admin')

@section('title', 'Rekapitulasi Absensi')

@section('content')
<div class="mb-4">
    <h1 class="h1-navy">Rekapitulasi Absensi</h1>
    <div class="subtitle-navy">Presensi Digital Bidtik Polda Kalimantan Selatan</div>
</div>

<!-- 5 Stat Cards -->
<div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
    <div class="col">
        <div class="np-stat-card">
            <span class="np-stat-label">Total Rekaman</span>
            <span class="np-stat-value text-primary">{{ $totalPresensi }}</span>
        </div>
    </div>
    <div class="col">
        <div class="np-stat-card">
            <span class="np-stat-label">Masuk</span>
            <span class="np-stat-value text-primary">{{ $totalMasuk }}</span>
        </div>
    </div>
    <div class="col">
        <div class="np-stat-card">
            <span class="np-stat-label">Pulang</span>
            <span class="np-stat-value text-primary">{{ $totalPulang }}</span>
        </div>
    </div>
    <div class="col">
        <div class="np-stat-card">
            <span class="np-stat-label">Terlambat</span>
            <span class="np-stat-value text-primary">{{ $totalTerlambat }}</span>
        </div>
    </div>
    <div class="col col-12 col-sm-6 col-md-4 col-xl">
        <div class="np-stat-card">
            <span class="np-stat-label">Perlu Ditinjau</span>
            <span class="np-stat-value text-warning">{{ $totalPerluDitinjau ?? 0 }}</span>
        </div>
    </div>
</div>

<!-- 6 Filter Fields & Ekspor -->
<form action="{{ route('admin.presensi.index') }}" method="GET" class="mb-4">
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <label class="form-label">Tanggal Mulai</label>
            <input type="date" name="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}">
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">Tanggal Selesai</label>
            <input type="date" name="tanggal_selesai" class="form-control" value="{{ request('tanggal_selesai') }}">
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
        <div class="col-12 col-md-4">
            <label class="form-label">Status Kehadiran</label>
            <select name="status_kehadiran" class="form-select">
                <option value="">Semua status</option>
                <option value="tepat_waktu" {{ request('status_kehadiran') === 'tepat_waktu' ? 'selected' : '' }}>Tepat Waktu</option>
                <option value="terlambat" {{ request('status_kehadiran') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
            </select>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">Jenis Keterangan</label>
            <select name="jenis_keterangan" class="form-select">
                <option value="">Semua keterangan</option>
                @foreach($daftarJenisKeterangan as $k => $lbl)
                    <option value="{{ $k }}" {{ request('jenis_keterangan') === $k ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">Status Verifikasi</label>
            <select name="status_anomali" class="form-select">
                <option value="">Semua verifikasi</option>
                <option value="perlu_ditinjau" {{ request('status_anomali') === 'perlu_ditinjau' ? 'selected' : '' }}>Perlu Ditinjau (Anomali)</option>
                <option value="normal" {{ request('status_anomali') === 'normal' ? 'selected' : '' }}>Wajar (Tanpa Anomali)</option>
            </select>
        </div>
    </div>

    <!-- Tombol Aksi: Terapkan Filter di kiri, Ekspor di kanan -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
        <div class="d-flex gap-2 align-items-center">
            <button type="submit" class="btn btn-primary fw-bold px-4">
                Terapkan Filter
            </button>
            @if(request()->hasAny(['tanggal_mulai', 'tanggal_selesai', 'id_anggota', 'jenis_presensi', 'status_kehadiran', 'jenis_keterangan', 'status_anomali']))
                <a href="{{ route('admin.presensi.index') }}" class="btn btn-outline-secondary fw-bold px-3">
                    Reset
                </a>
            @endif
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.presensi.ekspor_pdf', request()->all()) }}" data-base-url="{{ route('admin.presensi.ekspor_pdf') }}" class="btn btn-outline-secondary fw-bold px-4 np-export-link">
                Ekspor PDF
            </a>
            <a href="{{ route('admin.presensi.ekspor_excel', request()->all()) }}" data-base-url="{{ route('admin.presensi.ekspor_excel') }}" class="btn btn-outline-secondary fw-bold px-4 np-export-link">
                Ekspor Excel
            </a>
        </div>
    </div>
</form>

<!-- Tabel Rekapitulasi Presensi -->
<div class="mb-4">
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
                @forelse($presensiList as $p)
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
                                @if($p->waktu)
                                    {{ \Carbon\Carbon::parse($p->waktu)->format('H:i:s') }} WITA
                                @else
                                    -
                                @endif
                            </small>
                        </td>
                        <td>
                            @if($p->is_surat ?? false)
                                <span class="text-dark fw-bold">{{ $p->keterangan_surat ?? 'Surat Dinas' }}</span>
                            @else
                                <span class="text-dark fw-bold text-capitalize">{{ $p->jenis_presensi }}</span>
                            @endif
                        </td>
                        <td>
                            @if($p->is_surat ?? false)
                                <span class="badge rounded-pill badge-np-info">Disetujui</span>
                            @elseif($p->status_kehadiran === 'tepat_waktu')
                                <span class="badge rounded-pill badge-np-success">Tepat Waktu</span>
                            @else
                                <span class="badge rounded-pill badge-np-danger">Terlambat</span>
                            @endif

                            @if(! ($p->is_surat ?? false) && $p->flag_anomali)
                                <div class="mt-1">
                                    <span class="badge rounded-pill bg-warning text-dark" style="font-size: 11px;" title="{{ implode('; ', $p->daftar_alasan_anomali) }}">
                                        <i class="bi bi-shield-exclamation me-1"></i>Perlu Ditinjau
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            @if($p->is_surat ?? false)
                                <a href="{{ route('admin.surat.show', $p->id_pengajuan) }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                                    Surat
                                </a>
                            @else
                                <a href="{{ route('admin.presensi.show', $p->id_presensi) }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                                    Detail
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <h6 class="fw-bold mb-1">Tidak Ada Data Presensi</h6>
                            <p class="small mb-0">Tidak ada data presensi yang sesuai dengan kriteria filter.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card Reflow (< 768px) -->
    <div class="d-md-none">
        @forelse($presensiList as $p)
            <div class="card np-card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-bold text-dark fs-6">{{ $p->anggota->nama ?? '-' }}</div>
                    <div class="d-flex flex-column align-items-end gap-1">
                        @if($p->is_surat ?? false)
                            <span class="badge rounded-pill badge-np-info">Disetujui</span>
                        @elseif($p->status_kehadiran === 'tepat_waktu')
                            <span class="badge rounded-pill badge-np-success">Tepat Waktu</span>
                        @else
                            <span class="badge rounded-pill badge-np-danger">Terlambat</span>
                        @endif

                        @if(! ($p->is_surat ?? false) && $p->flag_anomali)
                            <span class="badge rounded-pill bg-warning text-dark" style="font-size: 11px;">
                                <i class="bi bi-shield-exclamation me-1"></i>Perlu Ditinjau
                            </span>
                        @endif
                    </div>
                </div>
                <div class="text-muted small mb-2">NRP {{ $p->anggota->nrp ?? '-' }} / {{ $p->anggota->unit_kerja ?? 'Bid TIK' }}</div>
                <div class="text-secondary small mb-3">
                    @if($p->is_surat ?? false)
                        {{ $p->keterangan_surat ?? 'Surat Dinas' }} / {{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}
                    @else
                        <span class="text-capitalize">{{ $p->jenis_presensi }}</span> / {{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}, {{ \Carbon\Carbon::parse($p->waktu)->format('H:i') }} WITA
                    @endif
                </div>
                @if($p->is_surat ?? false)
                    <a href="{{ route('admin.surat.show', $p->id_pengajuan) }}" class="btn btn-sm btn-outline-secondary rounded-pill w-100">
                        Detail Surat
                    </a>
                @else
                    <a href="{{ route('admin.presensi.show', $p->id_presensi) }}" class="btn btn-sm btn-outline-secondary rounded-pill w-100">
                        Detail Presensi
                    </a>
                @endif
            </div>
        @empty
            <div class="card np-card p-4 text-center text-muted">
                <h6 class="fw-bold mb-1">Tidak Ada Data Presensi</h6>
                <p class="small mb-0">Tidak ada data presensi yang sesuai dengan kriteria filter.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination Footer -->
    @if($presensiList->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            @if($presensiList->onFirstPage())
                <button class="btn btn-sm btn-np-outline" disabled>Sebelumnya</button>
            @else
                <a href="{{ $presensiList->previousPageUrl() }}" class="btn btn-sm btn-np-outline">Sebelumnya</a>
            @endif

            <span class="small text-muted">Halaman {{ $presensiList->currentPage() }} dari {{ $presensiList->lastPage() }}</span>

            @if($presensiList->hasMorePages())
                <a href="{{ $presensiList->nextPageUrl() }}" class="btn btn-sm btn-np-outline">Berikutnya</a>
            @else
                <button class="btn btn-sm btn-np-outline" disabled>Berikutnya</button>
            @endif
        </div>
    @endif
</div>
@endsection

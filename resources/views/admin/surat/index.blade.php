@extends('layouts.admin')

@section('title', 'Kelola Pengajuan Surat')

@section('content')
<!-- Page Header -->
<div class="mb-4">
    <h1 class="h1-navy">Kelola Pengajuan Surat</h1>
    <p class="subtitle-navy mb-0">Presensi Digital Bidtik Polda Kalimantan Selatan</p>
</div>

<!-- Alert Pesan Sukses / Error -->
@if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show mb-4 border-0" role="alert" style="background-color: var(--np-success-soft); color: var(--np-success); border-left: 4px solid var(--np-success) !important; border-radius: 8px;">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('sukses') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- 4 Kartu Statistik -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.surat.index') }}" class="text-decoration-none">
            <div class="np-stat-card h-100" style="{{ !request('status') ? 'border-color: var(--np-primary); background-color: var(--np-surface);' : '' }}">
                <span class="np-stat-label">Total Pengajuan</span>
                <span class="np-stat-value" style="color: var(--np-primary);">{{ $totalPengajuan }}</span>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.surat.index', ['status' => 'menunggu']) }}" class="text-decoration-none">
            <div class="np-stat-card h-100" style="{{ request('status') === 'menunggu' ? 'border-color: var(--np-warning);' : '' }}">
                <span class="np-stat-label">Menunggu</span>
                <span class="np-stat-value" style="color: var(--np-warning);">{{ $totalMenunggu }}</span>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.surat.index', ['status' => 'disetujui']) }}" class="text-decoration-none">
            <div class="np-stat-card h-100" style="{{ request('status') === 'disetujui' ? 'border-color: var(--np-success);' : '' }}">
                <span class="np-stat-label">Disetujui</span>
                <span class="np-stat-value" style="color: var(--np-success);">{{ $totalDisetujui }}</span>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.surat.index', ['status' => 'ditolak']) }}" class="text-decoration-none">
            <div class="np-stat-card h-100" style="{{ request('status') === 'ditolak' ? 'border-color: var(--np-danger);' : '' }}">
                <span class="np-stat-label">Ditolak</span>
                <span class="np-stat-value" style="color: var(--np-danger);">{{ $totalDitolak }}</span>
            </div>
        </a>
    </div>
</div>

<!-- Filter & Pencarian Pengajuan -->
<div class="np-card mb-4 p-3">
    <form action="{{ route('admin.surat.index') }}" method="GET">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label mb-1">Cari Nama / NRP</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nama atau NRP">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label mb-1">Jenis</label>
                <select name="jenis" class="form-select">
                    <option value="">Semua jenis</option>
                    @foreach($daftarJenis as $k => $lbl)
                        <option value="{{ $k }}" {{ request('jenis') === $k ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label mb-1">Personel</label>
                <select name="id_anggota" class="form-select">
                    <option value="">Semua personel</option>
                    @foreach($daftarAnggota as $ang)
                        <option value="{{ $ang->id_anggota }}" {{ request('id_anggota') == $ang->id_anggota ? 'selected' : '' }}>
                            {{ $ang->nama }} ({{ $ang->nrp }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn np-btn-primary flex-grow-1" style="height: 48px;">
                    Terapkan Filter
                </button>
                @if(request('q') || request('status') || request('jenis') || request('id_anggota'))
                    <a href="{{ route('admin.surat.index') }}" class="btn np-btn-outline d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;" title="Reset Filter">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Daftar Pengajuan Surat -->
<div class="np-card overflow-hidden">
    <!-- Desktop Table View (>= 768px) -->
    <div class="table-responsive d-none d-md-block">
        <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
            <thead style="background-color: #FAFCFE; border-bottom: 1px solid var(--np-line);">
                <tr style="height: 48px;">
                    <th class="ps-4 text-uppercase fw-bold" style="font-size: 12px; color: var(--np-muted); letter-spacing: 0.5px; width: 35%;">PERSONEL / JENIS</th>
                    <th class="text-uppercase fw-bold" style="font-size: 12px; color: var(--np-muted); letter-spacing: 0.5px; width: 25%;">PERIODE</th>
                    <th class="text-uppercase fw-bold" style="font-size: 12px; color: var(--np-muted); letter-spacing: 0.5px; width: 20%;">STATUS</th>
                    <th class="text-end pe-4 text-uppercase fw-bold" style="font-size: 12px; color: var(--np-muted); letter-spacing: 0.5px; width: 20%;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftarSurat as $surat)
                    <tr style="height: 80px; border-bottom: 1px solid var(--np-line);">
                        <td class="ps-4 py-3">
                            <div class="fw-bold" style="color: var(--np-ink); font-size: 15px;">{{ $surat->anggota->nama ?? '-' }}</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="fw-semibold" style="color: var(--np-primary); font-size: 13px;">{{ $surat->label_jenis }}</span>
                                <span class="text-muted" style="font-size: 12px;">•</span>
                                <span class="text-muted font-monospace" style="font-size: 12px;">NRP {{ $surat->anggota->nrp ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="py-3">
                            <div class="fw-semibold" style="color: var(--np-ink); font-size: 14px;">
                                {{ \Carbon\Carbon::parse($surat->tanggal_mulai)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($surat->tanggal_selesai)->translatedFormat('d M Y') }}
                            </div>
                            <div class="text-muted small mt-1">
                                {{ \Carbon\Carbon::parse($surat->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($surat->tanggal_selesai)) + 1 }} hari • Diajukan {{ \Carbon\Carbon::parse($surat->created_at)->translatedFormat('d M Y') }}
                            </div>
                        </td>
                        <td class="py-3">
                            @if($surat->status_pengajuan === 'disetujui')
                                <span class="np-badge np-badge-success">Disetujui</span>
                            @elseif($surat->status_pengajuan === 'ditolak')
                                <span class="np-badge np-badge-danger">Ditolak</span>
                            @else
                                <span class="np-badge np-badge-warning">Menunggu</span>
                            @endif
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.surat.show', $surat->id_pengajuan) }}" class="btn np-btn-outline btn-sm px-3 d-inline-flex align-items-center justify-content-center" style="height: 38px;">
                                    Detail
                                </a>
                                <a href="{{ route('admin.surat.file', $surat->id_pengajuan) }}" target="_blank" class="btn np-btn-ghost btn-sm px-2 d-inline-flex align-items-center justify-content-center" style="height: 38px;" title="Buka PDF">
                                    <i class="bi bi-file-earmark-pdf text-danger fs-6"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                            Tidak ada data pengajuan surat yang ditemukan sesuai kriteria filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card Reflow (< 768px) -->
    <div class="d-block d-md-none p-3">
        @forelse($daftarSurat as $surat)
            <div class="border rounded-3 p-3 mb-3 bg-white" style="border-color: var(--np-line) !important;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    @if($surat->status_pengajuan === 'disetujui')
                        <span class="np-badge np-badge-success">Disetujui</span>
                    @elseif($surat->status_pengajuan === 'ditolak')
                        <span class="np-badge np-badge-danger">Ditolak</span>
                    @else
                        <span class="np-badge np-badge-warning">Menunggu</span>
                    @endif
                    <small class="text-muted">{{ \Carbon\Carbon::parse($surat->created_at)->translatedFormat('d M Y') }}</small>
                </div>
                <h6 class="fw-bold mb-1" style="color: var(--np-ink); font-size: 16px;">{{ $surat->label_jenis }}</h6>
                <div class="text-muted small mb-2">{{ $surat->anggota->nama ?? '-' }} (NRP {{ $surat->anggota->nrp ?? '-' }})</div>
                <div class="d-flex justify-content-between align-items-center small text-muted mb-3 pt-2 border-top">
                    <span>{{ \Carbon\Carbon::parse($surat->tanggal_mulai)->translatedFormat('d M') }} - {{ \Carbon\Carbon::parse($surat->tanggal_selesai)->translatedFormat('d M Y') }}</span>
                    <span>PDF terlampir / {{ \Carbon\Carbon::parse($surat->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($surat->tanggal_selesai)) + 1 }} hari</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.surat.show', $surat->id_pengajuan) }}" class="btn np-btn-primary w-100 fw-bold d-flex align-items-center justify-content-center" style="height: 44px;">
                        Detail Pengajuan
                    </a>
                    <a href="{{ route('admin.surat.file', $surat->id_pengajuan) }}" target="_blank" class="btn np-btn-outline d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;" title="Buka PDF">
                        <i class="bi bi-file-earmark-pdf text-danger"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                Tidak ada data pengajuan surat yang ditemukan.
            </div>
        @endforelse
    </div>

    <!-- Paginasi -->
    @if($daftarSurat->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: #FAFCFE; border-color: var(--np-line) !important;">
            <small class="text-muted">
                Menampilkan {{ $daftarSurat->firstItem() }} - {{ $daftarSurat->lastItem() }} dari {{ $daftarSurat->total() }} data
            </small>
            <div>
                {{ $daftarSurat->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>
@endsection

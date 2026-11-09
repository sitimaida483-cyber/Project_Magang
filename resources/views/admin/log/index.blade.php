@extends('layouts.admin')

@section('title', 'Log Aktivitas Admin')

@section('content')
<!-- Page Header -->
<div class="mb-4">
    <h1 class="h1-navy">Log Aktivitas Admin</h1>
    <p class="subtitle-navy mb-0">Presensi Digital Bidtik Polda Kalimantan Selatan</p>
</div>

<!-- 3 Kartu Statistik (Page 37) -->
<div class="row g-3 mb-4">
    <div class="col-4">
        <a href="{{ route('admin.log.index') }}" class="text-decoration-none">
            <div class="np-stat-card h-100" style="{{ (!request('tanggal_mulai') && !request('tanggal_selesai') && !request('alamat_ip')) ? 'border-color: var(--np-primary);' : '' }}">
                <span class="np-stat-label">Total Log</span>
                <span class="np-stat-value" style="color: var(--np-primary);">{{ number_format($totalLog) }}</span>
            </div>
        </a>
    </div>
    <div class="col-4">
        <a href="{{ route('admin.log.index', ['tanggal_mulai' => date('Y-m-d'), 'tanggal_selesai' => date('Y-m-d')]) }}" class="text-decoration-none">
            <div class="np-stat-card h-100" style="{{ (request('tanggal_mulai') === date('Y-m-d') && request('tanggal_selesai') === date('Y-m-d')) ? 'border-color: var(--np-primary);' : '' }}">
                <span class="np-stat-label">Aktivitas Hari Ini</span>
                <span class="np-stat-value" style="color: var(--np-ink);">{{ number_format($logHariIni) }}</span>
            </div>
        </a>
    </div>
    <div class="col-4">
        <a href="#modalIpUnik" data-bs-toggle="modal" class="text-decoration-none">
            <div class="np-stat-card h-100" style="{{ request('alamat_ip') ? 'border-color: var(--np-primary);' : '' }}">
                <span class="np-stat-label">IP Unik</span>
                <span class="np-stat-value" style="color: var(--np-primary);">{{ number_format($ipUnik) }}</span>
            </div>
        </a>
    </div>
</div>

<!-- Filter Pencarian (Page 37) -->
<div class="np-card mb-4 p-3">
    <form method="GET" action="{{ route('admin.log.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label mb-1">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}">
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label mb-1">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control" value="{{ request('tanggal_selesai') }}">
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label mb-1">Tabel Terkait</label>
                <select name="tabel_terkait" class="form-select">
                    <option value="">Semua tabel</option>
                    @foreach($daftarTabel as $tbl)
                        <option value="{{ $tbl }}" {{ request('tabel_terkait') == $tbl ? 'selected' : '' }}>
                            {{ ucfirst($tbl) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label mb-1">Cari Aktivitas</label>
                <input type="text" name="q" class="form-control" placeholder="Teks aktivitas" value="{{ request('q') }}">
            </div>

            <div class="col-12 mt-3 d-flex gap-2">
                <button type="submit" class="btn np-btn-primary" style="height: 48px; min-width: 160px;">
                    Terapkan Filter
                </button>
                @if(request('tanggal_mulai') || request('tanggal_selesai') || request('tabel_terkait') || request('q') || request('alamat_ip'))
                    <a href="{{ route('admin.log.index') }}" class="btn np-btn-outline d-inline-flex align-items-center justify-content-center" style="height: 48px; width: 48px;" title="Reset Filter">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                @endif
            </div>

            @if(request('alamat_ip'))
                <input type="hidden" name="alamat_ip" value="{{ request('alamat_ip') }}">
                <div class="col-12 mt-2">
                    <span class="np-badge np-badge-info p-2 d-inline-flex align-items-center gap-2">
                        <span>Memfilter IP: <strong>{{ request('alamat_ip') }}</strong></span>
                        <a href="{{ request()->fullUrlWithQuery(['alamat_ip' => null]) }}" class="text-danger fw-bold ms-2 text-decoration-none" title="Hapus filter IP">&times;</a>
                    </span>
                </div>
            @endif
        </div>
    </form>
</div>

<!-- Daftar Log Aktivitas (Page 37) -->
<div class="np-card overflow-hidden">
    <!-- Desktop Table View (>= 768px) -->
    <div class="table-responsive d-none d-md-block">
        <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
            <thead style="background-color: #FAFCFE; border-bottom: 1px solid var(--np-line);">
                <tr style="height: 48px;">
                    <th class="ps-4 text-uppercase fw-bold" style="font-size: 12px; color: var(--np-muted); letter-spacing: 0.5px; width: 35%;">AKTIVITAS / DETAIL</th>
                    <th class="text-uppercase fw-bold" style="font-size: 12px; color: var(--np-muted); letter-spacing: 0.5px; width: 25%;">ADMIN / WAKTU</th>
                    <th class="text-uppercase fw-bold" style="font-size: 12px; color: var(--np-muted); letter-spacing: 0.5px; width: 20%;">MODUL TERKAIT</th>
                    <th class="text-end pe-4 text-uppercase fw-bold" style="font-size: 12px; color: var(--np-muted); letter-spacing: 0.5px; width: 20%;">ALAMAT IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr style="height: 76px; border-bottom: 1px solid var(--np-line);">
                        <td class="ps-4 py-3">
                            <div class="fw-bold" style="color: var(--np-ink); font-size: 15px;">{{ $log->aktivitas }}</div>
                            @if($log->keterangan)
                                <div class="text-muted small mt-1">{{ $log->keterangan }}</div>
                            @endif
                        </td>
                        <td class="py-3">
                            <div class="fw-semibold" style="color: var(--np-ink); font-size: 14px;">
                                {{ $log->admin->nama ?? 'Sistem' }}
                            </div>
                            <div class="text-muted small mt-1">
                                {{ \Carbon\Carbon::parse($log->waktu_aktivitas)->translatedFormat('d M Y') }}, {{ \Carbon\Carbon::parse($log->waktu_aktivitas)->format('H:i:s') }} WITA
                            </div>
                        </td>
                        <td class="py-3">
                            @if($log->tabel_terkait)
                                <span class="np-badge np-badge-muted">{{ ucfirst($log->tabel_terkait) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="pe-4 py-3 text-end font-monospace text-muted small">
                            <a href="{{ route('admin.log.index', ['alamat_ip' => $log->alamat_ip]) }}" class="text-decoration-none" title="Filter IP ini">
                                {{ $log->alamat_ip ?? '127.0.0.1' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-50"></i>
                            Tidak ditemukan riwayat log aktivitas pada kriteria filter yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card Reflow (< 768px - Exact Page 37 layout) -->
    <div class="d-block d-md-none p-3">
        @forelse($logs as $log)
            <div class="border rounded-3 p-3 mb-3 bg-white" style="border-color: var(--np-line) !important;">
                <h6 class="fw-bold mb-1" style="color: var(--np-ink); font-size: 15px;">{{ $log->aktivitas }}</h6>
                <div class="text-muted small mb-1">
                    {{ $log->admin->nama ?? 'Admin' }} / {{ \Carbon\Carbon::parse($log->waktu_aktivitas)->translatedFormat('d M Y') }}, {{ \Carbon\Carbon::parse($log->waktu_aktivitas)->format('H:i') }} WITA
                </div>
                <div class="text-muted small font-monospace">
                    IP {{ $log->alamat_ip ?? '127.0.0.1' }} / {{ $log->tabel_terkait ?? '-' }}
                </div>
                @if($log->keterangan)
                    <div class="small mt-2 pt-2 border-top text-muted" style="border-color: var(--np-line) !important;">
                        {{ $log->keterangan }}
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-50"></i>
                Tidak ditemukan riwayat log aktivitas.
            </div>
        @endforelse
    </div>

    <!-- Paginasi -->
    @if($logs->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: #FAFCFE; border-color: var(--np-line) !important;">
            <small class="text-muted">
                Menampilkan {{ $logs->firstItem() }} - {{ $logs->lastItem() }} dari {{ $logs->total() }} aktivitas
            </small>
            <div>
                {{ $logs->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

<!-- Modal Rincian IP Unik (Page 38) -->
@push('modals')
<div class="modal fade" id="modalIpUnik" tabindex="-1" aria-labelledby="modalIpUnikLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 552px;">
        <div class="modal-content border-0" style="border-radius: var(--np-radius-card); border: 1px solid var(--np-line); box-shadow: 0 10px 25px rgba(23,38,63,0.1);">
            <div class="p-4">
                <h2 class="h2-navy mb-3" id="modalIpUnikLabel" style="font-size: 20px;">Rincian Alamat IP Unik</h2>

                <div class="mb-4" style="max-height: 380px; overflow-y: auto;">
                    @forelse($daftarIpUnik as $ipItem)
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom" style="border-color: var(--np-line) !important;">
                            <div>
                                <div class="fw-bold font-monospace" style="color: var(--np-ink); font-size: 15px;">{{ $ipItem->alamat_ip }}</div>
                                <div class="text-muted small">{{ $ipItem->total_aktivitas }} aktivitas</div>
                            </div>
                            <div>
                                <a href="{{ route('admin.log.index', ['alamat_ip' => $ipItem->alamat_ip]) }}" class="btn np-btn-outline btn-sm px-3 fw-bold d-inline-flex align-items-center justify-content-center" style="height: 36px;">
                                    Filter
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            Belum ada riwayat alamat IP unik yang tercatat.
                        </div>
                    @endforelse
                </div>

                <button type="button" class="btn np-btn-outline w-100 fw-bold" style="height: 48px;" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endpush
@endsection

@extends('layouts.admin')

@section('title', 'Log Aktivitas Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold text-dark mb-0">Audit Trail Log Aktivitas Admin</h3>
        <p class="text-muted small mb-0">
            Pencatatan riwayat keamanan dan audit seluruh tindakan administratif di Sistem Presensi Web Bidtik Polda Kalsel.
        </p>
    </div>
</div>

<!-- Kartu Ringkasan Log -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <div class="card card-stat bg-white p-3 border-start border-4 border-dark">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted text-uppercase fw-semibold">Total Log Tercatat</small>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($totalLog) }}</h3>
                    <small class="text-muted">Seluruh riwayat aksi</small>
                </div>
                <div class="p-3 bg-dark bg-opacity-10 rounded-circle text-dark fs-4">
                    <i class="bi bi-journal-text"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card card-stat bg-white p-3 border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted text-uppercase fw-semibold">Aktivitas Hari Ini</small>
                    <h3 class="fw-bold text-primary mb-0 mt-1">{{ number_format($logHariIni) }}</h3>
                    <small class="text-muted">Aksi hari ini (WITA)</small>
                </div>
                <div class="p-3 bg-primary bg-opacity-10 rounded-circle text-primary fs-4">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card card-stat bg-white p-3 border-start border-4 border-info">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted text-uppercase fw-semibold">Alamat IP Unik</small>
                    <h3 class="fw-bold text-info mb-0 mt-1">{{ number_format($ipUnik) }}</h3>
                    <small class="text-muted">Sumber perangkat akses</small>
                </div>
                <div class="p-3 bg-info bg-opacity-10 rounded-circle text-info fs-4">
                    <i class="bi bi-hdd-network"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulir Filter & Pencarian -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="fw-bold text-dark mb-0">
            <i class="bi bi-funnel-fill me-1 text-primary"></i> Filter Riwayat Audit Log
        </h6>
    </div>
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.log.index') }}" class="row g-2 align-items-end">
            <!-- Filter Tanggal Mulai -->
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">
                    <i class="bi bi-calendar-event me-1"></i> Tanggal Mulai
                </label>
                <input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="{{ request('tanggal_mulai') }}">
            </div>

            <!-- Filter Tanggal Selesai -->
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">
                    <i class="bi bi-calendar-event-fill me-1"></i> Tanggal Selesai
                </label>
                <input type="date" name="tanggal_selesai" class="form-control form-control-sm" value="{{ request('tanggal_selesai') }}">
            </div>

            <!-- Filter Modul / Tabel -->
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">
                    <i class="bi bi-layers me-1"></i> Modul Terkait
                </label>
                <select name="tabel_terkait" class="form-select form-select-sm">
                    <option value="">-- Semua Modul --</option>
                    @foreach($daftarTabel as $tbl)
                        <option value="{{ $tbl }}" {{ request('tabel_terkait') == $tbl ? 'selected' : '' }}>
                            {{ ucfirst($tbl) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Pencarian Kata Kunci -->
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">
                    <i class="bi bi-search me-1"></i> Cari Kata Kunci / IP
                </label>
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Aksi, keterangan, IP..." value="{{ request('q') }}">
            </div>

            <!-- Tombol Aksi -->
            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold" style="background-color: #0e2238; border-color: #0e2238;" title="Terapkan Filter">
                    <i class="bi bi-filter"></i>
                </button>
                <a href="{{ route('admin.log.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Riwayat Log Aktivitas -->
<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: #0e2238; color: #ffffff;">
                    <tr>
                        <th class="text-center py-3" style="width: 50px;">NO</th>
                        <th class="py-3" style="width: 170px;">WAKTU (WITA)</th>
                        <th class="py-3" style="width: 170px;">ADMINISTRATOR</th>
                        <th class="py-3" style="width: 200px;">AKTIVITAS / AKSI</th>
                        <th class="py-3" style="width: 130px;">MODUL</th>
                        <th class="py-3">KETERANGAN / DETAIL</th>
                        <th class="py-3 text-center" style="width: 130px;">ALAMAT IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $index => $log)
                        <tr>
                            <!-- Nomor -->
                            <td class="text-center text-muted fw-semibold">
                                {{ $logs->firstItem() + $index }}
                            </td>

                            <!-- Waktu Aktivitas -->
                            <td>
                                <div class="fw-semibold text-dark">
                                    {{ \Carbon\Carbon::parse($log->waktu_aktivitas)->translatedFormat('d M Y') }}
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($log->waktu_aktivitas)->format('H:i:s') }} WITA
                                </small>
                            </td>

                            <!-- Admin Pelaksana -->
                            <td>
                                @if($log->admin)
                                    <div class="fw-bold text-dark">{{ $log->admin->nama }}</div>
                                    <small class="text-muted"><i class="bi bi-person-badge me-1"></i>{{ $log->admin->username }}</small>
                                @else
                                    <span class="text-muted font-italic">Sistem / ID: {{ $log->id_admin }}</span>
                                @endif
                            </td>

                            <!-- Nama Aktivitas -->
                            <td>
                                @php
                                    $aktLower = strtolower($log->aktivitas);
                                    $badgeClass = 'bg-secondary';
                                    $iconClass = 'bi-info-circle';

                                    if (str_contains($aktLower, 'login')) {
                                        $badgeClass = 'bg-success';
                                        $iconClass = 'bi-box-arrow-in-right';
                                    } elseif (str_contains($aktLower, 'logout')) {
                                        $badgeClass = 'bg-dark';
                                        $iconClass = 'bi-box-arrow-right';
                                    } elseif (str_contains($aktLower, 'tambah')) {
                                        $badgeClass = 'bg-primary';
                                        $iconClass = 'bi-plus-circle';
                                    } elseif (str_contains($aktLower, 'ubah') || str_contains($aktLower, 'update')) {
                                        $badgeClass = 'bg-warning text-dark';
                                        $iconClass = 'bi-pencil-square';
                                    } elseif (str_contains($aktLower, 'hapus') || str_contains($aktLower, 'delete')) {
                                        $badgeClass = 'bg-danger';
                                        $iconClass = 'bi-trash';
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }} px-2 py-1">
                                    <i class="bi {{ $iconClass }} me-1"></i>{{ $log->aktivitas }}
                                </span>
                            </td>

                            <!-- Modul / Tabel Terkait -->
                            <td>
                                @if($log->tabel_terkait)
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-table me-1"></i>{{ ucfirst($log->tabel_terkait) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <!-- Keterangan Detail -->
                            <td>
                                <span class="text-dark">{{ $log->keterangan ?? '-' }}</span>
                            </td>

                            <!-- Alamat IP -->
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">
                                    {{ $log->alamat_ip ?? '127.0.0.1' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-check fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                Tidak ditemukan riwayat log aktivitas pada kriteria filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Paginasi -->
    @if($logs->hasPages())
        <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted">
                Menampilkan <strong>{{ $logs->firstItem() }}</strong> sampai <strong>{{ $logs->lastItem() }}</strong> dari <strong>{{ $logs->total() }}</strong> aktivitas
            </small>
            <div>
                {{ $logs->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold text-dark mb-0">Dashboard Ringkasan</h3>
        <p class="text-muted small mb-0">
            <i class="bi bi-calendar3 me-1"></i> Data presensi hari ini: 
            <strong>{{ \Carbon\Carbon::parse($hari_ini)->translatedFormat('l, d F Y') }}</strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('/admin/presensi') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-table me-1"></i> Lihat Semua Rekap
        </a>
        <a href="{{ url('/admin/peta') }}" class="btn btn-primary btn-sm" style="background-color: #0e2238; border-color: #0e2238;">
            <i class="bi bi-geo-alt me-1"></i> Pantau Peta
        </a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
    <div class="col">
        <a href="{{ route('admin.presensi.index', ['tanggal_mulai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'tanggal_selesai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'jenis_presensi' => 'masuk']) }}" 
           class="card card-stat p-3 bg-white border-start border-4 border-success text-decoration-none h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Presensi Masuk</span>
                    <h3 class="fw-bold text-success mb-0 mt-1">{{ $total_masuk }}</h3>
                    <small class="text-muted" style="font-size: 0.72rem;">Hari ini <i class="bi bi-arrow-right-short text-success"></i></small>
                </div>
                <div class="p-2 bg-success bg-opacity-10 rounded-circle text-success fs-4">
                    <i class="bi bi-box-arrow-in-right"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="{{ route('admin.presensi.index', ['tanggal_mulai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'tanggal_selesai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'jenis_presensi' => 'pulang']) }}" 
           class="card card-stat p-3 bg-white border-start border-4 border-primary text-decoration-none h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Presensi Pulang</span>
                    <h3 class="fw-bold text-primary mb-0 mt-1">{{ $total_pulang }}</h3>
                    <small class="text-muted" style="font-size: 0.72rem;">Hari ini <i class="bi bi-arrow-right-short text-primary"></i></small>
                </div>
                <div class="p-2 bg-primary bg-opacity-10 rounded-circle text-primary fs-4">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="{{ route('admin.presensi.index', ['tanggal_mulai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'tanggal_selesai' => \Carbon\Carbon::parse($hari_ini)->toDateString(), 'status_kehadiran' => 'terlambat']) }}" 
           class="card card-stat p-3 bg-white border-start border-4 border-danger text-decoration-none h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Terlambat</span>
                    <h3 class="fw-bold text-danger mb-0 mt-1">{{ $total_terlambat }}</h3>
                    <small class="text-muted" style="font-size: 0.72rem;">Lewat jam masuk <i class="bi bi-arrow-right-short text-danger"></i></small>
                </div>
                <div class="p-2 bg-danger bg-opacity-10 rounded-circle text-danger fs-4">
                    <i class="bi bi-alarm"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="{{ route('admin.surat.index', ['status' => 'menunggu']) }}" 
           class="card card-stat p-3 bg-white border-start border-4 border-warning text-decoration-none h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Pengajuan Menunggu</span>
                    <h3 class="fw-bold text-warning mb-0 mt-1">{{ $total_pengajuan_menunggu ?? 0 }}</h3>
                    <small class="text-muted" style="font-size: 0.72rem;">Surat izin/dinas <i class="bi bi-arrow-right-short text-warning"></i></small>
                </div>
                <div class="p-2 bg-warning bg-opacity-10 rounded-circle text-warning fs-4">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="{{ route('admin.anggota.index', ['status' => 'aktif']) }}" 
           class="card card-stat p-3 bg-white border-start border-4 border-info text-decoration-none h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Anggota Aktif</span>
                    <h3 class="fw-bold text-info mb-0 mt-1">{{ $total_anggota_aktif }}</h3>
                    <small class="text-muted" style="font-size: 0.72rem;">Total terdaftar <i class="bi bi-arrow-right-short text-info"></i></small>
                </div>
                <div class="p-2 bg-info bg-opacity-10 rounded-circle text-info fs-4">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Table Card: 10 Presensi Terbaru -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="fw-bold text-dark mb-0">
            <i class="bi bi-clock-history me-1 text-primary"></i> Presensi Terbaru Hari Ini
        </h5>
        <span class="badge bg-secondary">10 Data Terakhir</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-3">NRP / Nama Anggota</th>
                        <th>Jabatan & Satuan</th>
                        <th>Jenis</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th class="text-center">Koordinat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($presensi_terbaru as $p)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold text-dark">{{ $p->anggota->nama ?? '-' }}</div>
                                <span class="badge bg-light text-secondary border">NRP: {{ $p->anggota->nrp ?? '-' }}</span>
                            </td>
                            <td>
                                <div>{{ $p->anggota->jabatan ?? '-' }}</div>
                                <small class="text-muted">{{ $p->anggota->unit_kerja ?? '-' }}</small>
                            </td>
                            <td>
                                @if($p->jenis_presensi === 'masuk')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                                    </span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        <i class="bi bi-box-arrow-right me-1"></i> Pulang
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold">{{ \Carbon\Carbon::parse($p->waktu)->format('H:i:s') }}</span>
                            </td>
                            <td>
                                @if($p->status_kehadiran === 'tepat_waktu')
                                    <span class="badge bg-success text-white">Tepat Waktu</span>
                                @else
                                    <span class="badge bg-danger text-white">Terlambat</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <small class="text-muted d-block">{{ $p->latitude }}, {{ $p->longitude }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                Belum ada aktivitas presensi pada hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

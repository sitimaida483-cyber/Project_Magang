@extends('layouts.admin')

@section('title', 'Rekap Absensi Anggota')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold text-dark mb-0">Rekapitulasi Absensi Anggota</h3>
        <p class="text-muted small mb-0">
            Laporan lengkap riwayat presensi masuk dan pulang personel Bidtik Polda Kalimantan Selatan.
        </p>
    </div>
    <!-- Tombol Ekspor -->
    <div class="d-flex gap-2">
        <a href="{{ route('admin.presensi.ekspor_excel', request()->all()) }}" class="btn btn-outline-success btn-sm fw-semibold">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Ekspor Excel (.xlsx)
        </a>
        <a href="{{ route('admin.presensi.ekspor_pdf', request()->all()) }}" class="btn btn-outline-danger btn-sm fw-semibold">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Cetak PDF
        </a>
    </div>
</div>

<!-- Kartu Ringkasan Terfilter -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card card-stat bg-white p-3 border-start border-4 border-dark">
            <small class="text-muted text-uppercase fw-semibold">Total Rekap</small>
            <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalPresensi }}</h3>
            <small class="text-muted">Data terfilter</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat bg-white p-3 border-start border-4 border-success">
            <small class="text-muted text-uppercase fw-semibold">Presensi Masuk</small>
            <h3 class="fw-bold text-success mb-0 mt-1">{{ $totalMasuk }}</h3>
            <small class="text-muted">Total kehadiran</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat bg-white p-3 border-start border-4 border-primary">
            <small class="text-muted text-uppercase fw-semibold">Presensi Pulang</small>
            <h3 class="fw-bold text-primary mb-0 mt-1">{{ $totalPulang }}</h3>
            <small class="text-muted">Total kepulangan</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat bg-white p-3 border-start border-4 border-danger">
            <small class="text-muted text-uppercase fw-semibold">Terlambat</small>
            <h3 class="fw-bold text-danger mb-0 mt-1">{{ $totalTerlambat }}</h3>
            <small class="text-muted">Lewat jam masuk</small>
        </div>
    </div>
</div>

<!-- Formulir Filter Pencarian -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="fw-bold text-dark mb-0">
            <i class="bi bi-funnel-fill me-1 text-primary"></i> Filter Periode & Kriteria Presensi
        </h6>
    </div>
    <div class="card-body p-3">
        <form action="{{ route('admin.presensi.index') }}" method="GET" class="row g-2">
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small text-muted mb-1">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="{{ request('tanggal_mulai') }}">
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small text-muted mb-1">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control form-control-sm" value="{{ request('tanggal_selesai') }}">
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small text-muted mb-1">Pilih Personel</label>
                <select name="id_anggota" class="form-select form-select-sm">
                    <option value="">Semua Personel</option>
                    @foreach($anggotaList as $a)
                        <option value="{{ $a->id_anggota }}" {{ request('id_anggota') == $a->id_anggota ? 'selected' : '' }}>
                            {{ $a->nrp }} - {{ $a->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-3 col-lg-2">
                <label class="form-label small text-muted mb-1">Jenis Presensi</label>
                <select name="jenis_presensi" class="form-select form-select-sm">
                    <option value="">Semua Jenis</option>
                    <option value="masuk" {{ request('jenis_presensi') === 'masuk' ? 'selected' : '' }}>Masuk</option>
                    <option value="pulang" {{ request('jenis_presensi') === 'pulang' ? 'selected' : '' }}>Pulang</option>
                </select>
            </div>
            <div class="col-6 col-sm-3 col-lg-2">
                <label class="form-label small text-muted mb-1">Status Kehadiran</label>
                <select name="status_kehadiran" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="tepat_waktu" {{ request('status_kehadiran') === 'tepat_waktu' ? 'selected' : '' }}>Tepat Waktu</option>
                    <option value="terlambat" {{ request('status_kehadiran') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                @if(request()->hasAny(['tanggal_mulai', 'tanggal_selesai', 'id_anggota', 'jenis_presensi', 'status_kehadiran']))
                    <a href="{{ route('admin.presensi.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                        Reset Filter
                    </a>
                @endif
                <button type="submit" class="btn btn-primary btn-sm px-4" style="background-color: #0e2238; border-color: #0e2238;">
                    <i class="bi bi-filter me-1"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Rekap Presensi -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 50px;">No</th>
                        <th>Waktu & Tanggal</th>
                        <th>NRP / Personel</th>
                        <th>Jabatan & Satuan</th>
                        <th class="text-center">Jenis</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($presensiList as $index => $p)
                        <tr>
                            <td class="ps-3 text-muted">
                                {{ $presensiList->firstItem() + $index }}
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}</div>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($p->waktu)->format('H:i:s') }} WITA</small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $p->anggota->nama ?? '-' }}</div>
                                <span class="badge bg-light text-secondary border font-monospace">NRP: {{ $p->anggota->nrp ?? '-' }}</span>
                            </td>
                            <td>
                                <div>{{ $p->anggota->jabatan ?? '-' }}</div>
                                <small class="text-muted">{{ $p->anggota->unit_kerja ?? '-' }}</small>
                            </td>
                            <td class="text-center">
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
                            <td class="text-center">
                                @if($p->status_kehadiran === 'tepat_waktu')
                                    <span class="badge bg-success text-white">Tepat Waktu</span>
                                @else
                                    <span class="badge bg-danger text-white">Terlambat</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.presensi.show', $p->id_presensi) }}" class="btn btn-outline-info btn-sm" title="Lihat Detail Foto, TTD, & GPS">
                                    <i class="bi bi-eye-fill me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                Tidak ada data presensi yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($presensiList->hasPages())
        <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Menampilkan {{ $presensiList->firstItem() }} - {{ $presensiList->lastItem() }} dari {{ $presensiList->total() }} rekaman
            </small>
            <div>
                {{ $presensiList->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>
@endsection

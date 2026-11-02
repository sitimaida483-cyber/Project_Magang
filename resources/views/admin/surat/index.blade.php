@extends('layouts.admin')

@section('title', 'Kelola Pengajuan Surat')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold text-dark mb-0">Kelola Pengajuan Surat</h3>
        <p class="text-muted small mb-0">
            Verifikasi permohonan izin, sakit, cuti, surat dinas, dan lampiran PDF personel Bidtik Polda Kalsel.
        </p>
    </div>
</div>

<!-- Alert Sukses / Error -->
@if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show small mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i> {{ session('sukses') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Ringkasan Statistik Kartu -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="{{ route('admin.surat.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-primary h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold d-block">Total Pengajuan</small>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalPengajuan }}</h3>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 fs-4">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <a href="{{ route('admin.surat.index', ['status' => 'menunggu']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-warning h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold d-block">Menunggu Verifikasi</small>
                        <h3 class="fw-bold text-warning mb-0 mt-1">{{ $totalMenunggu }}</h3>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 fs-4">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <a href="{{ route('admin.surat.index', ['status' => 'disetujui']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-success h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold d-block">Disetujui</small>
                        <h3 class="fw-bold text-success mb-0 mt-1">{{ $totalDisetujui }}</h3>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 fs-4">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <a href="{{ route('admin.surat.index', ['status' => 'ditolak']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-danger h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold d-block">Ditolak</small>
                        <h3 class="fw-bold text-danger mb-0 mt-1">{{ $totalDitolak }}</h3>
                    </div>
                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 fs-4">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Card Filter Pencarian -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="fw-bold text-dark mb-0">
            <i class="bi bi-funnel me-1 text-primary"></i> Filter & Pencarian Pengajuan
        </h6>
    </div>
    <div class="card-body p-3">
        <form action="{{ route('admin.surat.index') }}" method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small text-secondary fw-semibold mb-1">Cari Nama / NRP</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Ketik nama atau NRP...">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small text-secondary fw-semibold mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small text-secondary fw-semibold mb-1">Jenis Keterangan</label>
                    <select name="jenis" class="form-select form-select-sm">
                        <option value="">Semua Jenis</option>
                        @foreach($daftarJenis as $k => $lbl)
                            <option value="{{ $k }}" {{ request('jenis') === $k ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small text-secondary fw-semibold mb-1">Pilih Personel</label>
                    <select name="id_anggota" class="form-select form-select-sm">
                        <option value="">Semua Personel</option>
                        @foreach($daftarAnggota as $ang)
                            <option value="{{ $ang->id_anggota }}" {{ request('id_anggota') == $ang->id_anggota ? 'selected' : '' }}>
                                {{ $ang->nama }} ({{ $ang->nrp }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1" style="background-color: #0e2238; border-color: #0e2238;">
                        <i class="bi bi-filter me-1"></i> Terapkan
                    </button>
                    <a href="{{ route('admin.surat.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Pengajuan Surat -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-dark mb-0">
            <i class="bi bi-table me-1 text-primary"></i> Daftar Pengajuan Surat Personel
        </h5>
        <span class="badge bg-light text-secondary border">Total {{ $daftarSurat->total() }} Data</span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                    <tr>
                        <th class="ps-3 text-center" style="width: 50px;">No</th>
                        <th>Personel</th>
                        <th>Jenis Keterangan</th>
                        <th>Periode Tanggal</th>
                        <th>Tanggal Diajukan</th>
                        <th class="text-center">Status</th>
                        <th>Pemroses</th>
                        <th class="text-center pe-3" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($daftarSurat as $idx => $surat)
                        <tr>
                            <td class="ps-3 text-center text-muted">
                                {{ $daftarSurat->firstItem() + $idx }}
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $surat->anggota->nama ?? '-' }}</div>
                                <div class="font-monospace text-muted" style="font-size: 0.72rem;">NRP: {{ $surat->anggota->nrp ?? '-' }}</div>
                                <div class="text-secondary" style="font-size: 0.72rem;">{{ $surat->anggota->jabatan ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary text-uppercase px-2 py-1">
                                    {{ $surat->label_jenis }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    {{ \Carbon\Carbon::parse($surat->tanggal_mulai)->translatedFormat('d M Y') }}
                                    s/d
                                    {{ \Carbon\Carbon::parse($surat->tanggal_selesai)->translatedFormat('d M Y') }}
                                </div>
                                <small class="text-muted" style="font-size: 0.72rem;">
                                    {{ \Carbon\Carbon::parse($surat->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($surat->tanggal_selesai)) + 1 }} Hari
                                </small>
                            </td>
                            <td>
                                <div>{{ \Carbon\Carbon::parse($surat->created_at)->translatedFormat('d M Y') }}</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($surat->created_at)->format('H:i') }} WITA</small>
                            </td>
                            <td class="text-center">
                                {!! $surat->status_badge !!}
                            </td>
                            <td>
                                @if($surat->admin)
                                    <div class="fw-semibold text-dark">{{ $surat->admin->nama }}</div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($surat->tanggal_diproses)->translatedFormat('d M Y, H:i') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.surat.show', $surat->id_pengajuan) }}" class="btn btn-outline-primary" title="Lihat Detail & Proses">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </a>
                                    <a href="{{ route('admin.surat.file', $surat->id_pengajuan) }}" target="_blank" class="btn btn-outline-danger" title="Buka PDF">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                Tidak ada data pengajuan surat yang ditemukan sesuai kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($daftarSurat->hasPages())
        <div class="card-footer bg-white py-3 border-top">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">
                    Menampilkan {{ $daftarSurat->firstItem() }} - {{ $daftarSurat->lastItem() }} dari total {{ $daftarSurat->total() }} data
                </small>
                <div>
                    {{ $daftarSurat->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    @endif
</div>
@endsection


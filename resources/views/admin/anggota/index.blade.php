@extends('layouts.admin')

@section('title', 'Data Anggota')

@section('content')
<!-- Page Header -->
<div class="mb-4">
    <h1 class="h1-navy">Data Anggota</h1>
    <p class="subtitle-navy mb-0">Presensi Digital Bidtik Polda Kalimantan Selatan</p>
</div>

<!-- Alert Sukses / Error -->
@if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show mb-4 border-0" role="alert" style="background-color: var(--np-success-soft); color: var(--np-success); border-left: 4px solid var(--np-success) !important; border-radius: 8px;">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('sukses') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4 border-0" role="alert" style="background-color: var(--np-danger-soft); color: var(--np-danger); border-left: 4px solid var(--np-danger) !important; border-radius: 8px;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- 3 Kartu Statistik -->
<div class="row g-3 mb-4">
    <div class="col-4">
        <a href="{{ route('admin.anggota.index') }}" class="text-decoration-none">
            <div class="np-stat-card h-100" style="{{ !request('status') ? 'border-color: var(--np-primary);' : '' }}">
                <span class="np-stat-label">Semua Personel</span>
                <span class="np-stat-value" style="color: var(--np-primary);">{{ $totalSemua }}</span>
            </div>
        </a>
    </div>
    <div class="col-4">
        <a href="{{ route('admin.anggota.index', ['status' => 'aktif']) }}" class="text-decoration-none">
            <div class="np-stat-card h-100" style="{{ request('status') === 'aktif' ? 'border-color: var(--np-success);' : '' }}">
                <span class="np-stat-label">Aktif</span>
                <span class="np-stat-value" style="color: var(--np-success);">{{ $totalAktif }}</span>
            </div>
        </a>
    </div>
    <div class="col-4">
        <a href="{{ route('admin.anggota.index', ['status' => 'nonaktif']) }}" class="text-decoration-none">
            <div class="np-stat-card h-100" style="{{ request('status') === 'nonaktif' ? 'border-color: var(--np-muted);' : '' }}">
                <span class="np-stat-label">Nonaktif</span>
                <span class="np-stat-value" style="color: var(--np-muted);">{{ $totalNonaktif }}</span>
            </div>
        </a>
    </div>
</div>

<!-- Filter Pencarian & Tambah Anggota -->
<div class="np-card mb-4 p-3">
    <form action="{{ route('admin.anggota.index') }}" method="GET">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label mb-1">Cari Nama / NRP</label>
                <input type="text" name="q" class="form-control" placeholder="Nama atau NRP" value="{{ request('q') }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-6 col-md-4 d-flex gap-2">
                <button type="submit" class="btn np-btn-primary flex-grow-1" style="height: 48px;">
                    Terapkan Filter
                </button>
                <a href="{{ route('admin.anggota.create') }}" class="btn np-btn-primary flex-shrink-0 d-inline-flex align-items-center justify-content-center" style="height: 48px;">
                    Tambah Anggota
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Daftar Anggota -->
<div class="np-card overflow-hidden">
    <!-- Desktop Table (>= 768px) -->
    <div class="table-responsive d-none d-md-block">
        <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
            <thead style="background-color: #FAFCFE; border-bottom: 1px solid var(--np-line);">
                <tr style="height: 48px;">
                    <th class="ps-4 text-uppercase fw-bold" style="font-size: 12px; color: var(--np-muted); letter-spacing: 0.5px; width: 40%;">PERSONEL / NRP</th>
                    <th class="text-uppercase fw-bold" style="font-size: 12px; color: var(--np-muted); letter-spacing: 0.5px; width: 20%;">STATUS</th>
                    <th class="text-end pe-4 text-uppercase fw-bold" style="font-size: 12px; color: var(--np-muted); letter-spacing: 0.5px; width: 40%;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($anggotaList as $anggota)
                    <tr style="height: 80px; border-bottom: 1px solid var(--np-line);">
                        <td class="ps-4 py-3">
                            <div class="fw-bold" style="color: var(--np-ink); font-size: 15px;">{{ $anggota->nama }}</div>
                            <div class="text-muted small mt-1 font-monospace">
                                {{ $anggota->nrp }} / {{ $anggota->unit_kerja ?? 'Bid TIK' }}
                            </div>
                        </td>
                        <td class="py-3">
                            @if($anggota->status === 'aktif')
                                <span class="np-badge np-badge-success">Aktif</span>
                            @else
                                <span class="np-badge np-badge-muted">Nonaktif</span>
                            @endif

                            @if(! $anggota->hasAkunLogin())
                                <span class="np-badge np-badge-warning ms-1">Belum Punya Akun Login</span>
                            @endif
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.anggota.show', $anggota->id_anggota) }}" class="btn np-btn-outline btn-sm px-3 d-inline-flex align-items-center justify-content-center" style="height: 38px;">
                                    Detail
                                </a>
                                <a href="{{ route('admin.anggota.edit', $anggota->id_anggota) }}" class="btn np-btn-outline btn-sm px-3 d-inline-flex align-items-center justify-content-center" style="height: 38px;">
                                    Edit
                                </a>
                                <button type="button" class="btn np-btn-outline btn-sm px-3 d-inline-flex align-items-center justify-content-center" style="height: 38px;" 
                                        data-bs-toggle="modal" data-bs-target="#modalResetPassword{{ $anggota->id_anggota }}">
                                    Reset Password
                                </button>
                                <button type="button" class="btn np-btn-danger btn-sm px-3 d-inline-flex align-items-center justify-content-center" style="height: 38px;"
                                        data-bs-toggle="modal" data-bs-target="#modalHapus{{ $anggota->id_anggota }}">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
                            Tidak ada data personel yang ditemukan sesuai kriteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card Reflow (< 768px) -->
    <div class="d-block d-md-none p-3">
        @forelse($anggotaList as $anggota)
            <div class="border rounded-3 p-3 mb-3 bg-white" style="border-color: var(--np-line) !important;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="fw-bold mb-1" style="color: var(--np-ink); font-size: 16px;">{{ $anggota->nama }}</h6>
                        <div class="text-muted small font-monospace">{{ $anggota->nrp }} / {{ $anggota->unit_kerja ?? 'Bid TIK' }}</div>
                    </div>
                    <div>
                        @if($anggota->status === 'aktif')
                            <span class="np-badge np-badge-success">Aktif</span>
                        @else
                            <span class="np-badge np-badge-muted">Nonaktif</span>
                        @endif
                    </div>
                </div>

                @if(! $anggota->hasAkunLogin())
                    <div class="mb-3">
                        <span class="np-badge np-badge-warning">Belum Punya Akun Login</span>
                    </div>
                @endif

                <div class="row g-2 mt-2 pt-2 border-top" style="border-color: var(--np-line) !important;">
                    <div class="col-6">
                        <a href="{{ route('admin.anggota.show', $anggota->id_anggota) }}" class="btn np-btn-outline w-100 fw-bold d-flex align-items-center justify-content-center" style="height: 42px;">
                            Detail
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('admin.anggota.edit', $anggota->id_anggota) }}" class="btn np-btn-outline w-100 fw-bold d-flex align-items-center justify-content-center" style="height: 42px;">
                            Edit
                        </a>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn np-btn-outline w-100 fw-bold d-flex align-items-center justify-content-center" style="height: 42px; font-size: 13px;"
                                data-bs-toggle="modal" data-bs-target="#modalResetPassword{{ $anggota->id_anggota }}">
                            Reset Password
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn np-btn-danger w-100 fw-bold d-flex align-items-center justify-content-center" style="height: 42px;"
                                data-bs-toggle="modal" data-bs-target="#modalHapus{{ $anggota->id_anggota }}">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
                Tidak ada data anggota yang ditemukan.
            </div>
        @endforelse
    </div>

    <!-- Paginasi -->
    @if($anggotaList->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: #FAFCFE; border-color: var(--np-line) !important;">
            <small class="text-muted">
                Menampilkan {{ $anggotaList->firstItem() }} - {{ $anggotaList->lastItem() }} dari {{ $anggotaList->total() }} anggota
            </small>
            <div>
                {{ $anggotaList->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

<!-- Modals for each anggota -->
@push('modals')
@foreach($anggotaList as $anggota)
    <!-- Modal Reset Password (Page 33) -->
    <div class="modal fade" id="modalResetPassword{{ $anggota->id_anggota }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 552px;">
            <div class="modal-content border-0" style="border-radius: var(--np-radius-card); border: 1px solid var(--np-line); box-shadow: 0 10px 25px rgba(23,38,63,0.1);">
                <form action="{{ route('admin.anggota.reset_password', $anggota->id_anggota) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-4">
                        <h2 class="h2-navy mb-1" style="font-size: 20px;">Reset Password Personel</h2>
                        <p class="subtitle-navy mb-4 font-monospace" style="font-size: 13px;">{{ $anggota->nama }} / NRP {{ $anggota->nrp }}</p>

                        @if(! $anggota->email)
                            <div class="alert alert-warning py-2 px-3 small mb-3 border-0" style="background-color: var(--np-warning-soft); color: var(--np-warning); border-radius: 8px;">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Anggota ini belum memiliki email. Silakan edit data anggota untuk menambahkan email sebelum login.
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label mb-1">Password Baru *</label>
                            <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter" required minlength="8">
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <button type="submit" class="btn np-btn-primary w-100 fw-bold" style="height: 48px;">
                                Reset Password
                            </button>
                            <button type="button" class="btn np-btn-outline w-100 fw-bold" style="height: 48px;" data-bs-dismiss="modal">
                                Batal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus / Perlindungan Riwayat (Page 34) -->
    <div class="modal fade" id="modalHapus{{ $anggota->id_anggota }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 552px;">
            <div class="modal-content border-0" style="border-radius: var(--np-radius-card); border: 1px solid var(--np-line); box-shadow: 0 10px 25px rgba(23,38,63,0.1);">
                <div class="p-4">
                    <h2 class="h2-navy mb-1" style="font-size: 20px;">Konfirmasi Hapus Anggota</h2>
                    <p class="subtitle-navy mb-4 font-monospace" style="font-size: 13px;">{{ $anggota->nama }} / NRP {{ $anggota->nrp }}</p>

                    @if($anggota->presensi_count > 0)
                        <!-- Perlindungan Riwayat Presensi (Page 34) -->
                        <div class="p-3 mb-4 rounded-2" style="background-color: var(--np-warning-soft); border: 1px solid var(--np-warning-line);">
                            <div class="fw-bold mb-1" style="color: var(--np-warning); font-size: 14px;">Data Presensi Tetap Dilindungi</div>
                            <div class="small" style="color: var(--np-ink); line-height: 1.4;">
                                Anggota dengan riwayat presensi tidak dapat dihapus. Ubah status menjadi Nonaktif.
                            </div>
                        </div>

                        <button type="button" class="btn np-btn-outline w-100 fw-bold" style="height: 48px;" data-bs-dismiss="modal">
                            Tutup
                        </button>
                    @else
                        <!-- Diizinkan Hapus -->
                        <div class="p-3 mb-4 rounded-2" style="background-color: var(--np-danger-soft); border: 1px solid var(--np-danger-line);">
                            <div class="small" style="color: var(--np-danger); line-height: 1.4;">
                                Tindakan ini tidak dapat dibatalkan. Personel belum memiliki data presensi dan akan dihapus permanen.
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <form action="{{ route('admin.anggota.destroy', $anggota->id_anggota) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn np-btn-danger w-100 fw-bold" style="height: 48px;">
                                    Ya, Hapus Anggota
                                </button>
                            </form>
                            <button type="button" class="btn np-btn-outline w-100 fw-bold" style="height: 48px;" data-bs-dismiss="modal">
                                Batal
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach
@endpush
@endsection

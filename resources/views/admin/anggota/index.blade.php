@extends('layouts.admin')

@section('title', 'Data Anggota')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold text-dark mb-0">Kelola Data Anggota</h3>
        <p class="text-muted small mb-0">
            Daftar seluruh personel Bidtik Polda Kalimantan Selatan yang terdaftar dalam sistem presensi.
        </p>
    </div>
    <div>
        <a href="{{ route('admin.anggota.create') }}" class="btn btn-primary btn-sm" style="background-color: #0e2238; border-color: #0e2238;">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Anggota Baru
        </a>
    </div>
</div>

<!-- Kartu Ringkasan Anggota (Bisa Diklik untuk Filter Cepat) -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <a href="{{ route('admin.anggota.index') }}" 
           class="card card-stat bg-white p-3 border-start border-4 border-dark text-decoration-none {{ !request('status') ? 'active-filter' : '' }}"
           title="Klik untuk melihat semua anggota">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted text-uppercase fw-semibold">Total Personel</small>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalSemua }}</h3>
                    <small class="text-muted">Semua status <i class="bi bi-arrow-right-short text-dark"></i></small>
                </div>
                <div class="p-3 bg-dark bg-opacity-10 rounded-circle text-dark fs-4">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-4">
        <a href="{{ route('admin.anggota.index', ['status' => 'aktif']) }}" 
           class="card card-stat bg-white p-3 border-start border-4 border-success text-decoration-none {{ request('status') === 'aktif' ? 'active-filter' : '' }}"
           title="Klik untuk filter hanya anggota aktif">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted text-uppercase fw-semibold">Anggota Aktif</small>
                    <h3 class="fw-bold text-success mb-0 mt-1">{{ $totalAktif }}</h3>
                    <small class="text-muted">Dapat presensi <i class="bi bi-arrow-right-short text-success"></i></small>
                </div>
                <div class="p-3 bg-success bg-opacity-10 rounded-circle text-success fs-4">
                    <i class="bi bi-person-check-fill"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-4">
        <a href="{{ route('admin.anggota.index', ['status' => 'nonaktif']) }}" 
           class="card card-stat bg-white p-3 border-start border-4 border-secondary text-decoration-none {{ request('status') === 'nonaktif' ? 'active-filter' : '' }}"
           title="Klik untuk filter anggota nonaktif">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted text-uppercase fw-semibold">Nonaktif</small>
                    <h3 class="fw-bold text-secondary mb-0 mt-1">{{ $totalNonaktif }}</h3>
                    <small class="text-muted">Dinonaktifkan <i class="bi bi-arrow-right-short text-secondary"></i></small>
                </div>
                <div class="p-3 bg-secondary bg-opacity-10 rounded-circle text-secondary fs-4">
                    <i class="bi bi-person-x-fill"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Card Filter & Pencarian -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.anggota.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" 
                           placeholder="Cari berdasarkan NRP, Nama, Jabatan, Satuan..." 
                           value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-4 d-flex gap-2">
                <button type="submit" class="btn btn-secondary btn-sm px-3">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                @if(request('q') || request('status'))
                    <a href="{{ route('admin.anggota.index') }}" class="btn btn-outline-secondary btn-sm">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Card Tabel Anggota -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 60px;">No</th>
                        <th>NRP / Identitas</th>
                        <th>Jabatan & Satuan</th>
                        <th>Kontak Telepon</th>
                        <th class="text-center">Riwayat Presensi</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($anggotaList as $index => $anggota)
                        <tr>
                            <td class="ps-3 text-muted">
                                {{ $anggotaList->firstItem() + $index }}
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $anggota->nama }}</div>
                                <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
                                    <span class="badge bg-light text-secondary border font-monospace">NRP: {{ $anggota->nrp }}</span>
                                    @if($anggota->hasAkunLogin())
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" title="Email: {{ $anggota->email }}">
                                            <i class="bi bi-envelope-check me-1"></i>{{ $anggota->email }}
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Belum punya akun login
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-secondary">{{ $anggota->jabatan }}</div>
                                <small class="text-muted"><i class="bi bi-building me-1"></i>{{ $anggota->unit_kerja }}</small>
                            </td>
                            <td>
                                @if($anggota->no_telepon)
                                    <span class="small"><i class="bi bi-telephone text-muted me-1"></i>{{ $anggota->no_telepon }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($anggota->presensi_count > 0)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        {{ $anggota->presensi_count }} presensi
                                    </span>
                                @else
                                    <span class="text-muted small">Belum ada</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($anggota->status === 'aktif')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle me-1"></i> Aktif
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                        <i class="bi bi-dash-circle me-1"></i> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.anggota.show', $anggota->id_anggota) }}" 
                                       class="btn btn-outline-info" title="Lihat Profil & Riwayat">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.anggota.edit', $anggota->id_anggota) }}" 
                                       class="btn btn-outline-warning" title="Edit Data">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#resetPasswordModal{{ $anggota->id_anggota }}" 
                                            title="Reset Password Akun">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal{{ $anggota->id_anggota }}" 
                                            title="Hapus Anggota">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                                <!-- Modal Reset Password -->
                                <div class="modal fade" id="resetPasswordModal{{ $anggota->id_anggota }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <form action="{{ route('admin.anggota.reset_password', $anggota->id_anggota) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header bg-primary text-white" style="background-color: #0e2238 !important;">
                                                    <h5 class="modal-title fs-6">
                                                        <i class="bi bi-key-fill me-1 text-warning"></i> Reset Password Personel
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-start p-4">
                                                    <p class="mb-2">Masukkan password baru untuk akun personel berikut:</p>
                                                    <div class="p-3 bg-light rounded border mb-3">
                                                        <div class="fw-bold">{{ $anggota->nama }}</div>
                                                        <small class="text-muted">NRP: {{ $anggota->nrp }} | Email: {{ $anggota->email ?? 'Belum ada email' }}</small>
                                                    </div>

                                                    @if(! $anggota->email)
                                                        <div class="alert alert-warning py-2 px-3 small mb-3">
                                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                                            Anggota ini belum memiliki email. Silakan edit data anggota terlebih dahulu untuk menambahkan email sebelum login.
                                                        </div>
                                                    @endif

                                                    <div class="mb-2">
                                                        <label class="form-label small fw-semibold text-dark">Password Baru (Min. 8 karakter) <span class="text-danger">*</span></label>
                                                        <input type="password" name="password" class="form-control" placeholder="Contoh: password123" required minlength="8">
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light py-2">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary btn-sm" style="background-color: #0e2238; border-color: #0e2238;">
                                                        <i class="bi bi-check-circle me-1"></i> Simpan Password Baru
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Konfirmasi Hapus -->
                                <div class="modal fade" id="deleteModal{{ $anggota->id_anggota }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title fs-6">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Konfirmasi Hapus Anggota
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start p-4">
                                                <p class="mb-2">Apakah Anda yakin ingin menghapus anggota berikut?</p>
                                                <div class="p-3 bg-light rounded border mb-3">
                                                    <div class="fw-bold">{{ $anggota->nama }}</div>
                                                    <small class="text-muted">NRP: {{ $anggota->nrp }} | {{ $anggota->jabatan }}</small>
                                                </div>

                                                @if($anggota->presensi_count > 0)
                                                    <div class="alert alert-warning py-2 px-3 small mb-0">
                                                        <i class="bi bi-shield-exclamation me-1"></i>
                                                        <strong>Perhatian:</strong> Anggota ini memiliki <strong>{{ $anggota->presensi_count }} data presensi</strong>. Sistem akan menolak penghapusan untuk menjaga integritas data absensi. Disarankan ubah status menjadi Nonaktif.
                                                    </div>
                                                @else
                                                    <p class="text-danger small mb-0">
                                                        <i class="bi bi-info-circle me-1"></i> Tindakan ini tidak dapat dibatalkan.
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="modal-footer bg-light py-2">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('admin.anggota.destroy', $anggota->id_anggota) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        Ya, Hapus Data
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                Tidak ada data anggota yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($anggotaList->hasPages())
        <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Menampilkan {{ $anggotaList->firstItem() }} - {{ $anggotaList->lastItem() }} dari {{ $anggotaList->total() }} anggota
            </small>
            <div>
                {{ $anggotaList->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>
@endsection

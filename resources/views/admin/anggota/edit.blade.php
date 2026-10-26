@extends('layouts.admin')

@section('title', 'Edit Data Anggota')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-0">Edit Data Anggota</h3>
                <p class="text-muted small mb-0">
                    Perbarui informasi data personel: <strong>{{ $anggota->nama }}</strong>
                </p>
            </div>
            <a href="{{ route('admin.anggota.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-person-gear me-1 text-primary"></i> Formulir Perubahan Data
                </h5>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('admin.anggota.update', $anggota->id_anggota) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="nrp" class="form-label fw-semibold text-secondary small">
                                Nomor Registrasi Pokok (NRP) <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control font-monospace @error('nrp') is-invalid @enderror" 
                                   id="nrp" name="nrp" value="{{ old('nrp', $anggota->nrp) }}" 
                                   placeholder="Contoh: 80010006" required>
                            <small class="text-muted" style="font-size: 0.75rem;">Perubahan NRP akan memengaruhi identitas saat presensi.</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="status" class="form-label fw-semibold text-secondary small">
                                Status Keaktifan <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="aktif" {{ old('status', $anggota->status) === 'aktif' ? 'selected' : '' }}>Aktif (Dapat Presensi)</option>
                                <option value="nonaktif" {{ old('status', $anggota->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif (Tidak Dapat Presensi)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label fw-semibold text-secondary small">
                            Nama Lengkap & Gelar <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" 
                               id="nama" name="nama" value="{{ old('nama', $anggota->nama) }}" 
                               placeholder="Contoh: Bripka Muhammad Ilham, S.Kom." required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="jabatan" class="form-label fw-semibold text-secondary small">
                                Pangkat / Jabatan <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('jabatan') is-invalid @enderror" 
                                   id="jabatan" name="jabatan" value="{{ old('jabatan', $anggota->jabatan) }}" 
                                   placeholder="Contoh: Bintara Tekinfo / Banum" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="unit_kerja" class="form-label fw-semibold text-secondary small">
                                Unit Kerja / Satuan <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('unit_kerja') is-invalid @enderror" 
                                   id="unit_kerja" name="unit_kerja" value="{{ old('unit_kerja', $anggota->unit_kerja) }}" 
                                   placeholder="Contoh: Subbid Tekkom / Bidtik" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="no_telepon" class="form-label fw-semibold text-secondary small">
                            Nomor Telepon / WhatsApp (Opsional)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-phone"></i></span>
                            <input type="text" class="form-control @error('no_telepon') is-invalid @enderror" 
                                   id="no_telepon" name="no_telepon" value="{{ old('no_telepon', $anggota->no_telepon) }}" 
                                   placeholder="Contoh: 081234567890">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <a href="{{ route('admin.anggota.show', $anggota->id_anggota) }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-eye me-1"></i> Lihat Detail Profil
                        </a>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.anggota.index') }}" class="btn btn-light border px-4">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm" style="background-color: #0e2238; border-color: #0e2238;">
                                <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

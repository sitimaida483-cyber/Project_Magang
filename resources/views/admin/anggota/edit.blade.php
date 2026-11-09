@extends('layouts.admin')

@section('title', 'Edit Data Anggota')

@section('content')
<!-- Page Header -->
<div class="mb-4">
    <h1 class="h1-navy">Edit Data Anggota</h1>
    <p class="subtitle-navy mb-0">Presensi Digital Bidtik Polda Kalimantan Selatan</p>
</div>

<!-- Alert Error Validasi -->
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4 border-0" role="alert" style="background-color: var(--np-danger-soft); color: var(--np-danger); border-left: 4px solid var(--np-danger) !important; border-radius: 8px;">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="np-card p-4">
    <form action="{{ route('admin.anggota.update', $anggota->id_anggota) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Kolom 1 -->
            <div class="col-12 col-md-6">
                <!-- NRP -->
                <div class="mb-3">
                    <label for="nrp" class="form-label mb-1">NRP *</label>
                    <input type="text" class="form-control font-monospace @error('nrp') is-invalid @enderror" 
                           id="nrp" name="nrp" value="{{ old('nrp', $anggota->nrp) }}" 
                           placeholder="000001" required>
                </div>

                <!-- Nama Lengkap -->
                <div class="mb-3">
                    <label for="nama" class="form-label mb-1">Nama Lengkap *</label>
                    <input type="text" class="form-control @error('nama') is-invalid @enderror" 
                           id="nama" name="nama" value="{{ old('nama', $anggota->nama) }}" 
                           placeholder="Ahmad Pratama" required>
                </div>

                <!-- Password (Opsional) -->
                <div class="mb-3">
                    <label for="password" class="form-label mb-1">Password (Opsional)</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" placeholder="Minimal 8 karakter" minlength="8">
                    <small class="text-muted" style="font-size: 12px;">Kosongkan jika tidak ingin mengganti password.</small>
                </div>

                <!-- Unit Kerja -->
                <div class="mb-3">
                    <label for="unit_kerja" class="form-label mb-1">Unit Kerja *</label>
                    <input type="text" class="form-control @error('unit_kerja') is-invalid @enderror" 
                           id="unit_kerja" name="unit_kerja" value="{{ old('unit_kerja', $anggota->unit_kerja) }}" 
                           placeholder="Bid TIK" required>
                </div>
            </div>

            <!-- Kolom 2 -->
            <div class="col-12 col-md-6">
                <!-- Status -->
                <div class="mb-3">
                    <label for="status" class="form-label mb-1">Status *</label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="aktif" {{ old('status', $anggota->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status', $anggota->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <!-- Email Kedinasan -->
                <div class="mb-3">
                    <label for="email" class="form-label mb-1">Email Kedinasan *</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email', $anggota->email) }}" 
                           placeholder="ahmad@contoh.test" required>
                </div>

                <!-- Jabatan -->
                <div class="mb-3">
                    <label for="jabatan" class="form-label mb-1">Jabatan *</label>
                    <input type="text" class="form-control @error('jabatan') is-invalid @enderror" 
                           id="jabatan" name="jabatan" value="{{ old('jabatan', $anggota->jabatan) }}" 
                           placeholder="Personel" required>
                </div>

                <!-- Nomor Telepon -->
                <div class="mb-3">
                    <label for="no_telepon" class="form-label mb-1">Nomor Telepon (Opsional)</label>
                    <input type="text" class="form-control @error('no_telepon') is-invalid @enderror" 
                           id="no_telepon" name="no_telepon" value="{{ old('no_telepon', $anggota->no_telepon) }}" 
                           placeholder="08xxxxxxxxxx">
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="d-flex gap-2 mt-4 pt-3 border-top" style="border-color: var(--np-line) !important;">
            <button type="submit" class="btn np-btn-primary fw-bold" style="height: 48px; min-width: 160px;">
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.anggota.index') }}" class="btn np-btn-outline fw-bold d-inline-flex align-items-center justify-content-center" style="height: 48px; min-width: 100px;">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection

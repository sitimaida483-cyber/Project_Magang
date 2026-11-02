@extends('layouts.anggota')

@section('title', 'Ubah Password Akun')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm p-4 rounded-3 bg-white mb-4">
    <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-key-fill text-warning me-2"></i>Ubah Password Akun
            </h5>
            <small class="text-muted">Perbarui kata sandi akun presensi Anda secara mandiri.</small>
        </div>
        <a href="{{ route('presensi.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <!-- Alert Status -->
    @if(session('sukses'))
        <div class="alert alert-success py-2 px-3 small d-flex align-items-center mb-3 rounded-3" role="alert">
            <i class="bi bi-check-circle-fill flex-shrink-0 me-2 fs-5"></i>
            <div>{{ session('sukses') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger py-2 px-3 small d-flex align-items-center mb-3 rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2 fs-5"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger py-2 px-3 small mb-3 rounded-3">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Informasi Akun -->
    <div class="p-3 bg-light rounded-3 border mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 fs-4">
                <i class="bi bi-person-badge"></i>
            </div>
            <div>
                <div class="fw-bold text-dark">{{ $anggota->nama }}</div>
                <div class="small text-muted">NRP: {{ $anggota->nrp }} | {{ $anggota->jabatan }}</div>
                <div class="small text-secondary">{{ $anggota->email }}</div>
            </div>
        </div>
    </div>

    <!-- Form Ubah Password -->
    <form action="{{ route('anggota.password.update') }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Password Lama -->
        <div class="mb-3">
            <label for="password_lama" class="form-label small fw-semibold text-dark mb-1">
                Password Saat Ini <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="password_lama" name="password_lama" 
                       placeholder="Masukkan password saat ini" required autocomplete="current-password">
            </div>
        </div>

        <!-- Password Baru -->
        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold text-dark mb-1">
                Password Baru (Minimal 8 karakter) <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder="Masukkan password baru" required minlength="8" autocomplete="new-password">
            </div>
        </div>

        <!-- Konfirmasi Password Baru -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label small fw-semibold text-dark mb-1">
                Ulangi Password Baru <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-check2-circle"></i></span>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" 
                       placeholder="Ketik ulang password baru" required minlength="8" autocomplete="new-password">
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" style="background-color: #0e2238; border-color: #0e2238;">
            <i class="bi bi-save me-1"></i> Simpan Password Baru
        </button>
    </form>
        </div>
    </div>
</div>
@endsection


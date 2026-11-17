@extends('layouts.admin')

@section('title', 'Ubah Password Admin')

@section('content')
<div class="mb-4">
    <h1 class="h1-navy">Ubah Password Administrator</h1>
    <div class="subtitle-navy">Presensi Digital Bidtik Polda Kalimantan Selatan</div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Informasi Akun Admin & Panduan Keamanan -->
    <div class="col-12 col-lg-5 col-xl-4">
        <!-- Kartu Identitas Admin -->
        <div class="card np-card mb-3">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 52px; height: 52px; background-color: var(--np-soft); color: var(--np-primary); font-size: 26px; border: 1px solid #C7D7FE;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="color: var(--np-ink); font-size: 16px;">{{ $admin->nama ?? auth('admin')->user()->nama }}</div>
                        <div class="text-muted small">Username: {{ $admin->username ?? auth('admin')->user()->username }}</div>
                    </div>
                </div>
                <div class="pt-3 border-top d-flex align-items-center justify-content-between" style="border-color: var(--np-line) !important;">
                    <span class="small text-muted">Peran Sistem</span>
                    <span class="badge badge-np-primary">Administrator Utama</span>
                </div>
            </div>
        </div>

        <!-- Panduan Kebijakan Kata Sandi -->
        <div class="card np-card mb-3">
            <div class="card-header border-0 pb-0 pt-3 px-4">
                <span class="fw-bold text-dark fs-6"><i class="bi bi-shield-check me-2 text-primary"></i>Kebijakan Kata Sandi</span>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-0 d-flex flex-column gap-3 small text-muted">
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-check-circle-fill text-primary mt-1 flex-shrink-0"></i>
                        <span>Panjang kata sandi minimal <strong>10 karakter</strong>.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-check-circle-fill text-primary mt-1 flex-shrink-0"></i>
                        <span>Wajib mengombinasikan <strong>huruf besar</strong>, <strong>huruf kecil</strong>, dan <strong>angka</strong>.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-check-circle-fill text-primary mt-1 flex-shrink-0"></i>
                        <span>Sistem otomatis menolak password yang terdeteksi pernah mengalami kebocoran data publik.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-check-circle-fill text-primary mt-1 flex-shrink-0"></i>
                        <span>Setiap pergantian password admin dicatat ke dalam log aktivitas sistem demi jejak audit keamanan.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Formulir Pembaruan Kata Sandi -->
    <div class="col-12 col-lg-7 col-xl-8">
        <div class="card np-card">
            <div class="card-header border-0 pb-0 pt-3 px-4">
                <span class="fw-bold text-dark fs-6">Formulir Pembaruan Kata Sandi Administrator</span>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4" role="alert" style="background-color: #FEE2E2; color: #DC2626; border: 1px solid #FECACA !important;">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Mohon periksa kesalahan input:</div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('admin.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Password Saat Ini -->
                    <div class="mb-3">
                        <label for="password_lama" class="form-label">
                            Password Saat Ini <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control @error('password_lama') is-invalid @enderror" 
                               id="password_lama" name="password_lama" placeholder="Masukkan kata sandi lama Anda" required autocomplete="current-password">
                        @error('password_lama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Password Baru -->
                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label">
                                Password Baru <span class="text-danger">*</span>
                            </label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Minimal 10 karakter campuran" required minlength="10" autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Konfirmasi Password Baru -->
                        <div class="col-12 col-md-6">
                            <label for="password_confirmation" class="form-label">
                                Konfirmasi Password Baru <span class="text-danger">*</span>
                            </label>
                            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru" required minlength="10" autocomplete="new-password">
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-np-primary d-inline-flex align-items-center gap-2">
                            <i class="bi bi-shield-check"></i>
                            <span>Simpan Password Baru</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


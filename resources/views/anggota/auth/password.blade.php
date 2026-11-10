@extends('layouts.anggota')

@section('title', 'Ubah Password')

@section('content')
<div class="mb-4">
    <h1 class="h1-navy">Ubah Password</h1>
    <div class="subtitle-navy">Presensi Digital Bidtik Polda Kalimantan Selatan</div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Informasi Akun Personel & Panduan Keamanan -->
    <div class="col-12 col-lg-5 col-xl-4">
        <!-- Kartu Identitas Personel -->
        <div class="card np-card mb-3">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 52px; height: 52px; background-color: var(--np-soft); color: var(--np-primary); font-size: 26px; border: 1px solid #C7D7FE;">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="color: var(--np-ink); font-size: 16px;">{{ $anggota->nama ?? auth('anggota')->user()->nama }}</div>
                        <div class="text-muted small">NRP {{ $anggota->nrp ?? auth('anggota')->user()->nrp }} / {{ $anggota->unit_kerja ?? 'Bid TIK' }}</div>
                    </div>
                </div>
                <div class="pt-3 border-top d-flex align-items-center justify-content-between" style="border-color: var(--np-line) !important;">
                    <span class="small text-muted">Status Akun Personel</span>
                    <span class="badge badge-np-success">Aktif</span>
                </div>
            </div>
        </div>

        <!-- Panduan Keamanan Password -->
        <div class="card np-card mb-3">
            <div class="card-header border-0 pb-0 pt-3 px-4">
                <span class="fw-bold text-dark fs-6"><i class="bi bi-shield-check me-2 text-primary"></i>Panduan Keamanan Akun</span>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-0 d-flex flex-column gap-3 small text-muted">
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-check-circle-fill text-primary mt-1 flex-shrink-0"></i>
                        <span>Panjang kata sandi minimal <strong>8 karakter</strong>.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-check-circle-fill text-primary mt-1 flex-shrink-0"></i>
                        <span>Kombinasikan huruf kapital, huruf kecil, angka, dan simbol untuk keamanan maksimal.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-check-circle-fill text-primary mt-1 flex-shrink-0"></i>
                        <span>Hindari kata sandi yang mudah ditebak seperti tanggal lahir atau nomor NRP.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-check-circle-fill text-primary mt-1 flex-shrink-0"></i>
                        <span>Demi keamanan data kedinasan, jangan bagikan kata sandi Anda kepada orang lain.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Formulir Ubah Password -->
    <div class="col-12 col-lg-7 col-xl-8">
        <div class="card np-card">
            <div class="card-header border-0 pb-0 pt-3 px-4">
                <span class="fw-bold text-dark fs-6">Formulir Pembaruan Kata Sandi</span>
            </div>
            <div class="card-body p-4">
                <!-- Alert Status -->
                @if(session('sukses'))
                    <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-4" role="alert" style="background-color: #DCFCE7; color: #15803D; border: 1px solid #BBF7D0 !important;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('sukses') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4" role="alert" style="background-color: #FEE2E2; color: #DC2626; border: 1px solid #FECACA !important;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

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

                <!-- Form Ubah Password -->
                <form action="{{ route('anggota.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Password Saat Ini -->
                    <div class="mb-3">
                        <label for="password_lama" class="form-label">
                            Password Saat Ini *
                        </label>
                        <input type="password" class="form-control @error('password_lama') is-invalid @enderror" 
                               id="password_lama" name="password_lama" placeholder="Masukkan password lama Anda" required autocomplete="current-password">
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Password Baru -->
                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label">
                                Password Baru *
                            </label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Minimal 8 karakter" required minlength="8" autocomplete="new-password">
                        </div>

                        <!-- Konfirmasi Password Baru -->
                        <div class="col-12 col-md-6">
                            <label for="password_confirmation" class="form-label">
                                Konfirmasi Password Baru *
                            </label>
                            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru" required minlength="8" autocomplete="new-password">
                        </div>
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary fw-bold px-4" style="min-height: 48px; border-radius: 10px;">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Password
                        </button>
                        <a href="{{ route('presensi.index') }}" class="btn btn-outline-secondary fw-bold px-4" style="min-height: 48px; border-radius: 10px;">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

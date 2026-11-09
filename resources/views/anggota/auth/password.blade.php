@extends('layouts.anggota')

@section('title', 'Ubah Password')

@section('content')
<div class="mb-4">
    <h1 class="h1-navy">Ubah Password</h1>
    <div class="subtitle-navy">Presensi Digital Bidtik Polda Kalimantan Selatan</div>
</div>

<div class="row">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card np-card">
            <div class="card-body p-4">
                <!-- Banner Keamanan Akun -->
                <div class="p-3 mb-4 rounded-2" style="background-color: var(--np-soft); border: 1px solid var(--np-line);">
                    <div class="fw-bold text-dark mb-1">Keamanan Akun</div>
                    <div class="text-muted small">Gunakan password baru minimal 8 karakter.</div>
                </div>

                <!-- Alert Status -->
                @if(session('sukses'))
                    <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-3" role="alert" style="background-color: #DCFCE7; color: #15803D; border: 1px solid #BBF7D0 !important;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('sukses') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-3" role="alert" style="background-color: #FEE2E2; color: #DC2626; border: 1px solid #FECACA !important;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-3" role="alert" style="background-color: #FEE2E2; color: #DC2626; border: 1px solid #FECACA !important;">
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
                               id="password_lama" name="password_lama" required autocomplete="current-password">
                    </div>

                    <!-- Password Baru -->
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Password Baru *
                        </label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required minlength="8" autocomplete="new-password">
                    </div>

                    <!-- Konfirmasi Password Baru -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">
                            Konfirmasi Password Baru *
                        </label>
                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                               id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password">
                    </div>

                    <!-- Tombol Simpan -->
                    <button type="submit" class="btn btn-primary w-100 fw-bold" style="min-height: 48px; border-radius: 10px;">
                        Simpan Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

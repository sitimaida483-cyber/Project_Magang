<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Masuk Personel - PresTIK Bidtik Polda Kalsel</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-bidtik.svg') }}?v={{ file_exists(public_path('images/logo-bidtik.svg')) ? filemtime(public_path('images/logo-bidtik.svg')) : time() }}">
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Redesign CSS (Navy Precision) -->
    <link rel="stylesheet" href="{{ asset('css/navy-precision.css') }}?v={{ file_exists(public_path('css/navy-precision.css')) ? filemtime(public_path('css/navy-precision.css')) : time() }}">
</head>
<body data-design="A" class="design-navy-precision">

    <div class="auth-split-wrapper">
        <!-- Panel Kiri: Showcase Navy Precision -->
        <div class="auth-split-showcase">
            <div>
                <!-- Brand Header -->
                <div class="d-flex align-items-center gap-3 mb-5">
                    <img src="{{ asset('images/logo-bidtik.svg') }}?v={{ file_exists(public_path('images/logo-bidtik.svg')) ? filemtime(public_path('images/logo-bidtik.svg')) : time() }}" 
                         alt="Logo PresTIK" style="width: 44px; height: 44px; object-fit: contain;">
                    <div>
                        <div class="fw-bold fs-5 text-white lh-1">PresTIK</div>
                        <div class="text-white-50 small fw-bold mt-1" style="font-size: 10px; letter-spacing: 0.8px;">BIDTIK POLDA KALSEL</div>
                    </div>
                </div>

                <!-- Headline -->
                <h1 class="text-white fw-bold mb-3" style="font-size: 34px; line-height: 1.25; letter-spacing: -0.5px;">
                    Hadir dengan pasti.<br>Tercatat dengan rapi.
                </h1>
                <p class="text-white-50" style="font-size: 15px; line-height: 1.6; max-width: 380px;">
                    Presensi lokasi dan layanan surat personel dalam satu portal.
                </p>
            </div>

            <!-- Feature Points List -->
            <div class="d-flex flex-column gap-3 pt-4 border-top border-white border-opacity-10">
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-bold fs-6 text-white" style="font-family: monospace;">01</span>
                    <span class="text-white text-opacity-80 small fw-semibold">Lokasi GPS kantor</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-bold fs-6 text-white" style="font-family: monospace;">02</span>
                    <span class="text-white text-opacity-80 small fw-semibold">Selfie kamera depan</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-bold fs-6 text-white" style="font-family: monospace;">03</span>
                    <span class="text-white text-opacity-80 small fw-semibold">Status presensi harian</span>
                </div>
            </div>
        </div>

        <!-- Panel Kanan: Form Login Personel -->
        <div class="auth-split-form-panel">
            <div class="auth-form-card">
                <!-- Mobile Brand Header (< 992px) -->
                <div class="d-flex align-items-center gap-2 mb-4 d-lg-none">
                    <img src="{{ asset('images/logo-bidtik.svg') }}?v={{ file_exists(public_path('images/logo-bidtik.svg')) ? filemtime(public_path('images/logo-bidtik.svg')) : time() }}" 
                         alt="Logo PresTIK" style="width: 36px; height: 36px; object-fit: contain;">
                    <div>
                        <div class="fw-bold text-dark lh-1">PresTIK</div>
                        <div class="text-muted" style="font-size: 10px; font-weight: 700;">BIDTIK POLDA KALSEL</div>
                    </div>
                </div>

                <h2 class="h1-navy fs-4 fw-bold mb-1">Masuk Personel</h2>
                <p class="subtitle-navy mb-4">Gunakan akun yang telah terdaftar di PresTIK.</p>

                <!-- Alerts -->
                @if(session('error'))
                    <div class="alert alert-danger py-2 px-3 small d-flex align-items-center mb-3 rounded-3" style="background-color: #FEE2E2; color: #DC2626; border: 1px solid #FECACA;" role="alert">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif
                @if(session('sukses'))
                    <div class="alert alert-success py-2 px-3 small d-flex align-items-center mb-3 rounded-3" style="background-color: #DCFCE7; color: #15803D; border: 1px solid #BBF7D0;" role="alert">
                        <i class="bi bi-check-circle-fill flex-shrink-0 me-2"></i>
                        <div>{{ session('sukses') }}</div>
                    </div>
                @endif
                @if(session('info'))
                    <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-3 rounded-3" style="background-color: #EAF0FF; color: #254BD6; border: 1px solid #C7D7FE;" role="alert">
                        <i class="bi bi-info-circle-fill flex-shrink-0 me-2"></i>
                        <div>{{ session('info') }}</div>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger py-2 px-3 small mb-3 rounded-3" style="background-color: #FEE2E2; color: #DC2626; border: 1px solid #FECACA;">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Form Login -->
                <form action="{{ route('login.proses') }}" method="POST" autocomplete="on">
                    @csrf

                    <!-- Email Kedinasan -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Kedinasan *</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" 
                               placeholder="nama@contoh.test" required autofocus autocomplete="email">
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <div class="position-relative">
                            <input type="password" class="form-control pe-5 @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Masukkan password" 
                                   required autocomplete="current-password">
                            <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none me-2" 
                                    id="btnTogglePassword" title="Lihat/Sembunyikan password" style="z-index: 5;">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Ingat Saya -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Ingat saya
                            </label>
                        </div>
                    </div>

                    <!-- Tombol Masuk -->
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        Masuk ke PresTIK
                    </button>
                </form>

                <!-- Link Login Admin -->
                <div class="mt-3 text-start">
                    <a href="{{ route('admin.login') }}" class="fw-semibold text-decoration-none" style="color: var(--np-primary); font-size: 14px;">
                        Login Administrator
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Password JS -->
    <script>
        const btnTogglePassword = document.getElementById('btnTogglePassword');
        const passwordField = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (btnTogglePassword && passwordField && toggleIcon) {
            btnTogglePassword.addEventListener('click', function () {
                const isPassword = passwordField.getAttribute('type') === 'password';
                passwordField.setAttribute('type', isPassword ? 'text' : 'password');
                toggleIcon.classList.toggle('bi-eye', !isPassword);
                toggleIcon.classList.toggle('bi-eye-slash', isPassword);
            });
        }
    </script>
</body>
</html>

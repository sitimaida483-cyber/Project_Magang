<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Masuk Personel - PresTIK Bidtik Polda Kalsel</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-bidtik.svg') }}?v={{ file_exists(public_path('images/logo-bidtik.svg')) ? filemtime(public_path('images/logo-bidtik.svg')) : time() }}">
    
    <!-- Google Fonts & Bootstrap 5 & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Redesign CSS (Navy Precision) -->
    <link rel="stylesheet" href="{{ asset('css/navy-precision.css') }}?v={{ file_exists(public_path('css/navy-precision.css')) ? filemtime(public_path('css/navy-precision.css')) : time() }}">

    <style>
        body.auth-page-body {
            margin: 0;
            padding: 30px 16px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: 
                linear-gradient(180deg, rgba(8, 20, 36, 0.72) 0%, rgba(11, 26, 46, 0.40) 240px, rgba(11, 26, 46, 0.76) 100%),
                url('{{ asset("images/bg-polda-kalsel.jpg") }}') no-repeat center top;
            background-size: cover;
            background-attachment: fixed;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 960px;
            margin: auto;
        }

        .auth-card-dual {
            display: flex;
            background: #FFFFFF;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 65px rgba(0, 0, 0, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        /* Panel Kiri: Showcase Navy Precision (Sesuai Acuan Gambar User) */
        .auth-showcase-panel {
            width: 45%;
            background-color: #0B132B;
            color: #FFFFFF;
            padding: 50px 42px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-shrink: 0;
            position: relative;
        }

        .auth-showcase-panel .brand-logo {
            width: 44px;
            height: 44px;
            object-fit: contain;
        }

        .auth-showcase-panel h1 {
            font-size: 30px;
            font-weight: 700;
            line-height: 1.3;
            letter-spacing: -0.5px;
            color: #FFFFFF;
        }

        .auth-showcase-panel p {
            font-size: 14.5px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.75);
        }

        .showcase-features {
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            padding-top: 26px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .showcase-features .feature-item {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .showcase-features .feature-num {
            font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
            font-size: 15px;
            font-weight: 700;
            color: #FFFFFF;
        }

        .showcase-features .feature-text {
            font-size: 14px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Panel Kanan: Form Card */
        .auth-form-panel {
            width: 55%;
            padding: 46px 40px;
            background: #FFFFFF;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-form-panel .form-title {
            font-size: 24px;
            font-weight: 700;
            color: #0B132B;
            margin-bottom: 4px;
        }

        .auth-form-panel .form-subtitle {
            font-size: 14px;
            color: #64748B;
            margin-bottom: 24px;
        }

        .auth-form-panel .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .auth-form-panel .form-control {
            border: 1.5px solid #CBD5E1;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            color: #0F172A;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .auth-form-panel .form-control:focus {
            border-color: #254BD6;
            box-shadow: 0 0 0 3px rgba(37, 75, 214, 0.15);
        }

        .btn-toggle-pwd {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #94A3B8;
            padding: 4px;
            font-size: 16px;
            line-height: 1;
            cursor: pointer;
        }

        .btn-toggle-pwd:hover {
            color: #475569;
        }

        .btn-navy-submit {
            background-color: #254BD6;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            padding: 11px 16px;
            font-weight: 600;
            font-size: 15px;
            width: 100%;
            transition: background-color 0.2s ease, transform 0.15s ease;
        }

        .btn-navy-submit:hover {
            background-color: #1E3EA8;
            color: #FFFFFF;
        }

        .btn-navy-submit:active {
            transform: scale(0.99);
        }

        .auth-link {
            color: #254BD6;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .auth-link:hover {
            text-decoration: underline;
            color: #1E3EA8;
        }

        @media (max-width: 991.98px) {
            body.auth-page-body {
                padding: 24px 14px;
            }
            .auth-wrapper {
                max-width: 440px;
            }
            .auth-card-dual {
                flex-direction: column;
                border-radius: 16px;
            }
            .auth-showcase-panel {
                display: none !important;
            }
            .auth-form-panel {
                width: 100%;
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body data-design="A" class="auth-page-body">

    <div class="auth-wrapper">
        <div class="auth-card-dual">
            <!-- Panel Kiri: Showcase Navy Precision (SESUAI GAMBAR ACUAN) -->
            <div class="auth-showcase-panel">
                <div>
                    <!-- Brand Header -->
                    <div class="d-flex align-items-center gap-3 mb-5">
                        <img src="{{ asset('images/logo-bidtik.svg') }}?v={{ file_exists(public_path('images/logo-bidtik.svg')) ? filemtime(public_path('images/logo-bidtik.svg')) : time() }}" 
                             alt="Logo PresTIK" class="brand-logo">
                        <div>
                            <div class="fw-bold fs-5 text-white lh-1">PresTIK</div>
                            <div class="text-white-50 small fw-bold mt-1" style="font-size: 10px; letter-spacing: 0.8px;">BIDTIK POLDA KALSEL</div>
                        </div>
                    </div>

                    <!-- Headline & Subtitle -->
                    <h1 class="mb-3">
                        Hadir dengan pasti.<br>Tercatat dengan rapi.
                    </h1>
                    <p>
                        Presensi lokasi dan layanan surat personel dalam satu portal.
                    </p>
                </div>

                <!-- Feature Points List (01, 02, 03) -->
                <div class="showcase-features">
                    <div class="feature-item">
                        <span class="feature-num">01</span>
                        <span class="feature-text">Lokasi GPS kantor</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-num">02</span>
                        <span class="feature-text">Selfie kamera depan</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-num">03</span>
                        <span class="feature-text">Status presensi harian</span>
                    </div>
                </div>
            </div>

            <!-- Panel Kanan: Form Login Personel -->
            <div class="auth-form-panel">
                <!-- Mobile Brand Header (< 992px) -->
                <div class="d-flex align-items-center gap-2 mb-4 d-lg-none">
                    <img src="{{ asset('images/logo-bidtik.svg') }}?v={{ file_exists(public_path('images/logo-bidtik.svg')) ? filemtime(public_path('images/logo-bidtik.svg')) : time() }}" 
                         alt="Logo PresTIK" style="width: 34px; height: 34px; object-fit: contain;">
                    <div>
                        <div class="fw-bold text-dark lh-1" style="font-size: 17px;">PresTIK</div>
                        <div class="text-muted" style="font-size: 10px; font-weight: 700;">BIDTIK POLDA KALSEL</div>
                    </div>
                </div>

                <div class="form-title">Masuk Personel</div>
                <div class="form-subtitle">Gunakan akun yang telah terdaftar di PresTIK.</div>

                <!-- Alerts Pesan -->
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

                <!-- Form Login Personel -->
                <form action="{{ route('login.proses') }}" method="POST" autocomplete="on">
                    @csrf

                    <!-- Email Kedinasan -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Kedinasan *</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" 
                               placeholder="nama@bidtik.polri.go.id" required autofocus autocomplete="email">
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <div class="position-relative">
                            <input type="password" class="form-control pe-5 @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Masukkan password" 
                                   required autocomplete="current-password">
                            <button type="button" class="btn-toggle-pwd" id="btnTogglePassword" title="Lihat/Sembunyikan password">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Ingat Saya -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label text-muted small" for="remember">
                                Ingat saya
                            </label>
                        </div>
                    </div>

                    <!-- Tombol Masuk -->
                    <button type="submit" class="btn btn-navy-submit mb-3">
                        Masuk ke PresTIK
                    </button>
                </form>

                <!-- Link Login Admin -->
                <div class="mt-2 text-start">
                    <a href="{{ route('admin.login') }}" class="auth-link">
                        Login Administrator
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Password Visibility JS -->
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

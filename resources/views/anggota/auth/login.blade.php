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
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            margin: 0;
            background: linear-gradient(135deg, rgba(11, 26, 46, 0.88) 0%, rgba(9, 22, 36, 0.94) 100%),
                        url('{{ asset("images/bg-polda-kalsel.jpg") }}') center center / cover no-repeat fixed;
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
        }

        .auth-card {
            background: #FFFFFF;
            border-radius: 18px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .auth-card-header {
            background: #0B132B;
            color: #FFFFFF;
            padding: 2.2rem 1.8rem 1.6rem;
            text-align: center;
            position: relative;
        }

        .auth-card-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #F59E0B, #E5A93C, #FBBF24);
        }

        .gold-badge {
            display: inline-block;
            background: #E5A93C;
            color: #0B132B;
            font-weight: 800;
            font-size: 0.72rem;
            padding: 4px 14px;
            border-radius: 30px;
            letter-spacing: 0.6px;
            margin-bottom: 0.6rem;
            text-transform: uppercase;
        }

        .auth-card-body {
            padding: 2rem 1.8rem 1.8rem;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .input-group-text {
            background-color: #F8FAFC;
            border: 1.5px solid #CBD5E1;
            border-right: none;
            color: #64748B;
            border-radius: 10px 0 0 10px;
            padding: 0.7rem 0.9rem;
        }

        .form-control {
            border: 1.5px solid #CBD5E1;
            border-radius: 0 10px 10px 0;
            padding: 0.7rem 1rem;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #254BD6;
            box-shadow: 0 0 0 3px rgba(37, 75, 214, 0.15);
        }

        .form-control.has-toggle {
            border-radius: 0;
            border-right: none;
        }

        .btn-toggle-pwd {
            background-color: #F8FAFC;
            border: 1.5px solid #CBD5E1;
            border-left: none;
            border-radius: 0 10px 10px 0 !important;
            color: #64748B;
            padding: 0 0.9rem;
        }

        .btn-police-submit {
            background: linear-gradient(135deg, #17263F 0%, #254BD6 100%);
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            padding: 0.85rem;
            font-weight: 700;
            font-size: 15px;
            letter-spacing: 0.3px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(23, 38, 63, 0.3);
        }

        .btn-police-submit:hover {
            background: linear-gradient(135deg, #1E3EA8 0%, #1D4ED8 100%);
            color: #F59E0B;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(23, 38, 63, 0.4);
        }

        .footer-note {
            font-size: 11.5px;
            color: #64748B;
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.2rem;
            border-top: 1px dashed #E2E8F0;
        }
    </style>
</head>
<body data-design="A" class="design-navy-precision">

    <div class="auth-container">
        <div class="auth-card">
            <!-- Header Identitas PresTIK -->
            <div class="auth-card-header">
                <div class="mb-3">
                    <img src="{{ asset('images/logo-bidtik.svg') }}?v={{ file_exists(public_path('images/logo-bidtik.svg')) ? filemtime(public_path('images/logo-bidtik.svg')) : time() }}" 
                         alt="Logo Bidtik Polda Kalsel" style="width: 72px; height: 72px; object-fit: contain; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.5));">
                </div>
                <span class="gold-badge">PresTIK PERSONEL</span>
                <h4 class="fw-bold mb-1 text-white">PresTIK POLDA KALSEL</h4>
                <p class="text-white-50 mb-0 small">Presensi Digital Bidtik - Portal Masuk Personel</p>
            </div>

            <!-- Body Form Login -->
            <div class="auth-card-body">
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
                        <label for="email" class="form-label">Email Kedinasan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope-at"></i></span>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" 
                                   placeholder="nama@bidtik.polri.go.id" required autofocus autocomplete="email">
                        </div>
                    </div>

                    <!-- Password Akun -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                            <input type="password" class="form-control has-toggle @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Masukkan password akun" 
                                   required autocomplete="current-password">
                            <button class="btn btn-toggle-pwd" type="button" id="btnTogglePassword" title="Lihat/Sembunyikan password">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Ingat Saya -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small text-muted" for="remember">
                                Ingat saya di perangkat ini
                            </label>
                        </div>
                    </div>

                    <!-- Tombol Masuk -->
                    <button type="submit" class="btn btn-police-submit w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-box-arrow-in-right fs-5"></i>
                        <span>Masuk ke PresTIK</span>
                    </button>
                </form>

                <!-- Informasi Bantuan -->
                <div class="footer-note">
                    <i class="bi bi-info-circle me-1 text-primary"></i>
                    Belum memiliki akun login atau lupa password? Silakan hubungi <strong>Administrator Bidtik</strong>.
                </div>

                <!-- Tautan Panel Admin -->
                <div class="text-center mt-3">
                    <a href="{{ route('admin.login') }}" class="text-decoration-none text-secondary small fw-semibold">
                        <i class="bi bi-shield-lock me-1"></i> Panel Masuk Administrator
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

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login Personel - PresTIK Polda Kalsel</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-bidtik.svg') }}?v={{ file_exists(public_path('images/logo-bidtik.svg')) ? filemtime(public_path('images/logo-bidtik.svg')) : time() }}">
    
    <!-- Google Fonts & Bootstrap 5 & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --police-navy: #0e2238;
            --police-navy-dark: #091624;
            --police-gold: #e5a93c;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, var(--police-navy-dark) 0%, #17385c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
            margin: 0;
        }
        .login-card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
            border: none;
        }
        .card-header-custom {
            background: var(--police-navy-dark);
            color: #ffffff;
            padding: 2.2rem 1.8rem 1.8rem;
            text-align: center;
            position: relative;
        }
        .card-header-custom::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b, #e5a93c, #fbbf24);
        }
        .gold-badge {
            display: inline-block;
            background: var(--police-gold);
            color: #000000;
            font-weight: 800;
            font-size: 0.72rem;
            padding: 4px 14px;
            border-radius: 30px;
            letter-spacing: 0.6px;
            margin-bottom: 0.6rem;
            text-transform: uppercase;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1.5px solid #cbd5e1;
            font-size: 0.95rem;
        }
        .form-control:focus {
            border-color: #1e3a8a;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.15);
        }
        .input-group-text {
            border-radius: 10px 0 0 10px;
            border: 1.5px solid #cbd5e1;
            border-right: none;
            background-color: #f8fafc;
            color: #64748b;
        }
        .input-group .form-control {
            border-radius: 0 10px 10px 0;
        }
        .input-group .form-control.has-toggle {
            border-radius: 0;
            border-right: none;
        }
        .btn-toggle-pwd {
            border-radius: 0 10px 10px 0 !important;
            border: 1.5px solid #cbd5e1;
            border-left: none;
            background-color: #f8fafc;
            color: #64748b;
        }
        .btn-police {
            background: linear-gradient(135deg, var(--police-navy) 0%, #1e3a8a 100%);
            color: #ffffff;
            border-radius: 10px;
            padding: 0.85rem;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.3px;
            transition: all 0.25s ease;
            border: none;
            box-shadow: 0 4px 12px rgba(14, 34, 56, 0.25);
        }
        .btn-police:hover {
            background: linear-gradient(135deg, #17385c 0%, #1d4ed8 100%);
            color: var(--police-gold);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(14, 34, 56, 0.35);
        }
        .footer-note {
            font-size: 0.8rem;
            color: #64748b;
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.2rem;
            border-top: 1px dashed #e2e8f0;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <!-- Header Identitas -->
        <div class="card-header-custom">
            <div class="mb-3">
                <img src="{{ asset('images/logo-bidtik.svg') }}?v={{ file_exists(public_path('images/logo-bidtik.svg')) ? filemtime(public_path('images/logo-bidtik.svg')) : time() }}" alt="Logo Bidtik Polda Kalsel" style="width: 72px; height: 72px; object-fit: contain; filter: drop-shadow(0 4px 14px rgba(0,0,0,0.6));">
            </div>
            <span class="gold-badge">PresTIK · Presensi Digital</span>
            <h4 class="fw-bold mb-1 tracking-wide text-white">PresTIK POLDA KALSEL</h4>
            <p class="text-white-50 small mb-0">Presensi Digital Bidtik Polda Kalimantan Selatan</p>
        </div>

        <div class="p-4">
            <!-- Alert Pesan Status / Error -->
            @if(session('error'))
                <div class="alert alert-danger py-2 px-3 small d-flex align-items-center mb-3 rounded-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2 fs-5"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if(session('sukses'))
                <div class="alert alert-success py-2 px-3 small d-flex align-items-center mb-3 rounded-3" role="alert">
                    <i class="bi bi-check-circle-fill flex-shrink-0 me-2 fs-5"></i>
                    <div>{{ session('sukses') }}</div>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-3 rounded-3" role="alert">
                    <i class="bi bi-info-circle-fill flex-shrink-0 me-2 fs-5"></i>
                    <div>{{ session('info') }}</div>
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

            <!-- Form Login -->
            <form action="{{ route('login.proses') }}" method="POST" autocomplete="on">
                @csrf

                <!-- Input Email -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold text-dark small mb-1">
                        Email Kedinasan
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-at"></i></span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" 
                               placeholder="nama@bidtik.polri.go.id" required autofocus autocomplete="email">
                    </div>
                </div>

                <!-- Input Password -->
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold text-dark small mb-1">
                        Password
                    </label>
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

                <!-- Tombol Submit -->
                <button type="submit" class="btn btn-police w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-box-arrow-in-right fs-5"></i>
                    <span>Masuk ke Presensi</span>
                </button>
            </form>

            <div class="footer-note">
                <i class="bi bi-info-circle me-1 text-primary"></i>
                Belum memiliki akun login atau lupa password? Silakan hubungi <strong>Administrator Bidtik Polda Kalsel</strong>.
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('admin.login') }}" class="text-decoration-none text-secondary" style="font-size: 0.75rem;">
                    <i class="bi bi-shield-shaded me-1"></i> Panel Masuk Administrator
                </a>
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


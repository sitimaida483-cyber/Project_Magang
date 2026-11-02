<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Presensi Online') - Bidtik Polda Kalsel</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-bidtik.svg') }}">

    <!-- Google Fonts & Bootstrap 5 & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/presensi.css') }}?v={{ file_exists(public_path('css/presensi.css')) ? filemtime(public_path('css/presensi.css')) : time() }}">
    @vite('resources/js/app.js')

    <style>
        /* Mobile Bottom Navigation Bar */
        .anggota-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: #091624;
            border-top: 2px solid #e5a93c;
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1040;
            box-shadow: 0 -4px 18px rgba(0, 0, 0, 0.45);
        }
        .anggota-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 0.72rem;
            font-weight: 600;
            transition: all 0.2s ease;
            padding: 4px 10px;
            border-radius: 8px;
            background: none;
            border: none;
            cursor: pointer;
        }
        .anggota-nav-item i {
            font-size: 1.25rem;
            margin-bottom: 2px;
            transition: transform 0.2s ease;
        }
        .anggota-nav-item:hover, .anggota-nav-item.active {
            color: #f5b83d;
        }
        .anggota-nav-item:hover i, .anggota-nav-item.active i {
            transform: translateY(-2px);
            color: #f5b83d;
        }
        .has-bottom-nav {
            padding-bottom: 84px !important;
        }
        .user-pill-bar {
            background: rgba(14, 34, 56, 0.75);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(229, 169, 60, 0.35);
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            padding: 4px 14px;
            margin-top: 6px;
            color: #ffffff;
            font-size: 0.8rem;
        }
    </style>

    @stack('styles')
</head>
<body class="presensi-page {{ auth('anggota')->check() ? 'has-bottom-nav' : '' }}">

    <!-- Header Polri Kalsel -->
    <header class="header-police">
        <svg class="header-network header-network--left" style="display: none;" viewBox="0 0 400 200" aria-hidden="true" focusable="false">
            <g fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M-12 30 55 68 112 26 172 76 224 34 286 73 350 22 414 61M18 146 76 112 134 164 190 120 248 163 310 116 376 158M55 68 76 112M112 26 134 164M172 76 190 120M224 34 248 163M286 73 310 116M350 22 376 158M18 146 55 68M310 116 350 22" />
                <circle cx="55" cy="68" r="4" fill="currentColor" />
                <circle cx="112" cy="26" r="4" fill="currentColor" />
                <circle cx="172" cy="76" r="4" fill="currentColor" />
                <circle cx="224" cy="34" r="4" fill="currentColor" />
                <circle cx="286" cy="73" r="4" fill="currentColor" />
                <circle cx="350" cy="22" r="4" fill="currentColor" />
                <circle cx="76" cy="112" r="4" fill="currentColor" />
                <circle cx="134" cy="164" r="4" fill="currentColor" />
                <circle cx="190" cy="120" r="4" fill="currentColor" />
                <circle cx="248" cy="163" r="4" fill="currentColor" />
                <circle cx="310" cy="116" r="4" fill="currentColor" />
                <circle cx="376" cy="158" r="4" fill="currentColor" />
            </g>
        </svg>
        <svg class="header-network header-network--right" style="display: none;" viewBox="0 0 400 200" aria-hidden="true" focusable="false">
            <g fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M-12 30 55 68 112 26 172 76 224 34 286 73 350 22 414 61M18 146 76 112 134 164 190 120 248 163 310 116 376 158M55 68 76 112M112 26 134 164M172 76 190 120M224 34 248 163M286 73 310 116M350 22 376 158M18 146 55 68M310 116 350 22" />
                <circle cx="55" cy="68" r="4" fill="currentColor" />
                <circle cx="112" cy="26" r="4" fill="currentColor" />
                <circle cx="172" cy="76" r="4" fill="currentColor" />
                <circle cx="224" cy="34" r="4" fill="currentColor" />
                <circle cx="286" cy="73" r="4" fill="currentColor" />
                <circle cx="350" cy="22" r="4" fill="currentColor" />
                <circle cx="76" cy="112" r="4" fill="currentColor" />
                <circle cx="134" cy="164" r="4" fill="currentColor" />
                <circle cx="190" cy="120" r="4" fill="currentColor" />
                <circle cx="248" cy="163" r="4" fill="currentColor" />
                <circle cx="310" cy="116" r="4" fill="currentColor" />
                <circle cx="376" cy="158" r="4" fill="currentColor" />
            </g>
        </svg>
        <div class="mobile-container text-center header-content">
            <div class="mb-2">
                <img src="{{ asset('images/logo-bidtik.svg') }}" alt="Logo Bidtik Polda Kalsel" style="width: 58px; height: 58px; object-fit: contain; filter: drop-shadow(0 4px 14px rgba(0,0,0,0.65));">
            </div>
            <span class="badge-gold text-uppercase mb-1">Presensi Personel</span>
            <h1 class="fw-bold mb-0 text-white tracking-wide header-title">BIDTIK POLDA KALSEL</h1>
            <p class="text-white-50 small mb-2 header-subtitle">Bidang Teknologi Informasi dan Komunikasi</p>
            
            <!-- Jam Digital Real-Time WITA -->
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill small clock-capsule">
                <i class="bi bi-clock-fill clock-icon"></i>
                <span id="digitalClock" class="fw-semibold text-white font-monospace">--:--:-- WITA</span>
            </div>

            @if(auth('anggota')->check())
                <div class="d-block mt-2">
                    <div class="user-pill-bar">
                        <i class="bi bi-person-check-fill text-warning me-1"></i>
                        <span class="fw-semibold me-1">{{ auth('anggota')->user()->nama }}</span>
                        <span class="text-white-50">({{ auth('anggota')->user()->nrp }})</span>
                    </div>
                </div>
            @endif
        </div>
    </header>

    <!-- Konten Presensi Mobile -->
    <main class="mobile-container main-card-wrapper">
        <img src="{{ asset('images/watermark-shield.svg') }}" class="watermark-shield" alt="" aria-hidden="true">
        @yield('content')
    </main>

    <footer class="presensi-footer text-center">
        <div>Sistem Presensi Personel · Bidtik Polda Kalsel</div>
        <div class="mt-1">
            <a href="{{ route('admin.login') }}" class="text-decoration-none text-secondary" style="font-size: 0.75rem;">
                <i class="bi bi-shield-lock me-1"></i> Panel Masuk Admin
            </a>
        </div>
    </footer>

    @if(auth('anggota')->check())
        <!-- Mobile Bottom Navigation Bar -->
        <nav class="anggota-bottom-nav">
            <a href="{{ route('presensi.index') }}" class="anggota-nav-item {{ request()->routeIs('presensi.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt-fill"></i>
                <span>Presensi</span>
            </a>
            <a href="{{ \Illuminate\Support\Facades\Route::has('anggota.surat.index') ? route('anggota.surat.index') : '#' }}" class="anggota-nav-item {{ request()->routeIs('anggota.surat.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text-fill"></i>
                <span>Pengajuan Surat</span>
            </a>
            <a href="{{ route('anggota.password') }}" class="anggota-nav-item {{ request()->routeIs('anggota.password*') ? 'active' : '' }}">
                <i class="bi bi-key-fill"></i>
                <span>Password</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari sistem presensi?');">
                @csrf
                <button type="submit" class="anggota-nav-item">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </nav>
    @endif

    @stack('modals')

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Jam digital real-time WITA
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                timeZone: 'Asia/Makassar',
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            }).replace(/\./g, ':');
            const clockEl = document.getElementById('digitalClock');
            if (clockEl) {
                clockEl.textContent = timeString + ' WITA';
            }
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    @stack('scripts')
</body>
</html>

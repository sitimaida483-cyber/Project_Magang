<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Presensi Online') - Bidtik Polda Kalsel</title>

    <!-- Google Fonts & Bootstrap 5 & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --police-navy: #0e2238;
            --police-navy-light: #17385c;
            --police-gold: #e5a93c;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        .header-police {
            background: linear-gradient(135deg, var(--police-navy) 0%, var(--police-navy-light) 100%);
            color: #ffffff;
            padding: 1.25rem 1rem 2.5rem;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
            box-shadow: 0 4px 15px rgba(14, 34, 56, 0.15);
        }
        .mobile-container {
            max-width: 540px;
            margin: 0 auto;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .main-card-wrapper {
            margin-top: -1.75rem;
            margin-bottom: 2rem;
        }
        .badge-gold {
            background-color: var(--police-gold);
            color: #000;
            font-weight: 700;
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }
        .card-custom {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            background: #ffffff;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Header Polri Kalsel -->
    <header class="header-police">
        <div class="mobile-container text-center">
            <span class="badge-gold text-uppercase mb-1">Presensi Personel</span>
            <h4 class="fw-bold mb-0 text-white tracking-wide">BIDTIK POLDA KALSEL</h4>
            <p class="text-white-50 small mb-2" style="font-size: 0.8rem;">Bidang Teknologi Informasi dan Komunikasi</p>
            
            <!-- Jam Digital Real-Time WITA -->
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 bg-white bg-opacity-10 rounded-pill border border-white border-opacity-10 small">
                <i class="bi bi-clock-fill text-warning"></i>
                <span id="digitalClock" class="fw-semibold text-white font-monospace">--:--:-- WITA</span>
            </div>
        </div>
    </header>

    <!-- Konten Presensi Mobile -->
    <div class="mobile-container main-card-wrapper">
        @yield('content')
    </div>

    <footer class="text-center py-3 text-muted small border-top bg-white">
        &copy; {{ date('Y') }} Bidtik Polda Kalimantan Selatan
        <div class="mt-1">
            <a href="{{ route('admin.login') }}" class="text-decoration-none text-secondary" style="font-size: 0.75rem;">
                <i class="bi bi-shield-lock me-1"></i> Panel Masuk Admin
            </a>
        </div>
    </footer>

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
            });
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

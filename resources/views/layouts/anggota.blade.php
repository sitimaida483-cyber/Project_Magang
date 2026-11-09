<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Personel') - PresTIK Bidtik Polda Kalsel</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-bidtik.svg') }}?v={{ file_exists(public_path('images/logo-bidtik.svg')) ? filemtime(public_path('images/logo-bidtik.svg')) : time() }}">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Legacy & Redesign CSS (Navy Precision) -->
    <link rel="stylesheet" href="{{ asset('css/presensi.css') }}?v={{ file_exists(public_path('css/presensi.css')) ? filemtime(public_path('css/presensi.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/navy-precision.css') }}?v={{ file_exists(public_path('css/navy-precision.css')) ? filemtime(public_path('css/navy-precision.css')) : time() }}">
    
    @vite('resources/js/app.js')
    @stack('styles')
</head>
<body data-design="A" class="design-navy-precision">

    <!-- Mobile Drawer Backdrop -->
    <div class="np-drawer-backdrop" id="drawerBackdrop"></div>

    <!-- Sidebar Personel (Pilihan A: 248px) -->
    <nav id="sidebar">
        <a href="{{ route('presensi.index') }}" class="sidebar-brand">
            <img src="{{ asset('images/logo-bidtik.svg') }}?v={{ file_exists(public_path('images/logo-bidtik.svg')) ? filemtime(public_path('images/logo-bidtik.svg')) : time() }}" 
                 alt="Logo PresTIK Polda Kalsel" style="width: 38px; height: 38px; object-fit: contain;">
            <div>
                <div class="sidebar-brand-title">PresTIK</div>
                <div class="sidebar-brand-subtitle">BIDTIK POLDA KALSEL</div>
            </div>
        </a>

        <div class="sidebar-role-label">PERSONEL</div>

        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('presensi.*') ? 'active' : '' }}" href="{{ route('presensi.index') }}">
                    <i class="bi bi-grid-fill"></i>
                    <span>Presensi Kehadiran</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('anggota.surat.*') ? 'active' : '' }}" href="{{ route('anggota.surat.index') }}">
                    <i class="bi bi-file-earmark-text-fill"></i>
                    <span>Pengajuan Surat</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('anggota.password*') ? 'active' : '' }}" href="{{ route('anggota.password') }}">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>Ubah Password</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari sistem?');">
                @csrf
                <button type="submit" class="sidebar-logout-btn">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Keluar</span>
                </button>
            </form>
            <div class="sidebar-copyright">Presensi Digital Bidtik</div>
        </div>
    </nav>

    <!-- Main Content Shell -->
    <div id="main-content">
        <!-- Topbar Desktop (76px) & Mobile Header (72px) -->
        <header class="np-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none p-2" id="sidebarToggle" aria-label="Toggle Menu" style="min-height: 40px; border-radius: 8px;">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="np-topbar-date d-none d-sm-block">
                    {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, DD MMMM Y') }}
                </div>
            </div>

            <div class="np-topbar-right">
                <div id="digitalClock" class="font-monospace text-dark fw-bold">--:--:-- WITA</div>
                @if(auth('anggota')->check())
                    <span class="text-muted d-none d-md-inline">/</span>
                    <span class="d-none d-md-inline text-dark">{{ auth('anggota')->user()->nama }}</span>
                    <!-- Mobile Profile Avatar Initial -->
                    <div class="d-md-none rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 11px;">
                        {{ strtoupper(substr(auth('anggota')->user()->nama, 0, 2)) }}
                    </div>
                @endif
            </div>
        </header>

        <!-- Main Body Wrapper -->
        <main class="np-content-wrapper">
            {{-- Notifikasi Flash Message --}}
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
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show border-0 rounded-3 mb-4" role="alert" style="background-color: #EAF0FF; color: #254BD6; border: 1px solid #C7D7FE !important;">
                    <i class="bi bi-info-circle-fill me-2"></i> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @if(auth('anggota')->check())
        <!-- Personel Mobile Bottom Navigation (Pilihan A: 72px) -->
        <nav class="np-bottom-nav">
            <a href="{{ route('presensi.index') }}" class="np-bottom-nav-item {{ request()->routeIs('presensi.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt-fill"></i>
                <span>Presensi</span>
            </a>
            <a href="{{ route('anggota.surat.index') }}" class="np-bottom-nav-item {{ request()->routeIs('anggota.surat.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text-fill"></i>
                <span>Pengajuan</span>
            </a>
            <a href="{{ route('anggota.password') }}" class="np-bottom-nav-item {{ request()->routeIs('anggota.password*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Password</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="d-inline h-100 flex-grow-1" onsubmit="return confirm('Apakah Anda yakin ingin keluar?');">
                @csrf
                <button type="submit" class="np-bottom-nav-item w-100">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </nav>
    @endif

    @stack('modals')

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle & Mobile Drawer Control
        const sidebarEl = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const backdropEl = document.getElementById('drawerBackdrop');

        function toggleSidebar() {
            if (sidebarEl) {
                const isActive = sidebarEl.classList.toggle('active');
                if (backdropEl) backdropEl.classList.toggle('active', isActive);
            }
        }

        toggleBtn?.addEventListener('click', toggleSidebar);
        backdropEl?.addEventListener('click', toggleSidebar);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebarEl?.classList.contains('active')) {
                toggleSidebar();
            }
        });

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

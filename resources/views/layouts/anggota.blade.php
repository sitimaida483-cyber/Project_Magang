<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Personel') - Presensi Bidtik Polda Kalsel</title>
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
        :root {
            --police-navy: #0e2238;
            --police-dark: #091624;
            --police-gold: #e5a93c;
            --police-gold-hover: #cf9327;
            --sidebar-width: 260px;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f6f9;
            color: #334155;
            min-height: 100vh;
        }

        /* Sidebar Styling (Sama Persis seperti Admin) */
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, var(--police-navy) 0%, var(--police-dark) 100%);
            color: #e2e8f0;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s;
            box-shadow: 4px 0 10px rgba(0,0,0,0.05);
        }
        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            text-decoration: none;
            color: #ffffff;
        }
        .sidebar-brand .badge-role {
            background-color: var(--police-gold);
            color: #000;
            font-weight: 700;
            font-size: 0.65rem;
            padding: 3px 7px;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }
        .sidebar-menu {
            padding: 1rem 0.75rem;
            list-style: none;
            margin: 0;
        }
        .menu-header {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 0.75rem 0.75rem 0.25rem;
        }
        .sidebar-menu .nav-link {
            color: #94a3b8;
            padding: 0.7rem 0.85rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
            margin-bottom: 2px;
            text-decoration: none;
        }
        .sidebar-menu .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255,255,255,0.06);
        }
        .sidebar-menu .nav-link.active {
            color: #ffffff;
            background-color: #1e3a8a;
            font-weight: 600;
        }
        .sidebar-menu .nav-link.active i {
            color: var(--police-gold);
        }

        /* Content Area */
        #main-content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        /* Stat Cards (Sama Persis seperti Admin Dashboard) */
        .card-stat {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: inherit;
        }
        .card-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(0,0,0,0.1);
            color: inherit;
        }
        .card-stat:active {
            transform: translateY(-1px);
        }

        @media (max-width: 991.98px) {
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            #sidebar.active {
                margin-left: 0;
            }
            #main-content {
                margin-left: 0;
                padding-bottom: 74px; /* Space for bottom nav on mobile */
            }
        }

        /* Mobile Bottom Nav */
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
            padding: 4px 10px;
            border-radius: 8px;
            background: none;
            border: none;
        }
        .anggota-nav-item i {
            font-size: 1.25rem;
            margin-bottom: 2px;
        }
        .anggota-nav-item:hover, .anggota-nav-item.active {
            color: #f5b83d;
        }
        @media (min-width: 992px) {
            .anggota-bottom-nav {
                display: none !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar Anggota -->
    <nav id="sidebar">
        <a href="{{ route('presensi.index') }}" class="sidebar-brand">
            <img src="{{ asset('images/logo-bidtik.svg') }}" alt="Logo PresTIK Polda Kalsel" style="width: 42px; height: 42px; object-fit: contain; filter: drop-shadow(0 2px 6px rgba(0,0,0,0.4));">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-white fs-5 lh-1" style="letter-spacing: 0.5px;">PresTIK</span>
                    <span class="badge-role">PERSONEL</span>
                </div>
                <small class="text-white-50 d-block mt-1" style="font-size: 0.68rem; letter-spacing: 0.2px;">Presensi Digital Bidtik</small>
            </div>
        </a>

        <ul class="sidebar-menu">
            <li class="menu-header">Menu Utama</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('presensi.*') ? 'active' : '' }}" href="{{ route('presensi.index') }}">
                    <i class="bi bi-geo-alt-fill"></i> Presensi Kehadiran
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('anggota.surat.*') ? 'active' : '' }}" href="{{ route('anggota.surat.index') }}">
                    <i class="bi bi-file-earmark-text-fill"></i> Pengajuan Surat
                </a>
            </li>

            <li class="menu-header">Pengaturan Akun</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('anggota.password*') ? 'active' : '' }}" href="{{ route('anggota.password') }}">
                    <i class="bi bi-key-fill"></i> Ubah Password
                </a>
            </li>

            <li class="menu-header">Sesi Login</li>
            <li class="nav-item">
                <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari sistem?');">
                    @csrf
                    <button type="submit" class="nav-link text-danger border-0 w-100 text-start bg-transparent">
                        <i class="bi bi-box-arrow-right"></i> Keluar / Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div id="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="fw-semibold text-secondary small d-none d-md-block">
                    PresTIK · Presensi Digital Bidtik Polda Kalsel
                </div>
            </div>

            <!-- Jam WITA & User Dropdown -->
            <div class="d-flex align-items-center gap-3">
                <div class="d-none d-sm-inline-flex align-items-center gap-1 text-muted small bg-light px-3 py-1 rounded-pill border">
                    <i class="bi bi-clock-fill text-warning"></i>
                    <span id="digitalClock" class="font-monospace fw-semibold">--:--:-- WITA</span>
                </div>

                @if(auth('anggota')->check())
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 border py-1 px-2" type="button" data-bs-toggle="dropdown">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background-color: #0e2238 !important;">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div class="text-start d-none d-sm-block">
                                <span class="fw-semibold text-dark d-block lh-1 small">{{ auth('anggota')->user()->nama }}</span>
                                <small class="text-muted" style="font-size: 0.7rem;">NRP: {{ auth('anggota')->user()->nrp }}</small>
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 10px;">
                            <li class="px-3 py-2 border-bottom">
                                <small class="text-muted d-block">Personel Terdaftar</small>
                                <strong class="d-block text-dark">{{ auth('anggota')->user()->nama }}</strong>
                                <span class="text-secondary small">NRP: {{ auth('anggota')->user()->nrp }} ({{ auth('anggota')->user()->jabatan ?? 'Bidtik' }})</span>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('anggota.password') }}">
                                    <i class="bi bi-key me-2 text-warning"></i> Ganti Password
                                </a>
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin keluar?');">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger py-2">
                                        <i class="bi bi-box-arrow-right me-2"></i> Keluar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </header>

        <!-- Page Body Content -->
        <main class="p-3 p-md-4 flex-grow-1">
            @yield('content')
        </main>

        <footer class="bg-white border-top text-center py-3 text-muted small">
            &copy; {{ date('Y') }} PresTIK (Presensi Digital Bidtik) · Polda Kalimantan Selatan
        </footer>
    </div>

    @if(auth('anggota')->check())
        <!-- Mobile Bottom Nav -->
        <nav class="anggota-bottom-nav">
            <a href="{{ route('presensi.index') }}" class="anggota-nav-item {{ request()->routeIs('presensi.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt-fill"></i>
                <span>Presensi</span>
            </a>
            <a href="{{ route('anggota.surat.index') }}" class="anggota-nav-item {{ request()->routeIs('anggota.surat.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text-fill"></i>
                <span>Pengajuan</span>
            </a>
            <a href="{{ route('anggota.password') }}" class="anggota-nav-item {{ request()->routeIs('anggota.password*') ? 'active' : '' }}">
                <i class="bi bi-key-fill"></i>
                <span>Password</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin keluar?');">
                @csrf
                <button type="submit" class="anggota-nav-item">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </nav>
    @endif

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
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

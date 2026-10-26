<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - Presensi Bidtik Polda Kalsel</title>
    
    <!-- Google Fonts & Bootstrap 5 & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

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
        /* Sidebar Styling */
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

        /* Stat Cards */
        .card-stat {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: transform 0.2s;
        }
        .card-stat:hover {
            transform: translateY(-2px);
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
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar -->
    <nav id="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-shield-lock-fill text-dark fs-5"></i>
            </div>
            <div>
                <div class="fw-bold lh-1 text-white">BIDTIK POLDA</div>
                <small class="text-white-50" style="font-size: 0.75rem;">Kalsel Presensi</small>
            </div>
        </a>

        <ul class="sidebar-menu">
            <li class="menu-header">Menu Utama</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>

            <li class="menu-header">Presensi & Monitoring</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.presensi.*') ? 'active' : '' }}" href="{{ url('/admin/presensi') }}">
                    <i class="bi bi-calendar-check-fill"></i> Rekap Absensi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.peta.*') ? 'active' : '' }}" href="{{ url('/admin/peta') }}">
                    <i class="bi bi-geo-alt-fill"></i> Peta Lokasi
                </a>
            </li>

            <li class="menu-header">Master & Pengaturan</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.anggota.*') ? 'active' : '' }}" href="{{ url('/admin/anggota') }}">
                    <i class="bi bi-people-fill"></i> Data Anggota
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.pengaturan.*') ? 'active' : '' }}" href="{{ url('/admin/pengaturan') }}">
                    <i class="bi bi-sliders"></i> Pengaturan Kantor
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.log.*') ? 'active' : '' }}" href="{{ url('/admin/log') }}">
                    <i class="bi bi-clock-history"></i> Log Aktivitas
                </a>
            </li>

            <li class="menu-header">Akses Cepat</li>
            <li class="nav-item">
                <a class="nav-link text-info" href="{{ url('/presensi') }}" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i> Buka Halaman Presensi
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div id="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div class="fw-semibold text-secondary">
                Bidang Teknologi Informasi dan Komunikasi (Bidtik) Polda Kalsel
            </div>

            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 border" type="button" data-bs-toggle="dropdown">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <span class="fw-medium text-dark">{{ auth('admin')->user()->nama ?? 'Admin' }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li class="px-3 py-2 border-bottom">
                        <small class="text-muted d-block">Masuk sebagai</small>
                        <span class="fw-bold">{{ auth('admin')->user()->username ?? 'admin' }}</span>
                    </li>
                    <li>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger py-2">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <!-- Page Body -->
        <main class="p-4 flex-grow-1">
            {{-- Notifikasi Flash Message --}}
            @if(session('sukses'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('sukses') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="bg-white border-top text-center py-3 text-muted small">
            &copy; {{ date('Y') }} Bidtik Polda Kalimantan Selatan - Sistem Presensi Online
        </footer>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
    @stack('scripts')
</body>
</html>

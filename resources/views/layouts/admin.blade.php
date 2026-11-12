<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - PresTIK Bidtik Polda Kalsel</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-bidtik.svg') }}?v={{ file_exists(public_path('images/logo-bidtik.svg')) ? filemtime(public_path('images/logo-bidtik.svg')) : time() }}">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Redesign CSS (Navy Precision) -->
    <link rel="stylesheet" href="{{ asset('css/navy-precision.css') }}?v={{ file_exists(public_path('css/navy-precision.css')) ? filemtime(public_path('css/navy-precision.css')) : time() }}">
    
    @stack('styles')
</head>
<body data-design="A" class="design-navy-precision">

    <!-- Mobile Drawer Backdrop -->
    <div class="np-drawer-backdrop" id="drawerBackdrop"></div>

    <!-- Sidebar Admin (Pilihan A: 248px) -->
    <nav id="sidebar">
        <div class="d-flex align-items-center justify-content-between p-3 border-bottom border-secondary border-opacity-25 d-lg-none">
            <span class="text-white fw-bold">Menu Navigasi</span>
            <button class="btn btn-sm text-white-50 p-1" id="sidebarCloseBtn" aria-label="Tutup Menu">
                <i class="bi bi-x-lg fs-5"></i>
            </button>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <img src="{{ asset('images/logo-bidtik.svg') }}?v={{ file_exists(public_path('images/logo-bidtik.svg')) ? filemtime(public_path('images/logo-bidtik.svg')) : time() }}" 
                 alt="Logo PresTIK Polda Kalsel" style="width: 38px; height: 38px; object-fit: contain;">
            <div>
                <div class="sidebar-brand-title">PresTIK</div>
                <div class="sidebar-brand-subtitle">BIDTIK POLDA KALSEL</div>
            </div>
        </a>

        <div class="sidebar-role-label">ADMINISTRATOR</div>

        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-grid-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.presensi.*') ? 'active' : '' }}" href="{{ url('/admin/presensi') }}">
                    <i class="bi bi-calendar-check-fill"></i>
                    <span>Rekap Absensi</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.surat.*') ? 'active' : '' }}" href="{{ route('admin.surat.index') }}">
                    <i class="bi bi-file-earmark-text-fill"></i>
                    <span>Pengajuan Surat</span>
                    @php
                        $badgeMenunggu = \App\Models\PengajuanSurat::where('status_pengajuan', 'menunggu')->count();
                    @endphp
                    @if($badgeMenunggu > 0)
                        <span class="badge bg-warning text-dark ms-auto fw-bold" style="font-size: 0.7rem; border-radius: 4px;">{{ $badgeMenunggu }}</span>
                    @endif
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.anggota.*') ? 'active' : '' }}" href="{{ url('/admin/anggota') }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Data Anggota</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.peta.*') ? 'active' : '' }}" href="{{ url('/admin/peta') }}">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>Peta Lokasi</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.pengaturan.*') ? 'active' : '' }}" href="{{ url('/admin/pengaturan') }}">
                    <i class="bi bi-sliders"></i>
                    <span>Pengaturan Kantor</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.log.*') ? 'active' : '' }}" href="{{ url('/admin/log') }}">
                    <i class="bi bi-clock-history"></i>
                    <span>Log Aktivitas</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <form action="{{ route('admin.logout') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin logout?');">
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
                <span class="text-muted d-none d-md-inline">/</span>
                <span class="d-none d-md-inline text-dark">{{ auth('admin')->user()->nama ?? 'Admin' }}</span>
                <!-- Mobile Profile Avatar Initial -->
                <div class="d-md-none rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 11px;">
                    {{ strtoupper(substr(auth('admin')->user()->nama ?? 'AD', 0, 2)) }}
                </div>
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

    @stack('modals')

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle & Mobile Drawer Control
        const sidebarEl = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarCloseBtn');
        const backdropEl = document.getElementById('drawerBackdrop');

        function toggleSidebar() {
            if (sidebarEl) {
                const isActive = sidebarEl.classList.toggle('active');
                if (backdropEl) backdropEl.classList.toggle('active', isActive);
            }
        }

        toggleBtn?.addEventListener('click', toggleSidebar);
        closeBtn?.addEventListener('click', toggleSidebar);
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

        // Action Button Switcher: geser sorotan biru saat hover/klik/fokus
        document.querySelectorAll('.np-action-switcher').forEach(function(group) {
            const buttons = group.querySelectorAll('.np-switch-btn');

            buttons.forEach(function(btn) {
                btn.addEventListener('pointerenter', function(e) {
                    if (e.pointerType && e.pointerType !== 'mouse') return;
                    group.classList.add('has-hover');
                    buttons.forEach(function(b) { b.classList.remove('is-hovered'); });
                    btn.classList.add('is-hovered');
                });

                btn.addEventListener('pointerleave', function(e) {
                    if (e.pointerType && e.pointerType !== 'mouse') return;
                    btn.classList.remove('is-hovered');
                    if (!group.querySelector('.np-switch-btn.is-hovered')) {
                        group.classList.remove('has-hover');
                    }
                });

                btn.addEventListener('click', function() {
                    buttons.forEach(function(b) { b.classList.remove('is-active'); });
                    btn.classList.add('is-active');
                    btn.blur();

                    // Sinkronkan filter form saat ini ke tautan ekspor PDF / Excel
                    if (btn.classList.contains('np-export-link') && btn.dataset.baseUrl) {
                        const form = btn.closest('form');
                        if (form) {
                            const params = new URLSearchParams();
                            new FormData(form).forEach(function(value, key) {
                                if (typeof value === 'string' && value.trim() !== '') {
                                    params.append(key, value.trim());
                                }
                            });
                            const query = params.toString();
                            btn.href = query ? (btn.dataset.baseUrl + '?' + query) : btn.dataset.baseUrl;
                        }
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>

@extends('layouts.admin')

@section('title', 'Diagnostik Jalur Jaringan & Proxy')

@section('content')
<div class="mb-4">
    <div class="d-flex align-items-center gap-2">
        <h1 class="h1-navy mb-0">Diagnostik Proxy & Jaringan</h1>
        <span class="badge bg-warning text-dark fw-bold">Admin Tool</span>
    </div>
    <div class="subtitle-navy">Inspeksi header reverse proxy Cloudflare dan resolusi IP klien.</div>
</div>

<!-- Banner Peringatan Keamanan -->
<div class="alert alert-danger border-0 rounded-3 mb-4 p-3" style="background-color: var(--np-danger-soft); color: var(--np-danger); border-left: 4px solid var(--np-danger) !important;">
    <div class="d-flex align-items-start gap-3">
        <i class="bi bi-exclamation-octagon-fill fs-3"></i>
        <div>
            <h6 class="fw-bold mb-1">PERINGATAN KEAMANAN: Halaman Diagnostik Aktif</h6>
            <p class="small mb-0">
                Halaman ini menampilkan informasi diagnostik teknis jaringan server Anda.
                Segera nonaktifkan fitur ini di berkas <code>.env</code> produksi setelah Anda selesai memeriksa jalur proxy:
                <br>
                <strong class="font-monospace">DIAGNOSTIK_PROXY_ENABLED=false</strong>
            </p>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Card IP Terdeteksi Utama -->
    <div class="col-12 col-md-6">
        <div class="card np-card h-100">
            <div class="card-header bg-white">
                <span class="fw-bold text-dark">IP Klien Resmi ($request->ip())</span>
            </div>
            <div class="card-body">
                <div class="display-6 fw-bold text-primary mb-2 font-monospace">{{ $ipTerdeteksi }}</div>
                <p class="text-muted small mb-0">
                    Ini adalah alamat IP yang digunakan oleh rate limiter login, audit log aktivitas, dan rekaman presensi personel.
                </p>
            </div>
        </div>
    </div>

    <!-- Card Konfigurasi Trusted Proxies -->
    <div class="col-12 col-md-6">
        <div class="card np-card h-100">
            <div class="card-header bg-white">
                <span class="fw-bold text-dark">Status Konfigurasi Trusted Proxy</span>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Total Proxy Terdaftar:</span>
                    <span class="badge bg-primary px-3 py-2 fs-6">{{ $totalTrusted }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Status Wildcard (*):</span>
                    @if($isWildcard)
                        <span class="badge bg-danger">Aktif (Tidak Aman untuk Produksi)</span>
                    @else
                        <span class="badge bg-success">Nonaktif (Aman - CIDR Tertarget)</span>
                    @endif
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Konfigurasi ENV:</span>
                    <span class="font-monospace small text-dark">{{ env('TRUSTED_PROXIES', 'cloudflare') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Header Jaringan Mentah -->
<div class="table-np-container mb-4">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th style="width: 35%;">NAMA HEADER / SUMBER</th>
                <th style="width: 65%;">NILAI YANG DITERIMA DARI SERVER</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="fw-bold text-dark">CF-Connecting-IP (Cloudflare)</td>
                <td class="font-monospace text-primary fw-bold">{{ $cfIp }}</td>
            </tr>
            <tr>
                <td class="fw-bold text-dark">X-Forwarded-For</td>
                <td class="font-monospace text-dark">{{ $xForwardedFor }}</td>
            </tr>
            <tr>
                <td class="fw-bold text-dark">X-Real-IP</td>
                <td class="font-monospace text-dark">{{ $xRealIp }}</td>
            </tr>
            <tr>
                <td class="fw-bold text-dark">REMOTE_ADDR (Koneksi Langsung ke PHP)</td>
                <td class="font-monospace text-dark">{{ $remoteAddr }}</td>
            </tr>
            <tr>
                <td class="fw-bold text-dark">User-Agent</td>
                <td class="font-monospace text-muted small">{{ $userAgent }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="card np-card p-3">
    <h6 class="fw-bold mb-2">Cara Membaca Hasil Diagnostik:</h6>
    <ol class="small text-muted mb-0 ps-3">
        <li>Jika Anda menggunakan Cloudflare, nilai <code>$request->ip()</code> harus cocok dengan <code>CF-Connecting-IP</code> (alamat IP asli HP/laptop Anda).</li>
        <li>Nilai <code>REMOTE_ADDR</code> adalah IP simpul server yang langsung terhubung ke PHP (bisa berupa IP edge Cloudflare atau IP reverse proxy hosting).</li>
        <li>Jika <code>$request->ip()</code> menampilkan IP Cloudflare/hosting dan bukan IP asli Anda, tambahkan IP <code>REMOTE_ADDR</code> tersebut ke variabel <code>TRUSTED_PROXIES</code> di <code>.env</code>.</li>
    </ol>
</div>
@endsection


@extends('layouts.admin')

@section('title', 'Detail Anggota - ' . $anggota->nama)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold text-dark mb-0">Detail Profil Personel</h3>
        <p class="text-muted small mb-0">
            Informasi lengkap keanggotaan dan riwayat kehadiran presensi personel.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.anggota.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <a href="{{ route('admin.anggota.edit', $anggota->id_anggota) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit Data
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Kartu Profil Personel -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-person-badge-fill me-1 text-primary"></i> Data Anggota
                </h5>
                @if($anggota->status === 'aktif')
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                        <i class="bi bi-check-circle me-1"></i> Aktif
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                        <i class="bi bi-dash-circle me-1"></i> Nonaktif
                    </span>
                @endif
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary mx-auto d-flex align-items-center justify-content-center mb-2" 
                         style="width: 72px; height: 72px; font-size: 2rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-dark">{{ $anggota->nama }}</h5>
                    <div class="badge bg-light text-secondary border font-monospace mt-1">NRP: {{ $anggota->nrp }}</div>
                </div>

                <div class="list-group list-group-flush border-top border-bottom small">
                    <div class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted">Pangkat / Jabatan</span>
                        <span class="fw-semibold text-dark">{{ $anggota->jabatan }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted">Unit Kerja / Satuan</span>
                        <span class="fw-semibold text-dark">{{ $anggota->unit_kerja }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted">Nomor Kontak / WA</span>
                        <span class="fw-semibold text-dark">{{ $anggota->no_telepon ?? '-' }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted">Admin Pendaftar</span>
                        <span class="fw-semibold text-dark">{{ $anggota->admin->nama ?? '-' }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted">Tanggal Terdaftar</span>
                        <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($anggota->created_at)->translatedFormat('d F Y, H:i') }} WITA</span>
                    </div>
                </div>

                <!-- Perangkat Notifikasi Web Push -->
                <div class="mt-4">
                    <h6 class="fw-bold small text-secondary text-uppercase mb-2">
                        <i class="bi bi-bell-fill me-1 text-warning"></i> Perangkat Notifikasi Web Push
                    </h6>
                    @if($anggota->perangkatNotifikasi->count() > 0)
                        <div class="list-group list-group-flush small">
                            @foreach($anggota->perangkatNotifikasi as $perangkat)
                                <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-phone me-1 text-muted"></i>
                                        <span class="fw-semibold">{{ ucfirst($perangkat->jenis_perangkat ?? 'Browser/HP') }}</span>
                                    </div>
                                    @if($perangkat->status === 'aktif')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border">Nonaktif</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-2 bg-light rounded text-muted small text-center">
                            <i class="bi bi-info-circle me-1"></i> Belum ada perangkat notifikasi yang terdaftar. Izin push akan diminta saat anggota presensi pertama kali.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik & Riwayat Presensi -->
    <div class="col-12 col-lg-7">
        <!-- Stat Presensi Anggota -->
        <div class="row g-3 mb-4">
            <div class="col-4">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white border-bottom border-4 border-success">
                    <small class="text-muted text-uppercase fw-semibold d-block">Masuk</small>
                    <h3 class="fw-bold text-success mb-0 mt-1">{{ $totalMasuk }}</h3>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white border-bottom border-4 border-primary">
                    <small class="text-muted text-uppercase fw-semibold d-block">Pulang</small>
                    <h3 class="fw-bold text-primary mb-0 mt-1">{{ $totalPulang }}</h3>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white border-bottom border-4 border-danger">
                    <small class="text-muted text-uppercase fw-semibold d-block">Terlambat</small>
                    <h3 class="fw-bold text-danger mb-0 mt-1">{{ $totalTerlambat }}</h3>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat 10 Presensi Terakhir -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-calendar2-check-fill me-1 text-primary"></i> 10 Presensi Terakhir
                </h5>
                <span class="badge bg-light text-secondary border">Total {{ $anggota->presensi_count }} Kali</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light text-secondary text-uppercase">
                            <tr>
                                <th class="ps-3">Tanggal</th>
                                <th>Waktu</th>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th class="text-center">Koordinat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayatPresensi as $presensi)
                                <tr>
                                    <td class="ps-3 fw-semibold">
                                        {{ \Carbon\Carbon::parse($presensi->tanggal)->translatedFormat('d M Y') }}
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($presensi->waktu)->format('H:i:s') }} WITA</td>
                                    <td>
                                        @if($presensi->jenis_presensi === 'masuk')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Masuk</span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Pulang</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($presensi->status_kehadiran === 'tepat_waktu')
                                            <span class="badge bg-success text-white">Tepat Waktu</span>
                                        @else
                                            <span class="badge bg-danger text-white">Terlambat</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-monospace text-muted" style="font-size: 0.75rem;">
                                        {{ $presensi->latitude }}, {{ $presensi->longitude }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox d-block mb-1 fs-3 opacity-50"></i>
                                        Personel ini belum pernah melakukan presensi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

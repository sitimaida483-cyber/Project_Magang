@extends('layouts.anggota')

@section('title', 'Riwayat Pengajuan Surat')

@section('content')
<div class="card card-custom p-4 presensi-card">
    <!-- Header Halaman -->
    <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-3 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-file-earmark-text-fill text-warning me-1"></i> Pengajuan Surat
            </h5>
            <p class="text-muted small mb-0">
                Riwayat surat izin, sakit, cuti, atau dinas personel
            </p>
        </div>
        <a href="{{ route('anggota.surat.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm" style="background-color: #0e2238; border-color: #0e2238;">
            <i class="bi bi-plus-circle me-1"></i> Buat Pengajuan
        </a>
    </div>

    <!-- Alert Sukses / Error -->
    @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show small mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show small mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Ringkas -->
    <form method="GET" action="{{ route('anggota.surat.index') }}" class="mb-3">
        <div class="row g-2">
            <div class="col-6">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="col-6">
                <select name="jenis" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Jenis</option>
                    @foreach($daftarJenis as $val => $label)
                        <option value="{{ $val }}" {{ request('jenis') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <!-- Daftar Surat Keterangan -->
    @forelse($daftarSurat as $surat)
        <div class="card border rounded-3 p-3 mb-3 shadow-sm {{ $surat->status_pengajuan === 'disetujui' ? 'border-success-subtle bg-light-subtle' : ($surat->status_pengajuan === 'ditolak' ? 'border-danger-subtle bg-light-subtle' : '') }}">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="badge bg-primary text-uppercase px-2 py-1" style="font-size: 0.75rem;">
                        {{ $surat->label_jenis }}
                    </span>
                    <span class="text-muted small d-block mt-1" style="font-size: 0.72rem;">
                        Diajukan: {{ \Carbon\Carbon::parse($surat->created_at)->translatedFormat('d M Y, H:i') }} WITA
                    </span>
                </div>
                <div>
                    {!! $surat->status_badge !!}
                </div>
            </div>

            <!-- Periode Tanggal -->
            <div class="p-2 bg-light rounded-2 border mb-2 small">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted"><i class="bi bi-calendar3 me-1"></i> Periode:</span>
                    <span class="fw-semibold text-dark">
                        {{ \Carbon\Carbon::parse($surat->tanggal_mulai)->translatedFormat('d M Y') }}
                        s/d
                        {{ \Carbon\Carbon::parse($surat->tanggal_selesai)->translatedFormat('d M Y') }}
                    </span>
                </div>
                <div class="text-end text-secondary mt-1" style="font-size: 0.75rem;">
                    Durasi: {{ \Carbon\Carbon::parse($surat->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($surat->tanggal_selesai)) + 1 }} Hari
                </div>
            </div>

            <!-- Alasan / Uraian -->
            <div class="mb-2">
                <span class="text-muted small d-block" style="font-size: 0.75rem;">Keterangan / Alasan:</span>
                <p class="small text-dark mb-0 fw-normal" style="line-height: 1.4;">
                    {{ $surat->keterangan }}
                </p>
            </div>

            <!-- Catatan Admin jika diproses -->
            @if($surat->catatan_admin)
                <div class="p-2 rounded mb-2 small {{ $surat->status_pengajuan === 'disetujui' ? 'bg-success bg-opacity-10 text-success-emphasis border border-success-subtle' : 'bg-danger bg-opacity-10 text-danger-emphasis border border-danger-subtle' }}">
                    <strong class="d-block" style="font-size: 0.75rem;">
                        <i class="bi bi-chat-quote-fill me-1"></i> Catatan Admin ({{ $surat->admin->nama ?? 'Admin' }}):
                    </strong>
                    <span>{{ $surat->catatan_admin }}</span>
                </div>
            @endif

            <!-- Aksi: Buka File PDF & Batalkan -->
            <div class="d-flex justify-content-between align-items-center pt-2 border-top gap-2">
                <a href="{{ route('anggota.surat.file', $surat->id_pengajuan) }}" target="_blank" class="btn btn-outline-danger btn-sm py-1 px-2" style="font-size: 0.75rem;">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Lihat Surat PDF
                </a>

                @if($surat->status_pengajuan === 'menunggu')
                    <form action="{{ route('anggota.surat.cancel', $surat->id_pengajuan) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan surat ini?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm py-1 px-2 text-danger" style="font-size: 0.75rem;">
                            <i class="bi bi-trash me-1"></i> Batalkan
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
            <h6 class="fw-bold mb-1">Belum Ada Pengajuan Surat</h6>
            <p class="small mb-3">Anda belum pernah mengajukan surat izin, sakit, dinas, atau cuti.</p>
            <a href="{{ route('anggota.surat.create') }}" class="btn btn-primary btn-sm px-3" style="background-color: #0e2238; border-color: #0e2238;">
                <i class="bi bi-plus-circle me-1"></i> Ajukan Sekarang
            </a>
        </div>
    @endforelse

    <!-- Pagination -->
    @if($daftarSurat->hasPages())
        <div class="mt-3">
            {{ $daftarSurat->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection


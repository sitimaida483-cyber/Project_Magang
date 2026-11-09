@extends('layouts.admin')

@section('title', 'Detail Pengajuan Surat - ' . ($pengajuan->anggota->nama ?? 'Personel'))

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h1-navy">Detail Pengajuan Surat</h1>
        <p class="subtitle-navy mb-0">Presensi Digital Bidtik Polda Kalimantan Selatan</p>
    </div>
    <div>
        <a href="{{ route('admin.surat.index') }}" class="btn np-btn-outline btn-sm d-inline-flex align-items-center" style="height: 38px;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<!-- Alert Error Validasi -->
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4 border-0" role="alert" style="background-color: var(--np-danger-soft); color: var(--np-danger); border-left: 4px solid var(--np-danger) !important; border-radius: 8px;">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Kolom Kiri: Data Pengajuan Surat & Form Keputusan -->
    <div class="col-12 col-lg-5">
        <!-- Kartu Data Pengajuan Surat -->
        <div class="np-card mb-4 p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h2 class="h2-navy mb-0" style="font-size: 18px;">Data Pengajuan Surat</h2>
                @if($pengajuan->status_pengajuan === 'disetujui')
                    <span class="np-badge np-badge-success">Disetujui</span>
                @elseif($pengajuan->status_pengajuan === 'ditolak')
                    <span class="np-badge np-badge-danger">Ditolak</span>
                @else
                    <span class="np-badge np-badge-warning">Menunggu</span>
                @endif
            </div>

            <!-- Personel -->
            <div class="mb-3">
                <div class="text-muted small fw-semibold">Personel</div>
                <div class="fw-bold" style="color: var(--np-ink); font-size: 15px;">
                    {{ $pengajuan->anggota->nama ?? '-' }} / NRP {{ $pengajuan->anggota->nrp ?? '-' }}
                </div>
                <div class="text-muted small mt-1">
                    {{ $pengajuan->anggota->jabatan ?? '-' }} • {{ $pengajuan->anggota->unit_kerja ?? '-' }}
                </div>
                <div class="text-muted small">
                    {{ $pengajuan->anggota->email ?? '-' }} @if($pengajuan->anggota->no_telepon) • {{ $pengajuan->anggota->no_telepon }} @endif
                </div>
            </div>

            <!-- Jenis / Periode -->
            <div class="mb-3 pt-3 border-top" style="border-color: var(--np-line) !important;">
                <div class="text-muted small fw-semibold">Jenis / Periode</div>
                <div class="fw-bold" style="color: var(--np-ink); font-size: 14px;">
                    {{ $pengajuan->label_jenis }} / {{ \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->translatedFormat('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($pengajuan->tanggal_selesai)->translatedFormat('d-m-Y') }}
                </div>
                <div class="text-muted small mt-1">
                    Total Durasi: {{ \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($pengajuan->tanggal_selesai)) + 1 }} Hari • Diajukan {{ \Carbon\Carbon::parse($pengajuan->created_at)->translatedFormat('d F Y, H:i') }} WITA
                </div>
            </div>

            <!-- Uraian / Alasan -->
            <div class="mb-3 pt-3 border-top" style="border-color: var(--np-line) !important;">
                <div class="text-muted small fw-semibold mb-1">Uraian Keterangan / Alasan</div>
                <div class="p-3 rounded-2 small" style="background-color: var(--np-bg); border: 1px solid var(--np-line); color: var(--np-ink); white-space: pre-line;">
                    {{ $pengajuan->keterangan }}
                </div>
            </div>

            <!-- Riwayat Verifikasi Jika Pernah Diproses -->
            @if($pengajuan->id_admin)
                <div class="mb-3 pt-3 border-top" style="border-color: var(--np-line) !important;">
                    <div class="p-3 rounded-2 small {{ $pengajuan->status_pengajuan === 'disetujui' ? 'bg-success-subtle text-success-emphasis border border-success' : 'bg-danger-subtle text-danger-emphasis border border-danger' }}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong>Diverifikasi oleh: {{ $pengajuan->admin->nama ?? 'Admin' }}</strong>
                            <span class="text-muted" style="font-size: 11px;">{{ \Carbon\Carbon::parse($pengajuan->tanggal_diproses)->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                        @if($pengajuan->catatan_admin)
                            <div class="mt-2 pt-2 border-top">
                                <span class="fw-semibold">Catatan Admin:</span>
                                <div>{{ $pengajuan->catatan_admin }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Form Keputusan Verifikasi -->
            <form action="{{ route('admin.surat.proses', $pengajuan->id_pengajuan) }}" method="POST" id="formProsesSurat" class="pt-3 border-top" style="border-color: var(--np-line) !important;">
                @csrf

                <div class="mb-3">
                    <label class="form-label mb-2">
                        Keputusan Verifikasi <span class="text-danger">*</span>
                    </label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="status" id="statusSetuju" value="disetujui" 
                                   {{ old('status', $pengajuan->status_pengajuan) === 'disetujui' ? 'checked' : '' }} required>
                            <label class="btn w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2 decision-btn-setuju" for="statusSetuju" style="height: 48px; border-radius: 10px; border: 1px solid var(--np-line);">
                                <i class="bi bi-check-circle-fill"></i> Setujui
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="status" id="statusTolak" value="ditolak"
                                   {{ old('status', $pengajuan->status_pengajuan) === 'ditolak' ? 'checked' : '' }} required>
                            <label class="btn w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2 decision-btn-tolak" for="statusTolak" style="height: 48px; border-radius: 10px; border: 1px solid var(--np-line);">
                                <i class="bi bi-x-circle-fill"></i> Tolak
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="catatan_admin" class="form-label mb-1">
                        Catatan Admin <span id="labelWajibTolak" class="text-danger d-none">*</span>
                        <span id="labelOpsional" class="text-muted fw-normal">(Opsional)</span>
                    </label>
                    <textarea name="catatan_admin" id="catatan_admin" rows="3" class="form-control @error('catatan_admin') is-invalid @enderror" 
                              placeholder="Tuliskan catatan verifikasi atau alasan penolakan permohonan...">{{ old('catatan_admin', $pengajuan->catatan_admin) }}</textarea>
                    <div class="text-muted mt-1" style="font-size: 12px;">Catatan ini akan dapat dibaca langsung oleh personel.</div>
                </div>

                <button type="submit" class="btn np-btn-primary w-100 fw-bold" style="height: 48px;">
                    Simpan Keputusan
                </button>
            </form>
        </div>
    </div>

    <!-- Kolom Kanan: Pratinjau Lampiran PDF -->
    <div class="col-12 col-lg-7">
        <div class="np-card p-4 h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h2 class="h2-navy mb-0" style="font-size: 18px;">Pratinjau Lampiran PDF</h2>
                <a href="{{ route('admin.surat.file', $pengajuan->id_pengajuan) }}" target="_blank" class="btn np-btn-outline btn-sm d-inline-flex align-items-center gap-1" style="height: 38px;">
                    <i class="bi bi-box-arrow-up-right"></i> Buka Dokumen PDF
                </a>
            </div>

            <div class="flex-grow-1 border rounded-2 overflow-hidden bg-white" style="min-height: 580px; border-color: var(--np-line) !important;">
                <iframe src="{{ route('admin.surat.file', $pengajuan->id_pengajuan) }}#toolbar=1" 
                        title="Pratinjau Surat PDF" 
                        style="width: 100%; height: 100%; min-height: 580px; border: none;">
                    <div class="p-4 text-center text-muted">
                        Peramban Anda tidak mendukung pratinjau PDF langsung.
                        <a href="{{ route('admin.surat.file', $pengajuan->id_pengajuan) }}" target="_blank" class="btn np-btn-primary btn-sm mt-3 d-inline-block">
                            Buka Dokumen PDF
                        </a>
                    </div>
                </iframe>
            </div>
        </div>
    </div>
</div>

<style>
    /* Radio Decision Button Active States */
    .btn-check:checked + label.decision-btn-setuju {
        background-color: var(--np-primary) !important;
        color: #FFFFFF !important;
        border-color: var(--np-primary) !important;
    }
    .btn-check:not(:checked) + label.decision-btn-setuju {
        background-color: var(--np-surface);
        color: var(--np-ink);
    }
    .btn-check:checked + label.decision-btn-tolak {
        background-color: var(--np-danger) !important;
        color: #FFFFFF !important;
        border-color: var(--np-danger) !important;
    }
    .btn-check:not(:checked) + label.decision-btn-tolak {
        background-color: var(--np-surface);
        color: var(--np-ink);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusSetuju = document.getElementById('statusSetuju');
        const statusTolak = document.getElementById('statusTolak');
        const catatanAdmin = document.getElementById('catatan_admin');
        const labelWajib = document.getElementById('labelWajibTolak');
        const labelOpsional = document.getElementById('labelOpsional');

        function updateCatatanRequirement() {
            if (statusTolak && statusTolak.checked) {
                catatanAdmin.required = true;
                if (labelWajib) labelWajib.classList.remove('d-none');
                if (labelOpsional) labelOpsional.classList.add('d-none');
            } else {
                catatanAdmin.required = false;
                if (labelWajib) labelWajib.classList.add('d-none');
                if (labelOpsional) labelOpsional.classList.remove('d-none');
            }
        }

        if (statusSetuju) statusSetuju.addEventListener('change', updateCatatanRequirement);
        if (statusTolak) statusTolak.addEventListener('change', updateCatatanRequirement);
        updateCatatanRequirement();
    });
</script>
@endsection

@extends('layouts.admin')

@section('title', 'Detail Pengajuan Surat - ' . ($pengajuan->anggota->nama ?? 'Personel'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold text-dark mb-0">Detail Pengajuan Surat</h3>
        <p class="text-muted small mb-0">
            Periksa berkas PDF dan tentukan status verifikasi permohonan keterangan personel.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.surat.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<!-- Alert Pesan Error -->
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show small mb-4" role="alert">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Kolom Kiri: Informasi Personel & Pengajuan & Form Aksi -->
    <div class="col-12 col-lg-5">
        <!-- Kartu Identitas Personel -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-person-badge-fill me-1 text-primary"></i> Data Personel
                </h6>
                <span class="badge bg-light text-secondary border font-monospace">NRP: {{ $pengajuan->anggota->nrp ?? '-' }}</span>
            </div>
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">{{ $pengajuan->anggota->nama ?? '-' }}</h6>
                        <div class="small text-secondary">{{ $pengajuan->anggota->jabatan ?? '-' }}</div>
                        <div class="small text-muted">{{ $pengajuan->anggota->unit_kerja ?? '-' }}</div>
                    </div>
                </div>

                <div class="list-group list-group-flush border-top small">
                    <div class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted">Email Kedinasan:</span>
                        <span class="fw-semibold text-dark">{{ $pengajuan->anggota->email ?? '-' }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted">Nomor Telepon / WA:</span>
                        <span class="fw-semibold text-dark">{{ $pengajuan->anggota->no_telepon ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu Detail Permohonan -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-file-earmark-text-fill me-1 text-warning"></i> Detail Keterangan
                </h6>
                {!! $pengajuan->status_badge !!}
            </div>
            <div class="card-body p-3">
                <div class="list-group list-group-flush small">
                    <div class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted">Jenis Keterangan:</span>
                        <span class="badge bg-primary text-uppercase px-2 py-1">{{ $pengajuan->label_jenis }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted">Periode Tanggal:</span>
                        <span class="fw-semibold text-dark">
                            {{ \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->translatedFormat('d M Y') }}
                            s/d
                            {{ \Carbon\Carbon::parse($pengajuan->tanggal_selesai)->translatedFormat('d M Y') }}
                        </span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted">Total Durasi:</span>
                        <span class="fw-semibold text-dark">
                            {{ \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($pengajuan->tanggal_selesai)) + 1 }} Hari
                        </span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between py-2 px-0">
                        <span class="text-muted">Diajukan Pada:</span>
                        <span class="text-dark">
                            {{ \Carbon\Carbon::parse($pengajuan->created_at)->translatedFormat('d F Y, H:i') }} WITA
                        </span>
                    </div>
                </div>

                <div class="mt-3 pt-2 border-top">
                    <label class="form-label small fw-bold text-secondary mb-1">Uraian / Alasan Personel:</label>
                    <div class="p-3 bg-light rounded-3 border small text-dark" style="white-space: pre-line;">
                        {{ $pengajuan->keterangan }}
                    </div>
                </div>

                <!-- Info Verifikasi Jika Sudah Diproses -->
                @if($pengajuan->id_admin)
                    <div class="mt-3 pt-2 border-top small">
                        <div class="p-3 rounded {{ $pengajuan->status_pengajuan === 'disetujui' ? 'bg-success bg-opacity-10 border border-success-subtle' : 'bg-danger bg-opacity-10 border border-danger-subtle' }}">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-dark">Diverifikasi Oleh: {{ $pengajuan->admin->nama ?? 'Admin' }}</strong>
                                <span class="text-muted" style="font-size: 0.72rem;">{{ \Carbon\Carbon::parse($pengajuan->tanggal_diproses)->translatedFormat('d M Y, H:i') }}</span>
                            </div>
                            @if($pengajuan->catatan_admin)
                                <div class="mt-1">
                                    <span class="text-secondary fw-semibold">Catatan Admin:</span>
                                    <div class="text-dark mt-1">{{ $pengajuan->catatan_admin }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Panel Form Verifikasi Admin -->
        <div class="card border-0 shadow-sm rounded-3 border-top border-4 border-primary">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-shield-check me-1 text-primary"></i> Tindakan Verifikasi Admin
                </h6>
            </div>
            <div class="card-body p-3">
                <form action="{{ route('admin.surat.proses', $pengajuan->id_pengajuan) }}" method="POST" id="formProsesSurat">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">
                            Keputusan Verifikasi <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="status" id="statusSetuju" value="disetujui" 
                                       {{ old('status', $pengajuan->status_pengajuan) === 'disetujui' ? 'checked' : '' }} required>
                                <label class="btn btn-outline-success w-100 py-2 d-flex flex-column align-items-center" for="statusSetuju">
                                    <i class="bi bi-check-circle-fill fs-5 mb-1"></i>
                                    <span class="fw-bold small">Setujui Surat</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="status" id="statusTolak" value="ditolak"
                                       {{ old('status', $pengajuan->status_pengajuan) === 'ditolak' ? 'checked' : '' }} required>
                                <label class="btn btn-outline-danger w-100 py-2 d-flex flex-column align-items-center" for="statusTolak">
                                    <i class="bi bi-x-circle-fill fs-5 mb-1"></i>
                                    <span class="fw-bold small">Tolak Surat</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="catatan_admin" class="form-label small fw-bold text-secondary mb-1">
                            Catatan Admin <span id="labelWajibTolak" class="text-danger d-none">(Wajib diisi jika ditolak)</span>
                        </label>
                        <textarea name="catatan_admin" id="catatan_admin" rows="3" class="form-control form-control-sm @error('catatan_admin') is-invalid @enderror" 
                                  placeholder="Tuliskan catatan verifikasi atau alasan penolakan permohonan...">{{ old('catatan_admin', $pengajuan->catatan_admin) }}</textarea>
                        <small class="text-muted" style="font-size: 0.72rem;">Catatan ini akan dapat dibaca langsung oleh personel di aplikasi presensi mereka.</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm" style="background-color: #0e2238; border-color: #0e2238;">
                        <i class="bi bi-save me-1"></i> Simpan Keputusan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Pratinjau Dokumen PDF -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-file-earmark-pdf-fill me-1 text-danger"></i> Pratinjau Berkas Lampiran PDF
                </h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.surat.file', $pengajuan->id_pengajuan) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Tab Baru
                    </a>
                </div>
            </div>
            <div class="card-body p-2 bg-light">
                <div class="ratio ratio-1x1 border rounded-2 overflow-hidden bg-white shadow-sm" style="min-height: 620px;">
                    <iframe src="{{ route('admin.surat.file', $pengajuan->id_pengajuan) }}#toolbar=1" 
                            title="Pratinjau Surat PDF" 
                            style="width: 100%; height: 100%; border: none;">
                        <p class="p-4 text-center text-muted">
                            Peramban Anda tidak mendukung pratinjau PDF langsung. 
                            <a href="{{ route('admin.surat.file', $pengajuan->id_pengajuan) }}" target="_blank" class="btn btn-primary btn-sm mt-2">
                                Unduh / Buka Dokumen PDF
                            </a>
                        </p>
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusSetuju = document.getElementById('statusSetuju');
        const statusTolak = document.getElementById('statusTolak');
        const catatanAdmin = document.getElementById('catatan_admin');
        const labelWajib = document.getElementById('labelWajibTolak');

        function updateCatatanRequirement() {
            if (statusTolak.checked) {
                catatanAdmin.required = true;
                labelWajib.classList.remove('d-none');
            } else {
                catatanAdmin.required = false;
                labelWajib.classList.add('d-none');
            }
        }

        statusSetuju.addEventListener('change', updateCatatanRequirement);
        statusTolak.addEventListener('change', updateCatatanRequirement);
        updateCatatanRequirement();
    });
</script>
@endsection


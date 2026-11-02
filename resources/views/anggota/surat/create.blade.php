@extends('layouts.anggota')

@section('title', 'Form Pengajuan Surat')

@section('content')
<div class="card card-custom p-4 presensi-card">
    <!-- Header Halaman -->
    <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-file-earmark-plus-fill text-warning me-1"></i> Form Pengajuan Surat
            </h5>
            <p class="text-muted small mb-0">
                Lengkapi data permohonan surat keterangan dan lampiran PDF
            </p>
        </div>
        <a href="{{ route('anggota.surat.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <!-- Error Validation Alert -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show small mb-3" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Mohon perbaiki data berikut:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('anggota.surat.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Jenis Keterangan -->
        <div class="mb-3">
            <label for="jenis_keterangan" class="form-label fw-bold text-dark small mb-1">
                Jenis Keterangan <span class="text-danger">*</span>
            </label>
            <select name="jenis_keterangan" id="jenis_keterangan" class="form-select @error('jenis_keterangan') is-invalid @enderror" required>
                <option value="" disabled {{ old('jenis_keterangan') ? '' : 'selected' }}>-- Pilih Jenis Keterangan --</option>
                @foreach($daftarJenis as $key => $label)
                    <option value="{{ $key }}" {{ old('jenis_keterangan') === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Rentang Tanggal -->
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label for="tanggal_mulai" class="form-label fw-bold text-dark small mb-1">
                    Tanggal Mulai <span class="text-danger">*</span>
                </label>
                <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                       id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required>
            </div>
            <div class="col-6">
                <label for="tanggal_selesai" class="form-label fw-bold text-dark small mb-1">
                    Tanggal Selesai <span class="text-danger">*</span>
                </label>
                <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                       id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', date('Y-m-d')) }}" required>
            </div>
            <div class="col-12">
                <small class="text-muted" style="font-size: 0.75rem;">
                    <i class="bi bi-info-circle me-1"></i> Periode tidak boleh tumpang tindih dengan surat yang masih menunggu atau sudah disetujui.
                </small>
            </div>
        </div>

        <!-- Keterangan / Alasan -->
        <div class="mb-3">
            <label for="keterangan" class="form-label fw-bold text-dark small mb-1">
                Uraian Keterangan / Alasan <span class="text-danger">*</span>
            </label>
            <textarea name="keterangan" id="keterangan" rows="3" class="form-control @error('keterangan') is-invalid @enderror" 
                      placeholder="Jelaskan keperluan atau alasan pengajuan secara jelas..." required>{{ old('keterangan') }}</textarea>
            <small class="text-muted" style="font-size: 0.75rem;">Contoh: Cuti tahunan periode Semester I, atau Perintah dinas pengamanan di Polda.</small>
        </div>

        <!-- Unggah Dokumen PDF -->
        <div class="mb-4">
            <label for="file_surat" class="form-label fw-bold text-dark small mb-1">
                Unggah Berkas Surat Lampiran (PDF) <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light text-danger"><i class="bi bi-file-earmark-pdf"></i></span>
                <input type="file" class="form-control @error('file_surat') is-invalid @enderror" 
                       id="file_surat" name="file_surat" accept=".pdf,application/pdf" required>
            </div>
            <div class="p-2 bg-light rounded mt-2 border small text-muted" style="font-size: 0.75rem;">
                <i class="bi bi-shield-check text-success me-1"></i> Format wajib dokumen <strong>PDF (.pdf)</strong> asli. Ukuran maksimal <strong>{{ round($maxSizeKb / 1024, 1) }} MB</strong> ({{ $maxSizeKb }} KB). Berkas disimpan secara privat dan terlindungi.
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="d-flex gap-2 pt-2 border-top">
            <a href="{{ route('anggota.surat.index') }}" class="btn btn-light border flex-grow-1">
                Batal
            </a>
            <button type="submit" class="btn btn-primary flex-grow-1 shadow-sm" style="background-color: #0e2238; border-color: #0e2238;">
                <i class="bi bi-send-fill me-1"></i> Kirim Pengajuan
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tglMulai = document.getElementById('tanggal_mulai');
        const tglSelesai = document.getElementById('tanggal_selesai');

        if (tglMulai && tglSelesai) {
            tglMulai.addEventListener('change', function() {
                if (tglSelesai.value < tglMulai.value) {
                    tglSelesai.value = tglMulai.value;
                }
                tglSelesai.min = tglMulai.value;
            });
        }
    });
</script>
@endsection


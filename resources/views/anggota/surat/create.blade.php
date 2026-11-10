@extends('layouts.anggota')

@section('title', 'Form Pengajuan Surat')

@section('content')
<div class="mb-4">
    <h1 class="h1-navy">Form Pengajuan Surat</h1>
    <div class="subtitle-navy">Presensi Digital Bidtik Polda Kalimantan Selatan</div>
</div>

<!-- Error Validation Alert -->
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4" role="alert" style="background-color: #FEE2E2; color: #DC2626; border: 1px solid #FECACA !important;">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Mohon perbaiki data berikut:</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card np-card">
    <div class="card-body p-4">
        <form action="{{ route('anggota.surat.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Jenis Keterangan -->
            <div class="mb-3">
                <label for="jenis_keterangan" class="form-label">Jenis Keterangan *</label>
                <select name="jenis_keterangan" id="jenis_keterangan" class="form-select @error('jenis_keterangan') is-invalid @enderror" required>
                    @foreach($daftarJenis as $val => $label)
                        <option value="{{ $val }}" {{ old('jenis_keterangan', $jenisTerpilih ?? 'izin') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Rentang Tanggal (2 Kolom Desktop, 1 Kolom Mobile) -->
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai *</label>
                    <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                           id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required>
                </div>
                <div class="col-12 col-md-6">
                    <label for="tanggal_selesai" class="form-label">Tanggal Selesai *</label>
                    <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                           id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', date('Y-m-d')) }}" required>
                </div>
            </div>

            <!-- Uraian Keterangan / Alasan -->
            <div class="mb-3">
                <label for="keterangan" class="form-label">Uraian Keterangan / Alasan *</label>
                <textarea name="keterangan" id="keterangan" rows="4" class="form-control @error('keterangan') is-invalid @enderror" 
                          placeholder="Melaksanakan tugas kedinasan sesuai surat perintah." required>{{ old('keterangan') }}</textarea>
            </div>

            <!-- Berkas Surat Lampiran PDF -->
            <div class="mb-4">
                <label for="file_surat" class="form-label">Berkas Surat Lampiran PDF *</label>
                <input type="file" class="form-control @error('file_surat') is-invalid @enderror" 
                       id="file_surat" name="file_surat" accept=".pdf,application/pdf" required>
                <small class="text-muted d-block mt-2" style="font-size: 12px;">
                    PDF, maksimal {{ round($maxSizeKb / 1024, 0) }} MB. Wajib dilampirkan.<br>
                    Periode tidak boleh tumpang tindih dengan pengajuan menunggu atau disetujui.
                </small>
            </div>

            <!-- Tombol Aksi -->
            <div class="d-flex align-items-center gap-3">
                <button type="submit" class="btn btn-primary fw-bold px-4" style="min-height: 48px; border-radius: 10px;">
                    <i class="bi bi-send me-1"></i> Kirim Pengajuan Surat
                </button>
                <a href="{{ route('anggota.surat.index') }}" class="btn btn-outline-secondary fw-bold px-4" style="min-height: 48px; border-radius: 10px;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.anggota')

@section('title', 'Form Pengajuan Surat Personel')

@section('content')
<div class="card border-0 shadow-sm p-4 rounded-3 bg-white mb-4">
    <!-- Header Halaman -->
    <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-file-earmark-plus-fill text-warning me-1"></i> Form Pengajuan Surat
            </h5>
            <p class="text-muted small mb-0">
                Pilih kategori keterangan dan lampirkan berkas dokumen PDF
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

        <!-- PILIHAN KATEGORI SURAT (8 KARTU INTERAKTIF) -->
        <div class="mb-3">
            <label class="form-label fw-bold text-dark small mb-2 d-flex justify-content-between align-items-center">
                <span>Pilih Kategori Keterangan <span class="text-danger">*</span></span>
                <span class="text-primary small fw-semibold" id="kategoriTerpilihLabel">Terpilih: Izin</span>
            </label>

            <!-- Hidden input untuk disubmit ke backend -->
            <input type="hidden" name="jenis_keterangan" id="inputJenisKeterangan" value="{{ old('jenis_keterangan', $jenisTerpilih ?? 'izin') }}">

            <div class="row g-2">
                @foreach($kategoriMeta as $key => $meta)
                    @php
                        $isSelected = old('jenis_keterangan', $jenisTerpilih ?? 'izin') === $key;
                    @endphp
                    <div class="col-6 col-sm-6 col-md-3">
                        <div class="card h-100 p-2 category-select-card text-center position-relative shadow-sm {{ $isSelected ? 'border-primary active' : '' }}"
                             role="button"
                             id="card_kat_{{ $key }}"
                             onclick="pilihKategoriForm('{{ $key }}', '{{ $meta['nama'] }}', '{{ $meta['warna'] }}')"
                             style="cursor: pointer; border-radius: 12px; transition: all 0.2s ease; border: 2px solid {{ $isSelected ? $meta['warna'] : '#e2e8f0' }}; background: {{ $isSelected ? $meta['warna_bg'] : '#ffffff' }};">
                            
                            <!-- Checkmark jika terpilih -->
                            <div class="check-indicator position-absolute top-0 end-0 m-1" style="{{ $isSelected ? '' : 'display: none;' }}">
                                <i class="bi bi-check-circle-fill" style="color: {{ $meta['warna'] }}; font-size: 0.9rem;"></i>
                            </div>

                            <div class="d-flex justify-content-center my-1">
                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                     style="width: 38px; height: 38px; background-color: {{ $meta['warna'] }}; color: #ffffff;">
                                    <i class="bi {{ $meta['ikon'] }} fs-6"></i>
                                </div>
                            </div>

                            <span class="fw-bold d-block text-dark small" style="font-size: 0.85rem;">
                                {{ $meta['nama'] }}
                            </span>
                            <span class="text-muted d-block text-truncate" style="font-size: 0.68rem;" title="{{ $meta['deskripsi'] }}">
                                {{ $meta['deskripsi'] }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Rentang Tanggal -->
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label for="tanggal_mulai" class="form-label fw-bold text-dark small mb-1">
                    Tanggal Mulai <span class="text-danger">*</span>
                </label>
                <input type="date" class="form-control form-control-sm @error('tanggal_mulai') is-invalid @enderror" 
                       id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required>
            </div>
            <div class="col-6">
                <label for="tanggal_selesai" class="form-label fw-bold text-dark small mb-1">
                    Tanggal Selesai <span class="text-danger">*</span>
                </label>
                <input type="date" class="form-control form-control-sm @error('tanggal_selesai') is-invalid @enderror" 
                       id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', date('Y-m-d')) }}" required>
            </div>
            <div class="col-12">
                <small class="text-muted" style="font-size: 0.72rem;">
                    <i class="bi bi-info-circle me-1"></i> Periode tidak boleh tumpang tindih dengan surat yang masih menunggu atau sudah disetujui.
                </small>
            </div>
        </div>

        <!-- Keterangan / Alasan -->
        <div class="mb-3">
            <label for="keterangan" class="form-label fw-bold text-dark small mb-1">
                Uraian Keterangan / Alasan <span class="text-danger">*</span>
            </label>
            <textarea name="keterangan" id="keterangan" rows="3" class="form-control form-control-sm @error('keterangan') is-invalid @enderror" 
                      placeholder="Jelaskan keperluan atau alasan pengajuan secara jelas..." required>{{ old('keterangan') }}</textarea>
            <small class="text-muted" style="font-size: 0.72rem;">Contoh: Cuti tahunan periode Semester I, Perintah dinas Sprin Kapolda, atau Istirahat sakit dokter.</small>
        </div>

        <!-- Unggah Dokumen PDF -->
        <div class="mb-4">
            <label for="file_surat" class="form-label fw-bold text-dark small mb-1">
                Unggah Berkas Surat Lampiran (PDF) <span class="text-danger">*</span>
            </label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-danger"><i class="bi bi-file-earmark-pdf"></i></span>
                <input type="file" class="form-control @error('file_surat') is-invalid @enderror" 
                       id="file_surat" name="file_surat" accept=".pdf,application/pdf" required>
            </div>
            <div class="p-2 bg-light rounded mt-2 border small text-muted" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check text-success me-1"></i> Format berkas wajib dokumen <strong>PDF (.pdf)</strong> asli. Ukuran maksimal <strong>{{ round($maxSizeKb / 1024, 1) }} MB</strong> ({{ $maxSizeKb }} KB). Berkas disimpan secara privat dan terlindungi.
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <a href="{{ route('anggota.surat.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                Batal
            </a>
            <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold text-white shadow-sm" style="background-color: #0e2238; border-color: #0e2238;">
                <i class="bi bi-send-fill me-1 text-warning"></i> Kirim Pengajuan Surat
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .category-select-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
</style>
@endpush

@push('scripts')
<script>
    const kategoriMap = @json($kategoriMeta);

    function pilihKategoriForm(key, nama, warna) {
        document.getElementById('inputJenisKeterangan').value = key;
        document.getElementById('kategoriTerpilihLabel').innerText = 'Terpilih: ' + nama;
        document.getElementById('kategoriTerpilihLabel').style.color = warna;

        // Reset semua card
        document.querySelectorAll('.category-select-card').forEach(card => {
            card.style.border = '2px solid #e2e8f0';
            card.style.background = '#ffffff';
            const check = card.querySelector('.check-indicator');
            if (check) check.style.display = 'none';
        });

        // Set card terpilih
        const activeCard = document.getElementById('card_kat_' + key);
        if (activeCard && kategoriMap[key]) {
            activeCard.style.border = '2px solid ' + warna;
            activeCard.style.background = kategoriMap[key].warna_bg;
            const check = activeCard.querySelector('.check-indicator');
            if (check) check.style.display = 'block';
        }
    }
</script>
@endpush

@extends('layouts.anggota')

@section('title', 'Pengajuan Surat & Keterangan Personel')

@section('content')
<div class="card card-custom p-4 presensi-card">
    <!-- Header Halaman -->
    <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-3 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-file-earmark-text-fill text-warning me-1"></i> Pengajuan Surat & Keterangan
            </h5>
            <p class="text-muted small mb-0">
                Pilih kategori pengajuan surat dan lampirkan berkas dokumen PDF resmi
            </p>
        </div>
        <a href="{{ route('anggota.surat.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm d-none d-sm-inline-flex align-items-center gap-1" style="background-color: #0e2238; border-color: #0e2238;">
            <i class="bi bi-plus-circle"></i> Buat Pengajuan
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show small mb-3" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Pengajuan gagal, periksa isian:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- BAGIAN 1: KATEGORI PENGAJUAN SURAT (8 KARTU INTERAKTIF) -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <span class="fw-bold text-dark small text-uppercase letter-spacing-1">
                    <i class="bi bi-grid-fill text-primary me-1"></i> Kategori Surat (Pilih & Ajukan)
                </span>
                <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                    Klik salah satu kategori di bawah untuk langsung melampirkan berkas surat PDF:
                </p>
            </div>
        </div>

        <div class="row g-2">
            @foreach($kategoriMeta as $key => $meta)
                <div class="col-6 col-sm-6 col-md-3">
                    <div class="card h-100 p-2 category-card shadow-sm border text-center position-relative"
                         role="button"
                         tabindex="0"
                         onclick="bukaModalPengajuan('{{ $meta['key'] }}', '{{ $meta['nama'] }}', '{{ $meta['deskripsi'] }}', '{{ $meta['ikon'] }}', '{{ $meta['warna'] }}')"
                         onkeypress="if(event.key === 'Enter') bukaModalPengajuan('{{ $meta['key'] }}', '{{ $meta['nama'] }}', '{{ $meta['deskripsi'] }}', '{{ $meta['ikon'] }}', '{{ $meta['warna'] }}')"
                         style="cursor: pointer; border-radius: 12px; background: {{ $meta['warna_bg'] }}; border-color: {{ $meta['border'] }} !important; transition: all 0.2s ease;">
                        
                        <!-- Badge Jumlah Pengajuan jika ada -->
                        @if(!empty($jumlahPerJenis[$meta['key']]))
                            <span class="position-absolute top-0 end-0 badge rounded-pill bg-dark text-white m-1" style="font-size: 0.65rem;" title="{{ $jumlahPerJenis[$meta['key']] }} pengajuan riwayat">
                                {{ $jumlahPerJenis[$meta['key']] }}
                            </span>
                        @endif

                        <!-- Ikon Kategori -->
                        <div class="d-flex justify-content-center my-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                 style="width: 44px; height: 44px; background-color: {{ $meta['warna'] }}; color: #ffffff;">
                                <i class="bi {{ $meta['ikon'] }} fs-5"></i>
                            </div>
                        </div>

                        <!-- Nama Kategori -->
                        <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.9rem;">
                            {{ $meta['nama'] }}
                        </h6>

                        <!-- Deskripsi Ringkas -->
                        <p class="text-muted small mb-2 text-truncate" style="font-size: 0.7rem;" title="{{ $meta['deskripsi'] }}">
                            {{ $meta['deskripsi'] }}
                        </p>

                        <!-- Tombol Aksi Tambah Berkas Surat -->
                        <div class="mt-auto pt-1">
                            <span class="btn btn-sm w-100 fw-semibold text-white shadow-sm d-flex align-items-center justify-content-center gap-1"
                                  style="background-color: {{ $meta['warna'] }}; font-size: 0.72rem; border-radius: 8px; padding: 4px 6px;">
                                <i class="bi bi-file-earmark-plus"></i> + Unggah Surat
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <hr class="my-3 opacity-25">

    <!-- BAGIAN 2: RIWAYAT PENGAJUAN SURAT -->
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <h6 class="fw-bold text-dark mb-0">
            <i class="bi bi-clock-history text-secondary me-1"></i> Riwayat Pengajuan Saya
        </h6>
        <span class="text-muted small" style="font-size: 0.75rem;">
            Total: {{ $daftarSurat->total() }} Pengajuan
        </span>
    </div>

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
                    <option value="">Semua Kategori</option>
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
        <div class="text-center py-4 text-muted bg-light rounded-3 border">
            <i class="bi bi-file-earmark-text fs-2 d-block mb-2 text-secondary opacity-50"></i>
            <h6 class="fw-bold mb-1">Belum Ada Pengajuan Surat</h6>
            <p class="small mb-2">Silakan pilih kategori di atas untuk mengajukan surat izin, cuti, dinas, atau sakit.</p>
        </div>
    @endforelse

    <!-- Pagination -->
    @if($daftarSurat->hasPages())
        <div class="mt-3">
            {{ $daftarSurat->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<!-- MODAL FORM PENGAJUAN SURAT (LANGSUNG DENGAN UPLOAD PDF) -->
<div class="modal fade" id="modalPengajuanSurat" tabindex="-1" aria-labelledby="modalPengajuanSuratLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0" style="border-radius: 16px; overflow: hidden;">
            <!-- Modal Header Polri Theme -->
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #091624 0%, #0e2238 100%); border-bottom: 2px solid #e5a93c;">
                <div class="d-flex align-items-center gap-2">
                    <div id="modalHeaderIconBg" class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: #2563eb;">
                        <i id="modalHeaderIcon" class="bi bi-clipboard-check-fill text-white fs-6"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0" id="modalPengajuanSuratLabel">
                            Pengajuan Surat <span id="modalHeaderTitleKategori" class="text-warning">Izin</span>
                        </h6>
                        <small class="text-white-50" style="font-size: 0.7rem;">Sistem Presensi Bidtik Polda Kalsel</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body Form -->
            <form action="{{ route('anggota.surat.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="jenis_keterangan" id="modalJenisKeteranganInput" value="izin">

                <div class="modal-body p-3 p-md-4">
                    <!-- Banner Kategori Terpilih -->
                    <div class="p-2 mb-3 rounded-3 d-flex align-items-center justify-content-between" id="modalCategoryBanner" style="background: #eff6ff; border: 1px solid #bfdbfe;">
                        <div class="d-flex align-items-center gap-2">
                            <i id="modalBannerIcon" class="bi bi-clipboard-check-fill fs-5" style="color: #2563eb;"></i>
                            <div>
                                <strong id="modalBannerNama" class="d-block text-dark small">Kategori: Izin</strong>
                                <span id="modalBannerDeskripsi" class="text-muted" style="font-size: 0.72rem;">Keperluan dinas / pribadi penting</span>
                            </div>
                        </div>
                        <span class="badge bg-primary text-uppercase" id="modalBannerBadge" style="font-size: 0.7rem;">
                            Izin
                        </span>
                    </div>

                    <!-- Rentang Tanggal -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="modal_tanggal_mulai" class="form-label fw-bold text-dark small mb-1">
                                Tanggal Mulai <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control form-control-sm" 
                                   id="modal_tanggal_mulai" name="tanggal_mulai" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label for="modal_tanggal_selesai" class="form-label fw-bold text-dark small mb-1">
                                Tanggal Selesai <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control form-control-sm" 
                                   id="modal_tanggal_selesai" name="tanggal_selesai" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <small class="text-muted" style="font-size: 0.72rem;">
                                <i class="bi bi-info-circle me-1"></i> Tanggal tidak boleh tumpang tindih dengan pengajuan aktif.
                            </small>
                        </div>
                    </div>

                    <!-- Keterangan / Alasan -->
                    <div class="mb-3">
                        <label for="modal_keterangan" class="form-label fw-bold text-dark small mb-1">
                            Uraian Keterangan / Alasan <span class="text-danger">*</span>
                        </label>
                        <textarea name="keterangan" id="modal_keterangan" rows="3" class="form-control form-control-sm" 
                                  placeholder="Jelaskan alasan atau keperluan pengajuan surat..." required></textarea>
                    </div>

                    <!-- Unggah Berkas PDF -->
                    <div class="mb-2">
                        <label for="modal_file_surat" class="form-label fw-bold text-dark small mb-1">
                            Unggah Berkas Surat Lampiran (PDF) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-danger"><i class="bi bi-file-earmark-pdf"></i></span>
                            <input type="file" class="form-control" 
                                   id="modal_file_surat" name="file_surat" accept=".pdf,application/pdf" required>
                        </div>
                        <div class="p-2 bg-light rounded mt-2 border small text-muted" style="font-size: 0.72rem;">
                            <i class="bi bi-shield-check text-success me-1"></i> Format berkas wajib <strong>PDF (.pdf)</strong> asli. Ukuran maksimal <strong>{{ round($maxSizeKb / 1024, 1) }} MB</strong>. Disimpan secara privat dan terlindungi.
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light p-2 px-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold text-white shadow-sm" style="background-color: #0e2238; border-color: #0e2238;">
                        <i class="bi bi-send-fill me-1 text-warning"></i> Kirim Pengajuan Surat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .category-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    function bukaModalPengajuan(key, nama, deskripsi, ikon, warna) {
        // Set input hidden jenis
        document.getElementById('modalJenisKeteranganInput').value = key;
        
        // Update header modal
        document.getElementById('modalHeaderTitleKategori').innerText = nama;
        document.getElementById('modalHeaderIcon').className = 'bi ' + ikon + ' text-white fs-6';
        document.getElementById('modalHeaderIconBg').style.backgroundColor = warna;
        
        // Update banner kategori di dalam form
        document.getElementById('modalBannerNama').innerText = 'Kategori: ' + nama;
        document.getElementById('modalBannerDeskripsi').innerText = deskripsi;
        document.getElementById('modalBannerBadge').innerText = nama;
        document.getElementById('modalBannerBadge').style.backgroundColor = warna;
        document.getElementById('modalBannerIcon').className = 'bi ' + ikon + ' fs-5';
        document.getElementById('modalBannerIcon').style.color = warna;

        // Reset file input & textarea
        const fileInput = document.getElementById('modal_file_surat');
        if (fileInput) fileInput.value = '';
        
        // Buka modal Bootstrap
        const modalEl = document.getElementById('modalPengajuanSurat');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
</script>
@endpush

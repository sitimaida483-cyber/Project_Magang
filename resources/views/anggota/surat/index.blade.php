@extends('layouts.anggota')

@section('title', 'Pengajuan Surat Keterangan')

@section('content')
<!-- Header Halaman -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h1 class="h1-navy">Pengajuan Surat</h1>
        <div class="subtitle-navy mb-0">Presensi Digital Bidtik Polda Kalimantan Selatan</div>
    </div>
    <div>
        <button type="button" class="btn btn-primary fw-bold" onclick="bukaModalPengajuan('izin', 'Izin', 'Keperluan dinas / pribadi penting', 'bi-file-earmark-text', '#254BD6')">
            Buat Pengajuan Baru
        </button>
    </div>
</div>

<!-- Alert Notifikasi Flash Message -->
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

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4" role="alert" style="background-color: #FEE2E2; color: #DC2626; border: 1px solid #FECACA !important;">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Mohon perbaiki data pengajuan:</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- BAGIAN 1: PILIH JENIS SURAT (8 KARTU KATEGORI PILIHAN A) -->
<div class="mb-4">
    <div class="fw-bold text-dark mb-3 fs-6">Pilih Jenis Surat</div>
    <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 g-3">
        @foreach($kategoriMeta as $key => $meta)
            <div class="col">
                <div class="card np-card p-3 mb-0 h-100 text-decoration-none" role="button"
                     onclick="bukaModalPengajuan('{{ $meta['key'] }}', '{{ $meta['nama'] }}', '{{ $meta['deskripsi'] }}', 'bi-file-earmark-text', '#254BD6')"
                     style="cursor: pointer; min-height: 90px; display: flex; flex-direction: column; justify-content: center;">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-file-earmark-text fs-4 text-muted"></i>
                        <span class="fw-bold text-dark fs-6">{{ $meta['nama'] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- BAGIAN 2: FILTER RIWAYAT PENGAJUAN -->
<div class="card np-card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('anggota.surat.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label">Jenis</label>
                    <select name="jenis" class="form-select">
                        <option value="">Semua jenis</option>
                        @foreach($daftarJenis as $val => $label)
                            <option value="{{ $val }}" {{ request('jenis') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control" value="{{ request('tanggal_selesai') }}">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- BAGIAN 3: RIWAYAT PENGAJUAN SAYA (TABEL DESKTOP & KARTU REFLOW MOBILE) -->
<div class="mb-4">
    <div class="fw-bold text-dark mb-3 fs-6">Riwayat Pengajuan Saya</div>

    <!-- Desktop Table (>= 768px) -->
    <div class="table-np-container d-none d-md-block">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th style="width: 30%;">JENIS KETERANGAN</th>
                    <th style="width: 25%;">PERIODE</th>
                    <th style="width: 20%;">STATUS</th>
                    <th class="text-end" style="width: 25%;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftarSurat as $surat)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $surat->label_jenis }}</div>
                            <small class="text-muted">Diajukan {{ \Carbon\Carbon::parse($surat->created_at)->translatedFormat('d M Y') }}</small>
                        </td>
                        <td>
                            <div class="text-dark fw-semibold">
                                {{ \Carbon\Carbon::parse($surat->tanggal_mulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($surat->tanggal_selesai)->format('d/m/Y') }}
                            </div>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($surat->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($surat->tanggal_selesai)) + 1 }} hari
                            </small>
                        </td>
                        <td>
                            @if($surat->status_pengajuan === 'disetujui')
                                <span class="badge badge-np-success">Disetujui</span>
                            @elseif($surat->status_pengajuan === 'ditolak')
                                <span class="badge badge-np-danger">Ditolak</span>
                            @else
                                <span class="badge badge-np-warning">Menunggu</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('anggota.surat.file', $surat->id_pengajuan) }}" target="_blank" class="btn btn-sm btn-np-outline">
                                    Buka PDF
                                </a>
                                @if($surat->status_pengajuan === 'menunggu')
                                    <button type="button" class="btn btn-sm btn-np-outline text-danger border-danger" 
                                            onclick="konfirmasiBatalSurat('{{ $surat->id_pengajuan }}', '{{ $surat->label_jenis }}', '{{ \Carbon\Carbon::parse($surat->tanggal_mulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($surat->tanggal_selesai)->format('d/m/Y') }}')">
                                        Batal
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-file-earmark-text fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <h6 class="fw-bold mb-1">Belum Ada Pengajuan Surat</h6>
                            <p class="small mb-0">Pengajuan surat yang Anda kirim akan muncul di sini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards (< 768px) -->
    <div class="d-md-none">
        @forelse($daftarSurat as $surat)
            <div class="card np-card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    @if($surat->status_pengajuan === 'disetujui')
                        <span class="badge badge-np-success">Disetujui</span>
                    @elseif($surat->status_pengajuan === 'ditolak')
                        <span class="badge badge-np-danger">Ditolak</span>
                    @else
                        <span class="badge badge-np-warning">Menunggu</span>
                    @endif
                    <small class="text-muted">Diajukan {{ \Carbon\Carbon::parse($surat->created_at)->translatedFormat('d M Y') }}</small>
                </div>
                <div class="fw-bold text-dark fs-6 mb-1">{{ $surat->label_jenis }}</div>
                <div class="text-muted small mb-2">
                    {{ \Carbon\Carbon::parse($surat->tanggal_mulai)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($surat->tanggal_selesai)->translatedFormat('d M Y') }}
                </div>
                <div class="text-secondary small mb-3">
                    PDF terlampir / {{ \Carbon\Carbon::parse($surat->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($surat->tanggal_selesai)) + 1 }} hari
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('anggota.surat.file', $surat->id_pengajuan) }}" target="_blank" class="btn btn-sm btn-np-outline flex-grow-1">
                        Buka PDF
                    </a>
                    @if($surat->status_pengajuan === 'menunggu')
                        <button type="button" class="btn btn-sm btn-np-outline text-danger border-danger flex-grow-1" 
                                onclick="konfirmasiBatalSurat('{{ $surat->id_pengajuan }}', '{{ $surat->label_jenis }}', '{{ \Carbon\Carbon::parse($surat->tanggal_mulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($surat->tanggal_selesai)->format('d/m/Y') }}')">
                            Batalkan
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="card np-card p-4 text-center text-muted">
                <h6 class="fw-bold mb-1">Belum Ada Pengajuan Surat</h6>
                <p class="small mb-0">Pengajuan surat yang Anda kirim akan muncul di sini.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination Footer -->
    @if($daftarSurat->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            @if($daftarSurat->onFirstPage())
                <button class="btn btn-sm btn-np-outline" disabled>Sebelumnya</button>
            @else
                <a href="{{ $daftarSurat->previousPageUrl() }}" class="btn btn-sm btn-np-outline">Sebelumnya</a>
            @endif

            <span class="small text-muted">Halaman {{ $daftarSurat->currentPage() }} dari {{ $daftarSurat->lastPage() }}</span>

            @if($daftarSurat->hasMorePages())
                <a href="{{ $daftarSurat->nextPageUrl() }}" class="btn btn-sm btn-np-outline">Berikutnya</a>
            @else
                <button class="btn btn-sm btn-np-outline" disabled>Berikutnya</button>
            @endif
        </div>
    @endif
</div>
@endsection

@push('modals')
<!-- MODAL FORM PENGAJUAN SURAT (PILIHAN A: NAVY PRECISION) -->
<div class="modal fade" id="modalPengajuanSurat" tabindex="-1" aria-labelledby="modalPengajuanSuratLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content border-0">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="modalPengajuanSuratLabel">
                    Pengajuan Surat: <span id="modalHeaderTitleKategori">Izin</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('anggota.surat.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="jenis_keterangan" id="modalJenisKeteranganInput" value="izin">

                <div class="modal-body p-4">
                    <!-- Kategori Terpilih -->
                    <div class="p-3 mb-3 rounded-2" style="background-color: var(--np-soft); border: 1px solid var(--np-line);">
                        <small class="text-muted d-block" style="font-size: 11px; font-weight: 700; text-transform: uppercase;">Kategori Terpilih</small>
                        <strong class="text-dark fs-6" id="modalBannerNama">Izin</strong>
                    </div>

                    <!-- Tanggal Mulai & Selesai (2 Kolom Desktop, 1 Kolom Mobile) -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="modal_tanggal_mulai" class="form-label">
                                Tanggal Mulai *
                            </label>
                            <input type="date" class="form-control" 
                                   id="modal_tanggal_mulai" name="tanggal_mulai" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="modal_tanggal_selesai" class="form-label">
                                Tanggal Selesai *
                            </label>
                            <input type="date" class="form-control" 
                                   id="modal_tanggal_selesai" name="tanggal_selesai" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <!-- Uraian Keterangan / Alasan -->
                    <div class="mb-3">
                        <label for="modal_keterangan" class="form-label">
                            Uraian Keterangan / Alasan *
                        </label>
                        <textarea name="keterangan" id="modal_keterangan" rows="3" class="form-control" 
                                  placeholder="Melaksanakan tugas kedinasan sesuai surat perintah." required></textarea>
                    </div>

                    <!-- Berkas Surat Lampiran PDF -->
                    <div class="mb-3">
                        <label for="modal_file_surat" class="form-label">
                            Berkas Surat Lampiran PDF *
                        </label>
                        <input type="file" class="form-control" 
                               id="modal_file_surat" name="file_surat" accept=".pdf,application/pdf" required>
                        <small class="text-muted d-block mt-2" style="font-size: 12px;">
                            PDF, maksimal {{ round($maxSizeKb / 1024, 0) }} MB. Wajib dilampirkan.<br>
                            Periode tidak boleh tumpang tindih dengan pengajuan menunggu atau disetujui.
                        </small>
                    </div>

                    <!-- Tombol Aksi Form -->
                    <button type="submit" class="btn btn-primary w-100 fw-bold mb-2">
                        Kirim Pengajuan Surat
                    </button>
                    <button type="button" class="btn btn-outline-secondary w-100 fw-bold" data-bs-dismiss="modal">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI PEMBATALAN SURAT (PAGE 42 NAVY PRECISION) -->
<div class="modal fade" id="modalBatalSurat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0">
            <div class="modal-body p-4 text-center">
                <h4 class="fw-bold text-dark mb-2">Batalkan Pengajuan?</h4>
                <div class="mb-3">
                    <span class="badge badge-np-warning">Menunggu</span>
                </div>
                <p class="text-muted small mb-4" id="modalBatalDeskripsi">
                    Pengajuan surat beserta berkas PDF akan dihapus dari sistem.
                </p>

                <form id="formBatalSurat" method="POST" action="">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100 fw-bold mb-2">
                        Ya, Batalkan Pengajuan
                    </button>
                    <button type="button" class="btn btn-outline-secondary w-100 fw-bold" data-bs-dismiss="modal">
                        Kembali
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function bukaModalPengajuan(key, nama, deskripsi, ikon, warna) {
        document.getElementById('modalJenisKeteranganInput').value = key;
        document.getElementById('modalHeaderTitleKategori').innerText = nama;
        document.getElementById('modalBannerNama').innerText = nama;

        const fileInput = document.getElementById('modal_file_surat');
        if (fileInput) fileInput.value = '';
        
        const modalEl = document.getElementById('modalPengajuanSurat');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    function konfirmasiBatalSurat(idPengajuan, labelJenis, periode) {
        const formEl = document.getElementById('formBatalSurat');
        if (formEl) {
            formEl.action = '/surat/' + idPengajuan + '/batal';
        }
        const descEl = document.getElementById('modalBatalDeskripsi');
        if (descEl) {
            descEl.innerText = 'Pengajuan ' + labelJenis + ' ' + periode + ' beserta berkas PDF akan dihapus dari sistem.';
        }
        const modalEl = document.getElementById('modalBatalSurat');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
</script>
@endpush

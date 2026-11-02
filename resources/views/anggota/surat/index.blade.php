@extends('layouts.anggota')

@section('title', 'Pengajuan Surat Keterangan')

@section('content')
<!-- Header Halaman -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold text-dark mb-0">Pengajuan Surat Keterangan</h3>
        <p class="text-muted small mb-0">
            <i class="bi bi-shield-check text-primary me-1"></i> Ajukan surat izin, sakit, dinas, cuti, dan upload berkas dokumen PDF resmi
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('presensi.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-geo-alt me-1"></i> Buka Presensi GPS
        </a>
        <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" onclick="bukaModalPengajuan('izin', 'Izin', 'Keperluan dinas / pribadi penting', 'bi-clipboard-check-fill', '#2563eb')" style="background-color: #0e2238; border-color: #0e2238;">
            <i class="bi bi-plus-circle me-1 text-warning"></i> + Buat Pengajuan Baru
        </button>
    </div>
</div>

<!-- Alert Notifikasi Flash Message -->
@if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('sukses') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Mohon perbaiki data pengajuan:</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- BAGIAN 1: 8 KARTU KATEGORI (FORMAT STAT CARDS ADMIN DASHBOARD) -->
<div class="mb-2">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="text-muted small text-uppercase fw-bold letter-spacing-1">
            <i class="bi bi-grid-fill text-primary me-1"></i> Kategori Surat (Klik Kartu untuk Langsung Ajukan)
        </span>
    </div>

    @php
        $cardColorMap = [
            'izin'        => ['border' => 'border-primary',   'text' => 'text-primary',   'bg' => 'bg-primary'],
            'sakit'       => ['border' => 'border-danger',    'text' => 'text-danger',    'bg' => 'bg-danger'],
            'surat_dinas' => ['border' => 'border-dark',      'text' => 'text-dark',      'bg' => 'bg-dark'],
            'cuti'        => ['border' => 'border-success',   'text' => 'text-success',   'bg' => 'bg-success'],
            'piket'       => ['border' => 'border-warning',   'text' => 'text-warning',   'bg' => 'bg-warning'],
            'lepas_dinas' => ['border' => 'border-secondary', 'text' => 'text-secondary', 'bg' => 'bg-secondary'],
            'wfh'         => ['border' => 'border-info',      'text' => 'text-info',      'bg' => 'bg-info'],
            'pendidikan'  => ['border' => 'border-primary',   'text' => 'text-primary',   'bg' => 'bg-primary'],
        ];
    @endphp

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 mb-4">
        @foreach($kategoriMeta as $key => $meta)
            @php
                $colors = $cardColorMap[$key] ?? ['border' => 'border-primary', 'text' => 'text-primary', 'bg' => 'bg-primary'];
            @endphp
            <div class="col">
                <div class="card card-stat p-3 bg-white border-start border-4 {{ $colors['border'] }} text-decoration-none h-100"
                     role="button"
                     onclick="bukaModalPengajuan('{{ $meta['key'] }}', '{{ $meta['nama'] }}', '{{ $meta['deskripsi'] }}', '{{ $meta['ikon'] }}', '{{ $meta['warna'] }}')"
                     title="Klik untuk mengajukan {{ $meta['nama'] }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">{{ $meta['nama'] }}</span>
                            <h5 class="fw-bold {{ $colors['text'] }} mb-0 mt-1">{{ $meta['nama'] }}</h5>
                            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                                + Unggah Berkas PDF <i class="bi bi-arrow-right-short {{ $colors['text'] }}"></i>
                            </small>
                        </div>
                        <div class="p-2 {{ $colors['bg'] }} bg-opacity-10 rounded-circle {{ $colors['text'] }} fs-4">
                            <i class="bi {{ $meta['ikon'] }}"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- BAGIAN 2: FILTER DATA (SAMA PERSIS SEPERTI ADMIN) -->
<div class="card border-0 shadow-sm p-3 mb-4 rounded-3 bg-white">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="fw-bold text-dark small">
            <i class="bi bi-funnel-fill text-primary me-1"></i> Filter Riwayat Pengajuan Surat
        </span>
        @if(request()->anyFilled(['status', 'jenis', 'tanggal_mulai', 'tanggal_selesai']))
            <a href="{{ route('anggota.surat.index') }}" class="btn btn-sm btn-link text-danger text-decoration-none p-0" style="font-size: 0.75rem;">
                <i class="bi bi-x-circle me-1"></i> Reset Filter
            </a>
        @endif
    </div>
    <form method="GET" action="{{ route('anggota.surat.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-md-3 col-6">
                <label class="form-label text-muted small mb-1" style="font-size: 0.75rem;">Kategori Surat</label>
                <select name="jenis" class="form-select form-select-sm">
                    <option value="">Semua Kategori (8 Kategori)</option>
                    @foreach($daftarJenis as $val => $label)
                        <option value="{{ $val }}" {{ request('jenis') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label text-muted small mb-1" style="font-size: 0.75rem;">Status Verifikasi</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label text-muted small mb-1" style="font-size: 0.75rem;">Periode Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="{{ request('tanggal_mulai') }}">
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label text-muted small mb-1" style="font-size: 0.75rem;">Periode Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control form-control-sm" value="{{ request('tanggal_selesai') }}">
            </div>
            <div class="col-md-2 col-12">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold" style="background-color: #0e2238; border-color: #0e2238;">
                    <i class="bi bi-filter me-1"></i> Terapkan Filter
                </button>
            </div>
        </div>
    </form>
</div>

<!-- BAGIAN 3: TABEL RIWAYAT PENGAJUAN (STRUKTUR TERORGANISIR PERSIS ADMIN) -->
<div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white mb-4">
    <div class="card-header bg-white py-3 px-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="fw-bold text-dark mb-0">
            <i class="bi bi-clock-history text-secondary me-1"></i> Daftar Riwayat Pengajuan Surat Saya
        </h6>
        <span class="badge bg-light text-dark border fw-normal py-1 px-2" style="font-size: 0.75rem;">
            Total: <strong>{{ $daftarSurat->total() }}</strong> Pengajuan
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light text-uppercase text-secondary" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                <tr>
                    <th class="text-center" style="width: 45px;">No</th>
                    <th>Waktu Pengajuan</th>
                    <th>Kategori Surat</th>
                    <th>Periode Keterangan</th>
                    <th>Uraian / Alasan</th>
                    <th>Surat Lampiran</th>
                    <th>Status</th>
                    <th>Catatan Admin</th>
                    <th class="text-center" style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftarSurat as $index => $surat)
                    <tr>
                        <td class="text-center text-muted fw-semibold">
                            {{ $daftarSurat->firstItem() + $index }}
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">
                                {{ \Carbon\Carbon::parse($surat->created_at)->translatedFormat('d M Y') }}
                            </div>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($surat->created_at)->format('H:i') }} WITA
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-primary text-uppercase px-2 py-1" style="font-size: 0.72rem;">
                                {{ $surat->label_jenis }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">
                                {{ \Carbon\Carbon::parse($surat->tanggal_mulai)->translatedFormat('d M Y') }}
                                s/d
                                {{ \Carbon\Carbon::parse($surat->tanggal_selesai)->translatedFormat('d M Y') }}
                            </div>
                            <small class="text-muted">
                                Durasi: {{ \Carbon\Carbon::parse($surat->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($surat->tanggal_selesai)) + 1 }} Hari
                            </small>
                        </td>
                        <td style="max-width: 260px;">
                            <span class="text-dark d-inline-block text-truncate w-100" title="{{ $surat->keterangan }}">
                                {{ $surat->keterangan }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('anggota.surat.file', $surat->id_pengajuan) }}" target="_blank" class="btn btn-sm btn-outline-danger py-1 px-2 d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                <i class="bi bi-file-earmark-pdf-fill"></i> Lihat PDF
                            </a>
                        </td>
                        <td>
                            {!! $surat->status_badge !!}
                        </td>
                        <td>
                            @if($surat->catatan_admin)
                                <span class="small text-secondary fst-italic" title="{{ $surat->catatan_admin }}">
                                    "{{ Str::limit($surat->catatan_admin, 35) }}"
                                </span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($surat->status_pengajuan === 'menunggu')
                                <form action="{{ route('anggota.surat.cancel', $surat->id_pengajuan) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan surat ini?');" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Batalkan Pengajuan" style="font-size: 0.72rem;">
                                        <i class="bi bi-trash"></i> Batal
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">Selesai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-file-earmark-text fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <h6 class="fw-bold mb-1">Belum Ada Riwayat Pengajuan Surat</h6>
                            <p class="small mb-3">Klik salah satu kartu kategori di atas untuk membuat pengajuan surat keterangan baru.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    @if($daftarSurat->hasPages())
        <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
            {{ $daftarSurat->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<!-- MODAL FORM PENGAJUAN SURAT (DENGAN UPLOAD BERKAS PDF) -->
<div class="modal fade" id="modalPengajuanSurat" tabindex="-1" aria-labelledby="modalPengajuanSuratLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0" style="border-radius: 16px; overflow: hidden;">
            <!-- Modal Header -->
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #091624 0%, #0e2238 100%); border-bottom: 2px solid #e5a93c;">
                <div class="d-flex align-items-center gap-2">
                    <div id="modalHeaderIconBg" class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: #2563eb;">
                        <i id="modalHeaderIcon" class="bi bi-clipboard-check-fill text-white fs-6"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0" id="modalPengajuanSuratLabel">
                            Pengajuan Surat: <span id="modalHeaderTitleKategori" class="text-warning">Izin</span>
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
                                <i class="bi bi-info-circle me-1"></i> Tanggal tidak boleh tumpang tindih dengan pengajuan aktif lain.
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
                            <i class="bi bi-shield-check text-success me-1"></i> Format berkas wajib <strong>PDF (.pdf)</strong> asli. Ukuran maksimal <strong>{{ round($maxSizeKb / 1024, 1) }} MB</strong>. Berkas disimpan secara privat dan terlindungi.
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

@push('scripts')
<script>
    function bukaModalPengajuan(key, nama, deskripsi, ikon, warna) {
        document.getElementById('modalJenisKeteranganInput').value = key;
        document.getElementById('modalHeaderTitleKategori').innerText = nama;
        document.getElementById('modalHeaderIcon').className = 'bi ' + ikon + ' text-white fs-6';
        document.getElementById('modalHeaderIconBg').style.backgroundColor = warna;
        
        document.getElementById('modalBannerNama').innerText = 'Kategori: ' + nama;
        document.getElementById('modalBannerDeskripsi').innerText = deskripsi;
        document.getElementById('modalBannerBadge').innerText = nama;
        document.getElementById('modalBannerBadge').style.backgroundColor = warna;
        document.getElementById('modalBannerIcon').className = 'bi ' + ikon + ' fs-5';
        document.getElementById('modalBannerIcon').style.color = warna;

        const fileInput = document.getElementById('modal_file_surat');
        if (fileInput) fileInput.value = '';
        
        const modalEl = document.getElementById('modalPengajuanSurat');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
</script>
@endpush

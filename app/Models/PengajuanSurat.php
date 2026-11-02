<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model PengajuanSurat — mewakili tabel `pengajuan_surat`.
 * Data pengajuan ketidakhadiran atau dinas personel (Izin, Sakit, Cuti, dsb) beserta lampiran PDF.
 */
class PengajuanSurat extends Model
{
    protected $table      = 'pengajuan_surat';
    protected $primaryKey = 'id_pengajuan';

    public $timestamps = false;
    const CREATED_AT   = 'created_at';

    protected $fillable = [
        'id_anggota',
        'jenis_keterangan',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'file_surat',
        'status_pengajuan',
        'id_admin',
        'catatan_admin',
        'tanggal_diproses',
    ];

    protected $casts = [
        'tanggal_mulai'    => 'date:Y-m-d',
        'tanggal_selesai'  => 'date:Y-m-d',
        'tanggal_diproses' => 'datetime',
    ];

    // ===== RELASI =====

    /** Pengajuan surat diajukan oleh satu anggota */
    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id_anggota');
    }

    /** Pengajuan surat diproses/diverifikasi oleh admin */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    // ===== HELPER & DAFTAR ENUM =====

    /**
     * Daftar seluruh jenis keterangan surat dan label resminya dalam Bahasa Indonesia.
     */
    public static function daftarJenisKeterangan(): array
    {
        return [
            'izin'        => 'Izin',
            'sakit'       => 'Sakit',
            'surat_dinas' => 'Surat Dinas',
            'cuti'        => 'Cuti',
            'piket'       => 'Piket',
            'lepas_dinas' => 'Lepas Dinas',
            'wfh'         => 'WFH',
            'pendidikan'  => 'Pendidikan',
        ];
    }

    /**
     * Metadata visual kartu kategori surat (ikon, warna, deskripsi ringkas).
     */
    public static function kategoriMeta(): array
    {
        return [
            'izin' => [
                'key'       => 'izin',
                'nama'      => 'Izin',
                'ikon'      => 'bi-clipboard-check-fill',
                'warna'     => '#2563eb',
                'warna_bg'  => '#eff6ff',
                'border'    => '#bfdbfe',
                'deskripsi' => 'Keperluan dinas / pribadi penting',
            ],
            'sakit' => [
                'key'       => 'sakit',
                'nama'      => 'Sakit',
                'ikon'      => 'bi-heart-pulse-fill',
                'warna'     => '#dc2626',
                'warna_bg'  => '#fef2f2',
                'border'    => '#fecaca',
                'deskripsi' => 'Surat dokter / keterangan medis',
            ],
            'surat_dinas' => [
                'key'       => 'surat_dinas',
                'nama'      => 'Surat Dinas',
                'ikon'      => 'bi-briefcase-fill',
                'warna'     => '#0e2238',
                'warna_bg'  => '#f1f5f9',
                'border'    => '#cbd5e1',
                'deskripsi' => 'Surat Perintah Tugas (Sprin) dinas',
            ],
            'cuti' => [
                'key'       => 'cuti',
                'nama'      => 'Cuti',
                'ikon'      => 'bi-calendar2-check-fill',
                'warna'     => '#059669',
                'warna_bg'  => '#ecfdf5',
                'border'    => '#a7f3d0',
                'deskripsi' => 'Cuti tahunan / alasan penting',
            ],
            'piket' => [
                'key'       => 'piket',
                'nama'      => 'Piket',
                'ikon'      => 'bi-shield-check',
                'warna'     => '#d97706',
                'warna_bg'  => '#fffbeb',
                'border'    => '#fde68a',
                'deskripsi' => 'Piket penjagaan mako / siaga',
            ],
            'lepas_dinas' => [
                'key'       => 'lepas_dinas',
                'nama'      => 'Lepas Dinas',
                'ikon'      => 'bi-person-check-fill',
                'warna'     => '#7c3aed',
                'warna_bg'  => '#f5f3ff',
                'border'    => '#ddd6fe',
                'deskripsi' => 'Bebas piket dinas regu jaga',
            ],
            'wfh' => [
                'key'       => 'wfh',
                'nama'      => 'WFH',
                'ikon'      => 'bi-laptop',
                'warna'     => '#0891b2',
                'warna_bg'  => '#ecfeff',
                'border'    => '#a5f3fc',
                'deskripsi' => 'Work From Home / tugas daring',
            ],
            'pendidikan' => [
                'key'       => 'pendidikan',
                'nama'      => 'Pendidikan',
                'ikon'      => 'bi-mortarboard-fill',
                'warna'     => '#4f46e5',
                'warna_bg'  => '#eef2ff',
                'border'    => '#c7d2fe',
                'deskripsi' => 'Diklat / kejuruan kepolisian',
            ],
        ];
    }

    /**
     * Dapatkan label jenis keterangan dalam Bahasa Indonesia.
     */
    public function getJenisLabelAttribute(): string
    {
        $daftar = self::daftarJenisKeterangan();
        return $daftar[$this->jenis_keterangan] ?? ucfirst(str_replace('_', ' ', $this->jenis_keterangan));
    }

    /**
     * Alias label jenis keterangan.
     */
    public function getLabelJenisAttribute(): string
    {
        return $this->jenis_label;
    }

    /**
     * Dapatkan badge status pengajuan Bootstrap 5.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status_pengajuan) {
            'disetujui' => '<span class="badge bg-success text-white px-2 py-1"><i class="bi bi-check-circle me-1"></i>Disetujui</span>',
            'ditolak'   => '<span class="badge bg-danger text-white px-2 py-1"><i class="bi bi-x-circle me-1"></i>Ditolak</span>',
            default     => '<span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-hourglass-split me-1"></i>Menunggu</span>',
        };
    }
}


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


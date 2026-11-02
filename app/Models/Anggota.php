<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Anggota — mewakili tabel `anggota`.
 * Personel Bidtik Polda Kalsel yang memiliki akun login dan melakukan presensi.
 */
class Anggota extends Authenticatable
{
    protected $table      = 'anggota';
    protected $primaryKey = 'id_anggota';

    // Hanya ada created_at
    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'id_admin',
        'nrp',
        'nama',
        'email',
        'password',
        'jabatan',
        'unit_kerja',
        'no_telepon',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // ===== RELASI =====

    /** Anggota didaftarkan oleh satu admin */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    /** Anggota memiliki banyak rekaman presensi */
    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'id_anggota', 'id_anggota');
    }

    /** Anggota memiliki banyak perangkat notifikasi Web Push */
    public function perangkatNotifikasi(): HasMany
    {
        return $this->hasMany(PerangkatNotifikasi::class, 'id_anggota', 'id_anggota');
    }

    /** Anggota memiliki banyak riwayat pengajuan surat keterangan */
    public function pengajuanSurat(): HasMany
    {
        return $this->hasMany(PengajuanSurat::class, 'id_anggota', 'id_anggota');
    }

    // ===== HELPER =====

    /** Cek apakah anggota berstatus aktif */
    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    /** Cek apakah anggota sudah memiliki akun login (email & password) */
    public function hasAkunLogin(): bool
    {
        return ! empty($this->email) && ! empty($this->password);
    }
}

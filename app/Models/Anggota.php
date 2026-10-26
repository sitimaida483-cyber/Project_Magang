<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Anggota — mewakili tabel `anggota`.
 * Data personel yang bisa melakukan presensi.
 */
class Anggota extends Model
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
        'jabatan',
        'unit_kerja',
        'no_telepon',
        'status',
    ];

    // ===== RELASI =====

    /** Anggota dimiliki oleh satu admin */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    /** Anggota memiliki banyak presensi */
    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'id_anggota', 'id_anggota');
    }

    /** Anggota memiliki banyak perangkat notifikasi */
    public function perangkatNotifikasi(): HasMany
    {
        return $this->hasMany(PerangkatNotifikasi::class, 'id_anggota', 'id_anggota');
    }

    // ===== HELPER =====

    /** Cek apakah anggota berstatus aktif */
    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }
}

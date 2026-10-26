<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model PerangkatNotifikasi — mewakili tabel `perangkat_notifikasi`.
 * Menyimpan data Web Push subscription per perangkat anggota.
 */
class PerangkatNotifikasi extends Model
{
    protected $table      = 'perangkat_notifikasi';
    protected $primaryKey = 'id_perangkat';

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'id_anggota',
        'token_notifikasi',
        'jenis_perangkat',
        'status',
    ];

    // ===== RELASI =====

    /** Perangkat dimiliki oleh satu anggota */
    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id_anggota');
    }

    /** Perangkat memiliki banyak riwayat notifikasi */
    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'id_perangkat', 'id_perangkat');
    }
}

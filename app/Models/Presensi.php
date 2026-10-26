<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Presensi — mewakili tabel `presensi`.
 * Setiap record adalah 1 kejadian presensi masuk atau pulang.
 */
class Presensi extends Model
{
    protected $table      = 'presensi';
    protected $primaryKey = 'id_presensi';

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'id_anggota',
        'tanggal',
        'waktu',
        'jenis_presensi',
        'foto_selfie',
        'tanda_tangan',
        'latitude',
        'longitude',
        'status_kehadiran',
    ];

    protected $casts = [
        'tanggal'   => 'date',
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // ===== RELASI =====

    /** Presensi dimiliki oleh satu anggota */
    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id_anggota');
    }
}

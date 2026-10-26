<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Pengaturan — mewakili tabel `pengaturan`.
 * Konfigurasi lokasi kantor, radius, dan jam presensi.
 */
class Pengaturan extends Model
{
    protected $table      = 'pengaturan';
    protected $primaryKey = 'id_pengaturan';

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'id_admin',
        'nama_lokasi',
        'latitude',
        'longitude',
        'radius_meter',
        'jam_masuk',
        'jam_keluar',
    ];

    protected $casts = [
        'latitude'     => 'decimal:7',
        'longitude'    => 'decimal:7',
        'radius_meter' => 'integer',
    ];

    // ===== RELASI =====

    /** Pengaturan dimiliki oleh satu admin */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }
}

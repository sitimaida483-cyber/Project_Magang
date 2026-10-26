<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Notifikasi — mewakili tabel `notifikasi`.
 * Mencatat riwayat pengiriman notifikasi push ke perangkat anggota.
 */
class Notifikasi extends Model
{
    protected $table      = 'notifikasi';
    protected $primaryKey = 'id_notifikasi';

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'id_perangkat',
        'jenis_notifikasi',
        'pesan',
        'waktu_kirim',
        'status_kirim',
    ];

    protected $casts = [
        'waktu_kirim' => 'datetime',
    ];

    // ===== RELASI =====

    /** Notifikasi dimiliki oleh satu perangkat */
    public function perangkat(): BelongsTo
    {
        return $this->belongsTo(PerangkatNotifikasi::class, 'id_perangkat', 'id_perangkat');
    }
}

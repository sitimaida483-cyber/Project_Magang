<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Presensi — mewakili tabel `presensi`.
 * Setiap record adalah 1 kejadian presensi masuk atau pulang.
 * Dilengkapi telemetri audit & penanda anomali pemalsuan presensi (Tahap 2).
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
        'ip_address',
        'user_agent',
        'akurasi_gps',
        'foto_hash',
        'flag_anomali',
        'catatan_anomali',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'latitude'     => 'decimal:7',
        'longitude'    => 'decimal:7',
        'akurasi_gps'  => 'decimal:2',
        'flag_anomali' => 'boolean',
    ];

    // ===== RELASI =====

    /** Presensi dimiliki oleh satu anggota */
    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id_anggota');
    }

    // ===== AKSESOR & HELPER =====

    /**
     * Cek apakah rekaman presensi ditandai memiliki anomali/kejanggalan.
     */
    public function isAnomali(): bool
    {
        return (bool) $this->flag_anomali;
    }

    /**
     * Dapatkan daftar catatan anomali dalam bentuk array rapi.
     */
    public function getDaftarAlasanAnomaliAttribute(): array
    {
        if (empty($this->catatan_anomali)) {
            return [];
        }

        // Coba decode jika format JSON
        $decoded = json_decode($this->catatan_anomali, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Fallback: pisahkan dengan baris baru atau semicolon
        return array_filter(array_map('trim', explode(';', $this->catatan_anomali)));
    }
}

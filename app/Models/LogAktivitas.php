<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model LogAktivitas — mewakili tabel `log_aktivitas`.
 * Mencatat setiap aksi penting yang dilakukan admin.
 */
class LogAktivitas extends Model
{
    protected $table      = 'log_aktivitas';
    protected $primaryKey = 'id_log';

    // Tabel ini tidak menggunakan timestamps bawaan
    public $timestamps = false;

    protected $fillable = [
        'id_admin',
        'aktivitas',
        'tabel_terkait',
        'keterangan',
        'alamat_ip',
        'waktu_aktivitas',
    ];

    protected $casts = [
        'waktu_aktivitas' => 'datetime',
    ];

    // ===== RELASI =====

    /** Log dimiliki oleh satu admin */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }
}

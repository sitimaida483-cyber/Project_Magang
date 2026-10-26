<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Admin — mewakili tabel `admin`.
 * Digunakan untuk autentikasi dan pengelolaan sistem.
 */
class Admin extends Authenticatable
{
    // Nama tabel kustom (bukan default 'admins')
    protected $table = 'admin';

    // Primary key kustom
    protected $primaryKey = 'id_admin';

    // Nonaktifkan updated_at karena tabel hanya punya created_at
    public $timestamps = false;
    const CREATED_AT = 'created_at';

    // Kolom yang boleh diisi massal
    protected $fillable = [
        'username',
        'password',
        'nama',
    ];

    // Kolom yang disembunyikan dari serialisasi
    protected $hidden = [
        'password',
    ];

    // Hash otomatis saat mengisi password
    protected $casts = [
        'password' => 'hashed',
    ];

    // ===== RELASI =====

    /** Admin memiliki banyak anggota */
    public function anggota(): HasMany
    {
        return $this->hasMany(Anggota::class, 'id_admin', 'id_admin');
    }

    /** Admin memiliki banyak pengaturan */
    public function pengaturan(): HasMany
    {
        return $this->hasMany(Pengaturan::class, 'id_admin', 'id_admin');
    }

    /** Admin memiliki banyak log aktivitas */
    public function logAktivitas(): HasMany
    {
        return $this->hasMany(LogAktivitas::class, 'id_admin', 'id_admin');
    }
}

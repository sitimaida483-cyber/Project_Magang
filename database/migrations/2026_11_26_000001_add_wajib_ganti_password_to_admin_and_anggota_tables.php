<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration penambahan kolom wajib_ganti_password pada tabel admin dan anggota.
 * Digunakan untuk memaksa pengguna mengganti kata sandi setelah akun dibuat
 * atau setelah kata sandi direset oleh administrator / konsol.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom pada tabel admin jika belum ada
        if (Schema::hasTable('admin') && ! Schema::hasColumn('admin', 'wajib_ganti_password')) {
            Schema::table('admin', function (Blueprint $table) {
                $table->boolean('wajib_ganti_password')
                    ->default(false)
                    ->after('password')
                    ->comment('Indikator kewajiban ganti sandi saat login');
            });
        }

        // 2. Tambah kolom pada tabel anggota jika belum ada
        if (Schema::hasTable('anggota') && ! Schema::hasColumn('anggota', 'wajib_ganti_password')) {
            Schema::table('anggota', function (Blueprint $table) {
                $table->boolean('wajib_ganti_password')
                    ->default(false)
                    ->after('password')
                    ->comment('Indikator kewajiban ganti sandi saat login');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('admin') && Schema::hasColumn('admin', 'wajib_ganti_password')) {
            Schema::table('admin', function (Blueprint $table) {
                $table->dropColumn('wajib_ganti_password');
            });
        }

        if (Schema::hasTable('anggota') && Schema::hasColumn('anggota', 'wajib_ganti_password')) {
            Schema::table('anggota', function (Blueprint $table) {
                $table->dropColumn('wajib_ganti_password');
            });
        }
    }
};

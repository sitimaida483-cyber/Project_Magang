<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration penambahan kolom telemetri & deteksi anomali pada tabel presensi (Tahap 2).
 * Sifat: 100% penambahan kolom baru (additive) dan nullable demi menjaga integritas data riwayat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('status_kehadiran');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->decimal('akurasi_gps', 8, 2)->nullable()->after('user_agent');
            $table->string('foto_hash', 64)->nullable()->after('akurasi_gps')->index('presensi_foto_hash_idx');
            $table->boolean('flag_anomali')->default(false)->after('foto_hash')->index('presensi_flag_anomali_idx');
            $table->text('catatan_anomali')->nullable()->after('flag_anomali');
        });
    }

    public function down(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->dropIndex('presensi_foto_hash_idx');
            $table->dropIndex('presensi_flag_anomali_idx');
            $table->dropColumn([
                'ip_address',
                'user_agent',
                'akurasi_gps',
                'foto_hash',
                'flag_anomali',
                'catatan_anomali',
            ]);
        });
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel pengaturan.
 * Menyimpan konfigurasi lokasi kantor, radius, dan jam presensi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id('id_pengaturan');                  // Primary key kustom
            $table->unsignedBigInteger('id_admin');       // FK ke admin yang mengatur
            $table->string('nama_lokasi');                // Nama lokasi kantor
            $table->decimal('latitude', 10, 7);           // Koordinat lintang titik kantor
            $table->decimal('longitude', 10, 7);          // Koordinat bujur titik kantor
            $table->integer('radius_meter');              // Radius batas presensi (meter)
            $table->time('jam_masuk');                    // Jam batas presensi masuk
            $table->time('jam_keluar');                   // Jam mulai presensi pulang
            $table->timestamp('created_at')->useCurrent(); // Waktu dibuat

            // Foreign key ke tabel admin
            $table->foreign('id_admin')
                  ->references('id_admin')
                  ->on('admin')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};

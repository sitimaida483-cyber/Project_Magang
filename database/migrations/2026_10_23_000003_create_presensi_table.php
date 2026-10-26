<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel presensi.
 * Menyimpan setiap rekaman presensi masuk/pulang anggota.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $table) {
            $table->id('id_presensi');                                                    // Primary key kustom
            $table->unsignedBigInteger('id_anggota');                                     // FK ke tabel anggota
            $table->date('tanggal');                                                       // Tanggal presensi
            $table->time('waktu');                                                         // Waktu presensi (HH:MM:SS)
            $table->enum('jenis_presensi', ['masuk', 'pulang']);                          // Jenis: masuk atau pulang
            $table->string('foto_selfie');                                                 // Path file foto selfie
            $table->string('tanda_tangan');                                                // Path file tanda tangan PNG
            $table->decimal('latitude', 10, 7);                                           // Koordinat lintang GPS
            $table->decimal('longitude', 10, 7);                                          // Koordinat bujur GPS
            $table->enum('status_kehadiran', ['tepat_waktu', 'terlambat']);               // Status ketepatan waktu
            $table->timestamp('created_at')->useCurrent();                                 // Waktu dibuat

            // Foreign key ke tabel anggota
            $table->foreign('id_anggota')
                  ->references('id_anggota')
                  ->on('anggota')
                  ->onDelete('restrict');

            // Indeks untuk query rekap berdasarkan tanggal
            $table->index(['id_anggota', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};

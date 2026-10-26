<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel notifikasi.
 * Mencatat riwayat setiap notifikasi yang dikirim ke perangkat anggota.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id('id_notifikasi');                                                                          // Primary key kustom
            $table->unsignedBigInteger('id_perangkat');                                                           // FK ke perangkat_notifikasi
            $table->enum('jenis_notifikasi', ['pengingat_masuk', 'pengingat_pulang', 'presensi_berhasil']);       // Jenis notifikasi
            $table->text('pesan');                                                                                 // Isi pesan notifikasi
            $table->dateTime('waktu_kirim');                                                                       // Waktu pengiriman
            $table->enum('status_kirim', ['terkirim', 'gagal'])->default('terkirim');                             // Status pengiriman
            $table->timestamp('created_at')->useCurrent();                                                         // Waktu dibuat

            // Foreign key ke tabel perangkat_notifikasi
            $table->foreign('id_perangkat')
                  ->references('id_perangkat')
                  ->on('perangkat_notifikasi')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};

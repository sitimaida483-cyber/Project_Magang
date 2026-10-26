<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel perangkat_notifikasi.
 * Menyimpan data subscription Web Push per perangkat anggota.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perangkat_notifikasi', function (Blueprint $table) {
            $table->id('id_perangkat');                                           // Primary key kustom
            $table->unsignedBigInteger('id_anggota');                             // FK ke tabel anggota
            $table->text('token_notifikasi');                                     // JSON data push subscription (endpoint, keys)
            $table->string('jenis_perangkat')->nullable();                        // Jenis perangkat (android, ios, desktop)
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');     // Status langganan notifikasi
            $table->timestamp('created_at')->useCurrent();                         // Waktu dibuat

            // Foreign key ke tabel anggota
            $table->foreign('id_anggota')
                  ->references('id_anggota')
                  ->on('anggota')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perangkat_notifikasi');
    }
};

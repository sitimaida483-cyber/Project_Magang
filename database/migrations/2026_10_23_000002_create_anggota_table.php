<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel anggota.
 * Menyimpan data personel yang dapat melakukan presensi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggota', function (Blueprint $table) {
            $table->id('id_anggota');                                        // Primary key kustom
            $table->unsignedBigInteger('id_admin');                          // FK ke tabel admin
            $table->string('nrp')->unique();                                 // Nomor Registrasi Pokok, unik
            $table->string('nama');                                          // Nama lengkap anggota
            $table->string('jabatan');                                       // Jabatan/pangkat
            $table->string('unit_kerja');                                    // Unit kerja / satuan
            $table->string('no_telepon')->nullable();                        // Nomor HP (opsional)
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif'); // Status keanggotaan
            $table->timestamp('created_at')->useCurrent();                   // Waktu dibuat

            // Foreign key ke tabel admin
            $table->foreign('id_admin')
                  ->references('id_admin')
                  ->on('admin')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota');
    }
};

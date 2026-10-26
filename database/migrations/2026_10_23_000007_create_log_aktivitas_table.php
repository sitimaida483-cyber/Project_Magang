<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel log_aktivitas.
 * Mencatat setiap aksi penting yang dilakukan oleh admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id('id_log');                              // Primary key kustom
            $table->unsignedBigInteger('id_admin');            // FK ke tabel admin pelaku aksi
            $table->string('aktivitas');                       // Deskripsi singkat aksi (mis: "Tambah Anggota")
            $table->string('tabel_terkait')->nullable();       // Nama tabel yang dipengaruhi
            $table->text('keterangan')->nullable();            // Detail tambahan
            $table->string('alamat_ip')->nullable();           // IP Address pelaku
            $table->dateTime('waktu_aktivitas');               // Waktu aksi dilakukan

            // Foreign key ke tabel admin
            $table->foreign('id_admin')
                  ->references('id_admin')
                  ->on('admin')
                  ->onDelete('restrict');

            // Indeks untuk filter berdasarkan waktu
            $table->index('waktu_aktivitas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};

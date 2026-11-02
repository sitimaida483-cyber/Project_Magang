<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pembuatan tabel pengajuan_surat.
 * Menyimpan data pengajuan surat keterangan/izin dinas anggota beserta lampiran PDF.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_surat', function (Blueprint $table) {
            $table->id('id_pengajuan');
            $table->unsignedBigInteger('id_anggota');
            $table->enum('jenis_keterangan', [
                'izin',
                'sakit',
                'surat_dinas',
                'cuti',
                'piket',
                'lepas_dinas',
                'wfh',
                'pendidikan',
            ]);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('keterangan');
            $table->string('file_surat');
            $table->enum('status_pengajuan', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->unsignedBigInteger('id_admin')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->dateTime('tanggal_diproses')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Foreign Key
            $table->foreign('id_anggota')
                  ->references('id_anggota')
                  ->on('anggota')
                  ->onDelete('restrict');

            $table->foreign('id_admin')
                  ->references('id_admin')
                  ->on('admin')
                  ->onDelete('set null');

            // Indeks pencarian & filter
            $table->index(['id_anggota', 'status_pengajuan']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
            $table->index('status_pengajuan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_surat');
    }
};


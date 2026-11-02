<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration penambahan kolom email dan password pada tabel anggota.
 * Sifatnya additive (tidak menghapus kolom lama).
 * Nullable agar data anggota lama yang sudah ada tidak error/hilang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('nama');
            $table->string('password')->nullable()->after('email');
            $table->rememberToken()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropColumn(['email', 'password', 'remember_token']);
        });
    }
};


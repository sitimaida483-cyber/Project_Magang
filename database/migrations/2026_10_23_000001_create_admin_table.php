<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel admin.
 * Menyimpan akun administrator yang dapat login ke panel admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin', function (Blueprint $table) {
            $table->id('id_admin');                      // Primary key kustom
            $table->string('username')->unique();        // Username unik untuk login
            $table->string('password');                  // Password di-hash bcrypt
            $table->string('nama');                      // Nama lengkap admin
            $table->timestamp('created_at')->useCurrent(); // Waktu dibuat
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};

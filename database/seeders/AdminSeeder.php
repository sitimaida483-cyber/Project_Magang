<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder untuk membuat 1 akun admin awal.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'username' => 'admin',                    // Ganti sesuai kebutuhan
            'password' => Hash::make('password123'),  // Ganti password ini setelah login pertama!
            'nama'     => 'Administrator Bidtik',
        ]);
    }
}

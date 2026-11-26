<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Validasi
    |--------------------------------------------------------------------------
    | Pesan error dalam bahasa Indonesia untuk validasi kata sandi dan form.
    */

    'password' => [
        'letters'       => 'Kata sandi wajib mengandung setidaknya satu huruf.',
        'mixed'         => 'Kata sandi wajib mengombinasikan huruf besar (kapital) dan huruf kecil.',
        'numbers'       => 'Kata sandi wajib mengandung setidaknya satu angka (0-9).',
        'symbols'       => 'Kata sandi wajib mengandung setidaknya satu simbol atau karakter khusus.',
        'uncompromised' => 'Kata sandi yang dipilih terdeteksi pernah mengalami kebocoran data publik. Silakan pilih kata sandi lain.',
    ],

    'min' => [
        'string' => ':Attribute minimal harus :min karakter.',
    ],
    'max' => [
        'string' => ':Attribute maksimal :max karakter.',
    ],
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'required'  => ':Attribute wajib diisi.',
];

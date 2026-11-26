<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Aturan validasi untuk mencegah kata sandi memuat unsur identitas pemilik akun:
 * nama lengkap / kata dalam nama, username, email / username email, dan NRP.
 * Pemeriksaan dilakukan secara case-insensitive untuk fragmen bermakna (panjang >= 3 karakter).
 */
class TidakMemuatDataPengguna implements ValidationRule
{
    /**
     * @var array Daftar atribut pengenal pengguna (username, nama, email, nrp, dll.)
     */
    protected array $dataIdentitas;

    /**
     * Buat instance aturan baru.
     *
     * @param array $dataIdentitas Nilai-nilai identitas pemilik akun
     */
    public function __construct(array $dataIdentitas = [])
    {
        $this->dataIdentitas = $dataIdentitas;
    }

    /**
     * Jalankan validasi.
     *
     * @param string   $attribute
     * @param mixed    $value
     * @param \Closure $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        $passwordLower = mb_strtolower($value);

        foreach ($this->dataIdentitas as $item) {
            if (empty($item)) {
                continue;
            }

            $itemStr = trim((string) $item);
            if ($itemStr === '') {
                continue;
            }

            // 1. Cek string utuh jika panjang minimal 3 karakter
            $itemLower = mb_strtolower($itemStr);
            if (mb_strlen($itemLower) >= 3 && str_contains($passwordLower, $itemLower)) {
                $fail('Kata sandi tidak boleh memuat nama, username, email, atau NRP pemilik akun.');
                return;
            }

            // 2. Jika input berupa alamat email, periksa juga username sebelum karakter '@'
            if (str_contains($itemStr, '@')) {
                $emailPrefix = mb_strtolower(explode('@', $itemStr)[0]);
                if (mb_strlen($emailPrefix) >= 3 && str_contains($passwordLower, $emailPrefix)) {
                    $fail('Kata sandi tidak boleh memuat nama, username, email, atau NRP pemilik akun.');
                    return;
                }
            }

            // 3. Jika input berupa nama atau kalimat panjang, pecah per kata (hanya kata berpanjang >= 3)
            $kataPecahan = preg_split('/[\s,\.\-_@]+/', $itemStr);
            if (is_array($kataPecahan)) {
                foreach ($kataPecahan as $kata) {
                    $kataLower = mb_strtolower(trim($kata));
                    if (mb_strlen($kataLower) >= 3 && str_contains($passwordLower, $kataLower)) {
                        $fail('Kata sandi tidak boleh memuat nama, username, email, atau NRP pemilik akun.');
                        return;
                    }
                }
            }
        }
    }
}

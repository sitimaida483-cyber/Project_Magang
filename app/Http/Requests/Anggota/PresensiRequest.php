<?php

namespace App\Http\Requests\Anggota;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PresensiRequest — validasi data presensi anggota.
 */
class PresensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Halaman publik tanpa login, siapa pun dengan NRP valid boleh mengirim presensi
        return true;
    }

    public function rules(): array
    {
        return [
            'nrp'            => ['required', 'string', 'max:50'],
            'jenis_presensi' => ['required', 'in:masuk,pulang'],
            'latitude'       => ['required', 'numeric', 'between:-90,90'],
            'longitude'      => ['required', 'numeric', 'between:-180,180'],
            'foto_selfie'    => ['required', 'string'],
            'tanda_tangan'   => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nrp.required'            => 'NRP personel wajib diisi.',
            'jenis_presensi.required' => 'Jenis presensi (masuk/pulang) wajib dipilih.',
            'jenis_presensi.in'       => 'Jenis presensi harus berupa masuk atau pulang.',
            'latitude.required'       => 'Koordinat GPS latitude belum terdeteksi. Pastikan GPS aktif.',
            'longitude.required'      => 'Koordinat GPS longitude belum terdeteksi. Pastikan GPS aktif.',
            'foto_selfie.required'    => 'Foto selfie kamera depan wajib diambil sebelum mengirim presensi.',
            'tanda_tangan.required'   => 'Tanda tangan digital wajib dibubuhkan pada kanvas.',
        ];
    }
}

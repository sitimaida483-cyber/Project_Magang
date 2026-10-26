<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * AnggotaRequest — validasi form tambah dan edit anggota.
 */
class AnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        // Ambil parameter id anggota dari route untuk pengecualian unique saat update
        $anggotaId = $this->route('anggotum') ?? $this->route('anggota');

        return [
            'nrp' => [
                'required',
                'string',
                'max:50',
                Rule::unique('anggota', 'nrp')->ignore($anggotaId, 'id_anggota'),
            ],
            'nama'        => ['required', 'string', 'max:255'],
            'jabatan'     => ['required', 'string', 'max:100'],
            'unit_kerja'  => ['required', 'string', 'max:100'],
            'no_telepon'  => ['nullable', 'string', 'max:20'],
            'status'      => ['required', 'in:aktif,nonaktif'],
        ];
    }

    public function messages(): array
    {
        return [
            'nrp.required'        => 'NRP wajib diisi.',
            'nrp.unique'          => 'NRP ini sudah terdaftar untuk anggota lain.',
            'nrp.max'             => 'NRP maksimal 50 karakter.',
            'nama.required'       => 'Nama lengkap anggota wajib diisi.',
            'jabatan.required'    => 'Jabatan atau pangkat wajib diisi.',
            'unit_kerja.required' => 'Unit kerja atau divisi wajib diisi.',
            'status.required'     => 'Status keanggotaan wajib dipilih.',
            'status.in'           => 'Status harus berupa aktif atau nonaktif.',
        ];
    }
}

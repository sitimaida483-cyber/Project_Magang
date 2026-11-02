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
        $isCreate  = $this->isMethod('post');

        return [
            'nrp' => [
                'required',
                'string',
                'max:50',
                Rule::unique('anggota', 'nrp')->ignore($anggotaId, 'id_anggota'),
            ],
            'nama'        => ['required', 'string', 'max:255'],
            'email'       => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('anggota', 'email')->ignore($anggotaId, 'id_anggota'),
            ],
            'password'    => [
                $isCreate ? 'required' : 'nullable',
                'string',
                'min:8',
            ],
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
            'email.required'      => 'Email kedinasan anggota wajib diisi.',
            'email.email'         => 'Format email tidak valid.',
            'email.unique'        => 'Email ini sudah digunakan oleh anggota lain.',
            'password.required'   => 'Password akun anggota wajib diisi.',
            'password.min'        => 'Password minimal harus 8 karakter.',
            'jabatan.required'    => 'Jabatan atau pangkat wajib diisi.',
            'unit_kerja.required' => 'Unit kerja atau divisi wajib diisi.',
            'status.required'     => 'Status keanggotaan wajib dipilih.',
            'status.in'           => 'Status harus berupa aktif atau nonaktif.',
        ];
    }
}

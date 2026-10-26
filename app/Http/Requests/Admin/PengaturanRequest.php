<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PengaturanRequest — validasi form update pengaturan kantor.
 */
class PengaturanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya admin terautentikasi yang boleh mengubah pengaturan
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'nama_lokasi'  => ['required', 'string', 'max:255'],
            'latitude'     => ['required', 'numeric', 'between:-90,90'],
            'longitude'    => ['required', 'numeric', 'between:-180,180'],
            'radius_meter' => ['required', 'integer', 'min:5', 'max:50000'],
            'jam_masuk'    => ['required', 'date_format:H:i'],
            'jam_keluar'   => ['required', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_lokasi.required'  => 'Nama lokasi kantor wajib diisi.',
            'latitude.required'     => 'Koordinat latitude wajib ditentukan.',
            'latitude.numeric'      => 'Latitude harus berupa angka koordinat yang valid.',
            'latitude.between'      => 'Latitude harus berada dalam rentang -90 hingga 90.',
            'longitude.required'    => 'Koordinat longitude wajib ditentukan.',
            'longitude.numeric'     => 'Longitude harus berupa angka koordinat yang valid.',
            'longitude.between'     => 'Longitude harus berada dalam rentang -180 hingga 180.',
            'radius_meter.required' => 'Radius batas presensi wajib diisi.',
            'radius_meter.integer'  => 'Radius harus berupa bilangan bulat dalam meter.',
            'radius_meter.min'      => 'Radius minimal adalah 5 meter.',
            'radius_meter.max'      => 'Radius maksimal adalah 50.000 meter.',
            'jam_masuk.required'    => 'Jam masuk wajib diisi.',
            'jam_masuk.date_format' => 'Format jam masuk harus Jam:Menit (contoh: 07:30).',
            'jam_keluar.required'   => 'Jam keluar wajib diisi.',
            'jam_keluar.date_format'=> 'Format jam keluar harus Jam:Menit (contoh: 16:00).',
        ];
    }
}

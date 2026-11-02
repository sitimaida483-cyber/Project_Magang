<?php

namespace App\Http\Requests\Anggota;

use App\Models\PengajuanSurat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * PengajuanSuratRequest — validasi form pengajuan surat keterangan oleh personel.
 */
class PengajuanSuratRequest extends FormRequest
{
    public function authorize(): bool
    {
        $anggota = auth('anggota')->user();
        return $anggota && $anggota->isAktif();
    }

    public function rules(): array
    {
        $maxSizeKb = config('presensi.surat_max_size_kb', 2048);
        $jenisList = implode(',', array_keys(PengajuanSurat::daftarJenisKeterangan()));

        return [
            'jenis_keterangan' => ['required', 'string', "in:{$jenisList}"],
            'tanggal_mulai'    => ['required', 'date'],
            'tanggal_selesai'  => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan'       => ['required', 'string', 'min:5', 'max:1000'],
            'file_surat'       => [
                'required',
                'file',
                'mimes:pdf',
                'mimetypes:application/pdf',
                "max:{$maxSizeKb}",
            ],
        ];
    }

    public function messages(): array
    {
        $maxSizeKb = config('presensi.surat_max_size_kb', 2048);
        $maxMb = round($maxSizeKb / 1024, 1);

        return [
            'jenis_keterangan.required' => 'Jenis keterangan surat wajib dipilih.',
            'jenis_keterangan.in'       => 'Jenis keterangan yang dipilih tidak valid.',
            'tanggal_mulai.required'    => 'Tanggal mulai periode wajib diisi.',
            'tanggal_mulai.date'        => 'Format tanggal mulai tidak valid.',
            'tanggal_selesai.required'  => 'Tanggal selesai periode wajib diisi.',
            'tanggal_selesai.date'      => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'keterangan.required'       => 'Uraian atau alasan keterangan wajib diisi.',
            'keterangan.min'            => 'Keterangan minimal terdiri dari 5 karakter.',
            'keterangan.max'            => 'Keterangan maksimal terdiri dari 1000 karakter.',
            'file_surat.required'       => 'Berkas surat lampiran (PDF) wajib diunggah.',
            'file_surat.file'           => 'Berkas yang diunggah harus berupa file yang valid.',
            'file_surat.mimes'          => 'Format berkas hanya diperbolehkan berupa dokumen PDF (.pdf).',
            'file_surat.mimetypes'      => 'Tipe berkas tidak valid. Hanya berkas PDF resmi yang diizinkan.',
            'file_surat.max'            => "Ukuran berkas PDF tidak boleh melebihi {$maxMb} MB ({$maxSizeKb} KB).",
        ];
    }

    /**
     * Validasi tumpang tindih tanggal (overlap) dengan pengajuan lain milik anggota.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $anggota = auth('anggota')->user();
            if (! $anggota) {
                return;
            }

            $tglMulai   = $this->input('tanggal_mulai');
            $tglSelesai = $this->input('tanggal_selesai');

            if ($tglMulai && $tglSelesai && $tglSelesai >= $tglMulai) {
                $adaOverlap = PengajuanSurat::where('id_anggota', $anggota->id_anggota)
                    ->whereIn('status_pengajuan', ['menunggu', 'disetujui'])
                    ->where(function ($query) use ($tglMulai, $tglSelesai) {
                        $query->where('tanggal_mulai', '<=', $tglSelesai)
                              ->where('tanggal_selesai', '>=', $tglMulai);
                    })
                    ->exists();

                if ($adaOverlap) {
                    $validator->errors()->add(
                        'tanggal_mulai',
                        'Periode tanggal yang Anda pilih tumpang tindih dengan pengajuan surat Anda yang lain (berstatus Menunggu atau Disetujui).'
                    );
                }
            }
        });
    }
}


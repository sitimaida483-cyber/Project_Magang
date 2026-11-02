<?php

namespace App\Http\Controllers\Anggota;

use App\Http\Controllers\Controller;
use App\Http\Requests\Anggota\PengajuanSuratRequest;
use App\Models\PengajuanSurat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * PengajuanSuratController (Anggota)
 * Mengelola riwayat, formulir pengajuan, pembatalan, dan pengunduhan surat oleh personel dinas.
 */
class PengajuanSuratController extends Controller
{
    /**
     * Tampilkan riwayat pengajuan surat keterangan personel.
     */
    public function index(Request $request): View
    {
        $anggota = auth('anggota')->user();

        $query = PengajuanSurat::where('id_anggota', $anggota->id_anggota);

        // Filter status
        if ($request->filled('status')) {
            $query->where('status_pengajuan', $request->status);
        }

        // Filter jenis keterangan
        if ($request->filled('jenis')) {
            $query->where('jenis_keterangan', $request->jenis);
        }

        // Filter periode tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_mulai', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_selesai', '<=', $request->tanggal_selesai);
        }

        $daftarSurat  = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $daftarJenis  = PengajuanSurat::daftarJenisKeterangan();
        $kategoriMeta = PengajuanSurat::kategoriMeta();
        $maxSizeKb    = config('presensi.surat_max_size_kb', 2048);

        // Jumlah surat aktif per kategori milik anggota
        $jumlahPerJenis = PengajuanSurat::where('id_anggota', $anggota->id_anggota)
            ->selectRaw('jenis_keterangan, count(*) as total')
            ->groupBy('jenis_keterangan')
            ->pluck('total', 'jenis_keterangan')
            ->toArray();

        return view('anggota.surat.index', compact(
            'daftarSurat', 
            'daftarJenis', 
            'kategoriMeta', 
            'anggota', 
            'maxSizeKb', 
            'jumlahPerJenis'
        ));
    }

    /**
     * Tampilkan form pembuatan pengajuan surat keterangan baru.
     */
    public function create(Request $request): View
    {
        $anggota      = auth('anggota')->user();
        $daftarJenis  = PengajuanSurat::daftarJenisKeterangan();
        $kategoriMeta = PengajuanSurat::kategoriMeta();
        $maxSizeKb    = config('presensi.surat_max_size_kb', 2048);
        $jenisTerpilih = $request->query('jenis', old('jenis_keterangan', 'izin'));

        return view('anggota.surat.create', compact('anggota', 'daftarJenis', 'kategoriMeta', 'maxSizeKb', 'jenisTerpilih'));
    }

    /**
     * Simpan pengajuan surat keterangan personel ke database dan storage privat.
     */
    public function store(PengajuanSuratRequest $request): RedirectResponse
    {
        $anggota = auth('anggota')->user();

        // 1. Simpan berkas PDF ke storage privat dengan nama acak
        $file = $request->file('file_surat');
        $namaFileAcak = 'surat_' . $anggota->nrp . '_' . date('Ymd_His') . '_' . Str::random(12) . '.pdf';
        $direktoriTujuan = storage_path('app/private_surat');

        if (! File::exists($direktoriTujuan)) {
            File::makeDirectory($direktoriTujuan, 0755, true);
        }

        $file->move($direktoriTujuan, $namaFileAcak);

        // 2. Simpan rekaman pengajuan
        PengajuanSurat::create([
            'id_anggota'       => $anggota->id_anggota,
            'jenis_keterangan' => $request->jenis_keterangan,
            'tanggal_mulai'    => $request->tanggal_mulai,
            'tanggal_selesai'  => $request->tanggal_selesai,
            'keterangan'       => $request->keterangan,
            'file_surat'       => $namaFileAcak,
            'status_pengajuan' => 'menunggu',
            'id_admin'         => null,
            'catatan_admin'    => null,
            'tanggal_diproses' => null,
        ]);

        return redirect()->route('anggota.surat.index')
            ->with('sukses', 'Pengajuan surat keterangan berhasil dikirim dan saat ini berstatus Menunggu verifikasi admin.');
    }

    /**
     * Batalkan pengajuan surat oleh personel (hanya jika masih berstatus 'menunggu').
     */
    public function cancel(int $id): RedirectResponse
    {
        $anggota   = auth('anggota')->user();
        $pengajuan = PengajuanSurat::where('id_pengajuan', $id)
            ->where('id_anggota', $anggota->id_anggota)
            ->firstOrFail();

        if ($pengajuan->status_pengajuan !== 'menunggu') {
            return redirect()->route('anggota.surat.index')
                ->with('error', 'Pengajuan tidak dapat dibatalkan karena telah diproses oleh admin.');
        }

        // Hapus file dari disk privat
        $pathFile = storage_path('app/private_surat/' . $pengajuan->file_surat);
        if (File::exists($pathFile)) {
            File::delete($pathFile);
        }

        $pengajuan->delete();

        return redirect()->route('anggota.surat.index')
            ->with('sukses', 'Pengajuan surat berhasil dibatalkan dan dihapus dari sistem.');
    }

    /**
     * Akses aman file PDF surat milik anggota yang sedang login.
     */
    public function file(int $id): BinaryFileResponse
    {
        $anggota   = auth('anggota')->user();
        $pengajuan = PengajuanSurat::where('id_pengajuan', $id)
            ->where('id_anggota', $anggota->id_anggota)
            ->firstOrFail();

        $pathFile = storage_path('app/private_surat/' . $pengajuan->file_surat);

        if (! File::exists($pathFile)) {
            abort(404, 'Berkas surat PDF tidak ditemukan di server.');
        }

        $downloadName = 'Surat_' . ucfirst($pengajuan->jenis_keterangan) . '_' . $anggota->nrp . '.pdf';

        return response()->file($pathFile, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $downloadName . '"',
        ]);
    }
}


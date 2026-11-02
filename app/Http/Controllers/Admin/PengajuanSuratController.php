<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\PengajuanSurat;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * PengajuanSuratController (Admin)
 * Mengelola verifikasi, persetujuan, penolakan, dan pemantauan surat keterangan personel.
 */
class PengajuanSuratController extends Controller
{
    /**
     * Tampilkan daftar pengajuan surat dengan filter komprehensif.
     */
    public function index(Request $request): View
    {
        $query = PengajuanSurat::with(['anggota', 'admin']);

        // Filter status
        if ($request->filled('status')) {
            $query->where('status_pengajuan', $request->status);
        }

        // Filter jenis keterangan
        if ($request->filled('jenis')) {
            $query->where('jenis_keterangan', $request->jenis);
        }

        // Filter anggota tertentu
        if ($request->filled('id_anggota')) {
            $query->where('id_anggota', $request->id_anggota);
        }

        // Filter periode tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_mulai', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_selesai', '<=', $request->tanggal_selesai);
        }

        // Pencarian Nama / NRP
        if ($request->filled('q')) {
            $cari = $request->q;
            $query->whereHas('anggota', function ($q) use ($cari) {
                $q->where('nama', 'like', "%{$cari}%")
                  ->orWhere('nrp', 'like', "%{$cari}%");
            });
        }

        // Ringkasan Statistik
        $totalPengajuan = PengajuanSurat::count();
        $totalMenunggu  = PengajuanSurat::where('status_pengajuan', 'menunggu')->count();
        $totalDisetujui = PengajuanSurat::where('status_pengajuan', 'disetujui')->count();
        $totalDitolak   = PengajuanSurat::where('status_pengajuan', 'ditolak')->count();

        $daftarSurat   = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $daftarJenis   = PengajuanSurat::daftarJenisKeterangan();
        $daftarAnggota = Anggota::orderBy('nama', 'asc')->get(['id_anggota', 'nama', 'nrp']);

        return view('admin.surat.index', compact(
            'daftarSurat',
            'daftarJenis',
            'daftarAnggota',
            'totalPengajuan',
            'totalMenunggu',
            'totalDisetujui',
            'totalDitolak'
        ));
    }

    /**
     * Tampilkan detail pengajuan surat dan panel persetujuan/penolakan.
     */
    public function show(int $id): View
    {
        $pengajuan = PengajuanSurat::with(['anggota', 'admin'])->findOrFail($id);

        return view('admin.surat.show', compact('pengajuan'));
    }

    /**
     * Proses persetujuan atau penolakan pengajuan surat oleh admin.
     */
    public function proses(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'status'        => ['required', 'in:disetujui,ditolak'],
            'catatan_admin' => ['required_if:status,ditolak', 'nullable', 'string', 'max:1000'],
        ], [
            'status.required'            => 'Keputusan status pengajuan wajib dipilih.',
            'status.in'                  => 'Status harus berupa disetujui atau ditolak.',
            'catatan_admin.required_if'  => 'Catatan alasan penolakan wajib diisi apabila pengajuan ditolak.',
            'catatan_admin.max'          => 'Catatan admin tidak boleh lebih dari 1000 karakter.',
        ]);

        $pengajuan = PengajuanSurat::with('anggota')->findOrFail($id);
        $admin     = auth('admin')->user();
        $statusBaru = $request->status;

        $pengajuan->update([
            'status_pengajuan' => $statusBaru,
            'id_admin'         => $admin->id_admin,
            'catatan_admin'    => $request->catatan_admin,
            'tanggal_diproses' => Carbon::now(),
        ]);

        // Catat ke Log Aktivitas
        $tindakanTeks = $statusBaru === 'disetujui' ? 'Menyetujui' : 'Menolak';
        $logKeterangan = "{$tindakanTeks} pengajuan surat ({$pengajuan->label_jenis}) personel: {$pengajuan->anggota->nama} (NRP: {$pengajuan->anggota->nrp})";
        if ($statusBaru === 'ditolak' && $request->filled('catatan_admin')) {
            $logKeterangan .= " - Alasan: {$request->catatan_admin}";
        }

        LogAktivitasService::catat(
            idAdmin: $admin->id_admin,
            aktivitas: "{$tindakanTeks} Pengajuan Surat",
            tabelTerkait: 'pengajuan_surat',
            keterangan: $logKeterangan
        );

        $pesan = $statusBaru === 'disetujui'
            ? "Pengajuan surat personel {$pengajuan->anggota->nama} berhasil disetujui."
            : "Pengajuan surat personel {$pengajuan->anggota->nama} telah ditolak dengan catatan.";

        return redirect()->route('admin.surat.index')->with('sukses', $pesan);
    }

    /**
     * Pratinjau / unduh berkas PDF surat yang disimpan di storage privat.
     */
    public function file(int $id): BinaryFileResponse
    {
        $pengajuan = PengajuanSurat::with('anggota')->findOrFail($id);
        $pathFile  = storage_path('app/private_surat/' . $pengajuan->file_surat);

        if (! File::exists($pathFile)) {
            abort(404, 'Berkas surat PDF tidak ditemukan di server.');
        }

        $downloadName = 'Surat_' . ucfirst($pengajuan->jenis_keterangan) . '_' . ($pengajuan->anggota->nrp ?? 'Dokumen') . '.pdf';

        return response()->file($pathFile, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $downloadName . '"',
        ]);
    }
}

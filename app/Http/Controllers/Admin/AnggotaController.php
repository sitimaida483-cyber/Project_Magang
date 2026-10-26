<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnggotaRequest;
use App\Models\Anggota;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AnggotaController
 * Mengelola Master Data Personel / Anggota Bidtik Polda Kalsel (CRUD).
 */
class AnggotaController extends Controller
{
    /**
     * Tampilkan daftar anggota dengan filter pencarian dan status.
     */
    public function index(Request $request): View
    {
        $query = Anggota::query()->withCount('presensi');

        // Filter pencarian berdasarkan NRP atau Nama
        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                  ->orWhere('nrp', 'like', "%{$keyword}%")
                  ->orWhere('jabatan', 'like', "%{$keyword}%")
                  ->orWhere('unit_kerja', 'like', "%{$keyword}%");
            });
        }

        // Filter status keaktifan
        if ($request->filled('status') && in_array($request->status, ['aktif', 'nonaktif'])) {
            $query->where('status', $request->status);
        }

        $anggotaList = $query->orderByDesc('id_anggota')->paginate(10)->withQueryString();

        return view('admin.anggota.index', compact('anggotaList'));
    }

    /**
     * Tampilkan formulir tambah anggota baru.
     */
    public function create(): View
    {
        return view('admin.anggota.create');
    }

    /**
     * Simpan data anggota baru ke database.
     */
    public function store(AnggotaRequest $request): RedirectResponse
    {
        $admin = auth('admin')->user();

        $anggota = Anggota::create([
            'id_admin'   => $admin->id_admin,
            'nrp'        => $request->nrp,
            'nama'       => $request->nama,
            'jabatan'    => $request->jabatan,
            'unit_kerja' => $request->unit_kerja,
            'no_telepon' => $request->no_telepon,
            'status'     => $request->status,
        ]);

        // Catat aksi penambahan anggota ke log aktivitas
        LogAktivitasService::catat(
            idAdmin: $admin->id_admin,
            aktivitas: 'Tambah Anggota',
            tabelTerkait: 'anggota',
            keterangan: "Menambahkan anggota baru: {$anggota->nama} (NRP: {$anggota->nrp}, Jabatan: {$anggota->jabatan})"
        );

        return redirect()->route('admin.anggota.index')
            ->with('sukses', "Anggota '{$anggota->nama}' berhasil didaftarkan.");
    }

    /**
     * Tampilkan profil dan riwayat presensi detail anggota.
     */
    public function show(int $id): View
    {
        $anggota = Anggota::with(['admin', 'perangkatNotifikasi'])
            ->withCount('presensi')
            ->findOrFail($id);

        // Ambil 10 presensi terbaru anggota ini
        $riwayatPresensi = $anggota->presensi()
            ->orderByDesc('tanggal')
            ->orderByDesc('waktu')
            ->limit(10)
            ->get();

        // Statistik kehadiran
        $totalMasuk = $anggota->presensi()->where('jenis_presensi', 'masuk')->count();
        $totalPulang = $anggota->presensi()->where('jenis_presensi', 'pulang')->count();
        $totalTerlambat = $anggota->presensi()->where('status_kehadiran', 'terlambat')->count();

        return view('admin.anggota.show', compact(
            'anggota', 
            'riwayatPresensi', 
            'totalMasuk', 
            'totalPulang', 
            'totalTerlambat'
        ));
    }

    /**
     * Tampilkan formulir edit data anggota.
     */
    public function edit(int $id): View
    {
        $anggota = Anggota::findOrFail($id);
        return view('admin.anggota.edit', compact('anggota'));
    }

    /**
     * Perbarui data anggota.
     */
    public function update(AnggotaRequest $request, int $id): RedirectResponse
    {
        $anggota = Anggota::findOrFail($id);
        $admin   = auth('admin')->user();

        $anggota->update([
            'nrp'        => $request->nrp,
            'nama'       => $request->nama,
            'jabatan'    => $request->jabatan,
            'unit_kerja' => $request->unit_kerja,
            'no_telepon' => $request->no_telepon,
            'status'     => $request->status,
        ]);

        // Catat aksi pengubahan data anggota ke log aktivitas
        LogAktivitasService::catat(
            idAdmin: $admin->id_admin,
            aktivitas: 'Ubah Anggota',
            tabelTerkait: 'anggota',
            keterangan: "Mengubah data anggota: {$anggota->nama} (NRP: {$anggota->nrp}, Status: {$anggota->status})"
        );

        return redirect()->route('admin.anggota.index')
            ->with('sukses', "Data anggota '{$anggota->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus data anggota dengan validasi riwayat presensi.
     */
    public function destroy(int $id): RedirectResponse
    {
        $anggota = Anggota::findOrFail($id);
        $admin   = auth('admin')->user();

        // ATURAN BISNIS: Anggota tidak boleh dihapus jika sudah punya data presensi
        if ($anggota->presensi()->exists()) {
            return back()->with('error', "Anggota '{$anggota->nama}' (NRP: {$anggota->nrp}) TIDAK DAPAT DIHAPUS karena memiliki riwayat data presensi. Disarankan untuk mengubah status menjadi 'Nonaktif'.");
        }

        $nama = $anggota->nama;
        $nrp  = $anggota->nrp;

        $anggota->delete();

        // Catat aksi penghapusan anggota ke log aktivitas
        LogAktivitasService::catat(
            idAdmin: $admin->id_admin,
            aktivitas: 'Hapus Anggota',
            tabelTerkait: 'anggota',
            keterangan: "Menghapus data anggota: {$nama} (NRP: {$nrp})"
        );

        return redirect()->route('admin.anggota.index')
            ->with('sukses', "Anggota '{$nama}' berhasil dihapus dari sistem.");
    }
}

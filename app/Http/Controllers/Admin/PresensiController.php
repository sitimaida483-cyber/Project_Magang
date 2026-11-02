<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Pengaturan;
use App\Models\PengajuanSurat;
use App\Models\Presensi;
use App\Services\HaversineService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * PresensiController (Admin Panel)
 * Mengelola rekap kehadiran personel, filter periode/anggota/keterangan surat,
 * melihat detail berkas foto & tanda tangan, serta ekspor PDF dan Excel.
 */
class PresensiController extends Controller
{
    /**
     * Tampilkan rekap daftar presensi personel dengan integrasi surat keterangan disetujui.
     */
    public function index(Request $request): View
    {
        $semuaData = $this->getRekapData($request);

        $totalPresensi   = $semuaData->count();
        $totalMasuk      = $semuaData->where('jenis_presensi', 'masuk')->count();
        $totalPulang     = $semuaData->where('jenis_presensi', 'pulang')->count();
        $totalTerlambat  = $semuaData->where('status_kehadiran', 'terlambat')->count();
        $totalKeterangan = $semuaData->where('is_surat', true)->count();

        // Paginasi manual untuk gabungan Collection presensi & surat
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $itemsForCurrentPage = $semuaData->slice(($page - 1) * $perPage, $perPage)->values();

        $presensiList = new LengthAwarePaginator(
            $itemsForCurrentPage,
            $totalPresensi,
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        // Daftar dropdown filter
        $anggotaList = Anggota::orderBy('nama')->get(['id_anggota', 'nama', 'nrp']);
        $daftarJenisKeterangan = PengajuanSurat::daftarJenisKeterangan();

        return view('admin.presensi.index', compact(
            'presensiList',
            'anggotaList',
            'daftarJenisKeterangan',
            'totalPresensi',
            'totalMasuk',
            'totalPulang',
            'totalTerlambat',
            'totalKeterangan'
        ));
    }

    /**
     * Tampilkan detail rekaman presensi lengkap dengan foto, tanda tangan, dan peta GPS.
     */
    public function show(int $id): View
    {
        $presensi = Presensi::with('anggota')->findOrFail($id);
        $pengaturan = Pengaturan::first();

        $jarakKeKantor = null;
        if ($pengaturan && $presensi->latitude && $presensi->longitude) {
            $jarakKeKantor = HaversineService::hitungJarak(
                (float) $presensi->latitude,
                (float) $presensi->longitude,
                (float) $pengaturan->latitude,
                (float) $pengaturan->longitude
            );
        }

        return view('admin.presensi.show', compact('presensi', 'pengaturan', 'jarakKeKantor'));
    }

    /**
     * Ekspor rekapitulasi data presensi ke format PDF dinas resmi (Landscape A4).
     */
    public function eksporPdf(Request $request): Response
    {
        $dataPresensi = $this->getRekapData($request);
        $pengaturan   = Pengaturan::first();

        $periodeTeks = 'Semua Periode';
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $tglMulai = Carbon::parse($request->tanggal_mulai)->translatedFormat('d M Y');
            $tglSelesai = Carbon::parse($request->tanggal_selesai)->translatedFormat('d M Y');
            $periodeTeks = "{$tglMulai} s/d {$tglSelesai}";
        } elseif ($request->filled('tanggal_mulai')) {
            $periodeTeks = "Mulai " . Carbon::parse($request->tanggal_mulai)->translatedFormat('d M Y');
        } elseif ($request->filled('tanggal_selesai')) {
            $periodeTeks = "Sampai " . Carbon::parse($request->tanggal_selesai)->translatedFormat('d M Y');
        }

        $pdf = Pdf::loadView('admin.presensi.pdf', [
            'dataPresensi' => $dataPresensi,
            'pengaturan'   => $pengaturan,
            'periodeTeks'  => $periodeTeks,
            'waktuCetak'   => Carbon::now()->translatedFormat('d F Y, H:i') . ' WITA',
            'namaAdmin'    => auth('admin')->user()->nama ?? 'Administrator',
        ]);

        $pdf->setPaper('a4', 'landscape');

        $namaFile = 'Rekap_Presensi_Bidtik_Polda_Kalsel_' . date('Ymd_His') . '.pdf';
        return $pdf->download($namaFile);
    }

    /**
     * Ekspor rekapitulasi data presensi ke format Excel XLSX menggunakan PhpSpreadsheet.
     */
    public function eksporExcel(Request $request): StreamedResponse
    {
        $dataPresensi = $this->getRekapData($request);
        $pengaturan   = Pengaturan::first();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Presensi');

        // Judul Laporan
        $sheet->setCellValue('A1', 'KEPOLISIAN DAERAH KALIMANTAN SELATAN');
        $sheet->setCellValue('A2', 'BIDANG TEKNOLOGI INFORMASI DAN KOMUNIKASI (BIDTIK)');
        $sheet->setCellValue('A3', 'LAPORAN REKAPITULASI PRESENSI PERSONEL');

        $periodeTeks = 'Semua Periode';
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $periodeTeks = $request->tanggal_mulai . ' s/d ' . $request->tanggal_selesai;
        }
        $sheet->setCellValue('A4', 'Periode: ' . $periodeTeks . ' | Dicetak: ' . Carbon::now()->format('d/m/Y H:i') . ' WITA');

        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');
        $sheet->mergeCells('A4:J4');

        $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Tabel Kolom (Baris 6)
        $headers = [
            'A6' => 'NO',
            'B6' => 'TANGGAL',
            'C6' => 'WAKTU (WITA)',
            'D6' => 'NRP',
            'E6' => 'NAMA PERSONEL',
            'F6' => 'JABATAN',
            'G6' => 'UNIT KERJA',
            'H6' => 'JENIS PRESENSI',
            'I6' => 'STATUS KEHADIRAN',
            'J6' => 'KETERANGAN / IZIN',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Styling Header (Navy Background, Text Putih Bold)
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0E2238'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];
        $sheet->getStyle('A6:J6')->applyFromArray($headerStyle);
        $sheet->getRowDimension(6)->setRowHeight(26);

        // Pengisian Data Baris
        $row = 7;
        foreach ($dataPresensi as $index => $p) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, Carbon::parse($p->tanggal)->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $p->waktu ? Carbon::parse($p->waktu)->format('H:i:s') : '-');
            $sheet->setCellValueExplicit('D' . $row, $p->anggota->nrp ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $row, $p->anggota->nama ?? '-');
            $sheet->setCellValue('F' . $row, $p->anggota->jabatan ?? '-');
            $sheet->setCellValue('G' . $row, $p->anggota->unit_kerja ?? '-');

            if ($p->is_surat ?? false) {
                $sheet->setCellValue('H' . $row, 'SURAT KETERANGAN');
                $sheet->setCellValue('I' . $row, 'DISPENSASI DINAS');
                $sheet->setCellValue('J' . $row, $p->keterangan_surat ?? '-');
                $sheet->getStyle('I' . $row)->getFont()->getColor()->setRGB('2563EB'); // Biru
            } else {
                $sheet->setCellValue('H' . $row, strtoupper($p->jenis_presensi));
                $sheet->setCellValue('I' . $row, $p->status_kehadiran === 'tepat_waktu' ? 'TEPAT WAKTU' : 'TERLAMBAT');
                $sheet->setCellValue('J' . $row, $p->keterangan_surat ?? '-');

                if ($p->status_kehadiran === 'terlambat') {
                    $sheet->getStyle('I' . $row)->getFont()->getColor()->setRGB('DC2626'); // Merah
                } else {
                    $sheet->getStyle('I' . $row)->getFont()->getColor()->setRGB('16A34A'); // Hijau
                }
            }

            // Alignment center untuk kolom No, Tanggal, Waktu, NRP, Jenis, Status
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Border seluruh tabel data
        $lastRow = max(7, $row - 1);
        $sheet->getStyle('A7:J' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Auto-width kolom
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $namaFile = 'Rekap_Presensi_Bidtik_Polda_Kalsel_' . date('Ymd_His') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $namaFile . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Helper: Ambil gabungan rekaman presensi fisik dan tanggal izin/cuti/dinas yang telah disetujui.
     */
    private function getRekapData(Request $request): Collection
    {
        // 1. Query Rekaman Presensi Fisik
        $presensiQuery = Presensi::with('anggota');

        if ($request->filled('tanggal_mulai')) {
            $presensiQuery->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $presensiQuery->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }
        if ($request->filled('id_anggota')) {
            $presensiQuery->where('id_anggota', $request->id_anggota);
        }
        if ($request->filled('jenis_presensi') && in_array($request->jenis_presensi, ['masuk', 'pulang'])) {
            $presensiQuery->where('jenis_presensi', $request->jenis_presensi);
        }
        if ($request->filled('status_kehadiran') && in_array($request->status_kehadiran, ['tepat_waktu', 'terlambat'])) {
            $presensiQuery->where('status_kehadiran', $request->status_kehadiran);
        }

        $presensiRecords = $presensiQuery->get();

        // 2. Query Pengajuan Surat yang DISETUJUI
        $suratQuery = PengajuanSurat::with('anggota')
            ->where('status_pengajuan', 'disetujui');

        if ($request->filled('id_anggota')) {
            $suratQuery->where('id_anggota', $request->id_anggota);
        }
        if ($request->filled('jenis_keterangan')) {
            $suratQuery->where('jenis_keterangan', $request->jenis_keterangan);
        }
        if ($request->filled('tanggal_mulai')) {
            $suratQuery->whereDate('tanggal_selesai', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $suratQuery->whereDate('tanggal_mulai', '<=', $request->tanggal_selesai);
        }

        $daftarSuratDisetujui = $suratQuery->get();

        // Peta presensi yang sudah ada: ["{id_anggota}_{tanggal}"] => true
        $petaPresensiAda = [];
        foreach ($presensiRecords as $p) {
            $tglStr = Carbon::parse($p->tanggal)->toDateString();
            $petaPresensiAda["{$p->id_anggota}_{$tglStr}"] = true;
        }

        // Peta surat disetujui: ["{id_anggota}_{tanggal}"] => PengajuanSurat
        $petaSurat = [];
        foreach ($daftarSuratDisetujui as $s) {
            $tMulai   = Carbon::parse($s->tanggal_mulai);
            $tSelesai = Carbon::parse($s->tanggal_selesai);

            $curr = $tMulai->copy();
            while ($curr->lte($tSelesai)) {
                $tglKey = "{$s->id_anggota}_{$curr->toDateString()}";
                $petaSurat[$tglKey] = $s;
                $curr->addDay();
            }
        }

        // Sematkan keterangan_surat pada presensi fisik jika ada
        foreach ($presensiRecords as $p) {
            $tglStr = Carbon::parse($p->tanggal)->toDateString();
            $tglKey = "{$p->id_anggota}_{$tglStr}";
            $p->is_surat = false;
            $p->keterangan_surat = isset($petaSurat[$tglKey]) ? $petaSurat[$tglKey]->label_jenis : null;
        }

        // 3. Tambahkan baris keterangan untuk tanggal tercakup surat disetujui yang BELUM ada presensi
        $barisSurat = collect();

        // Hanya tambahkan baris surat jika filter jenis_presensi bukan masuk/pulang
        if (! $request->filled('jenis_presensi') || $request->jenis_presensi === 'keterangan') {
            $batasMulai   = $request->filled('tanggal_mulai') ? Carbon::parse($request->tanggal_mulai) : null;
            $batasSelesai = $request->filled('tanggal_selesai') ? Carbon::parse($request->tanggal_selesai) : null;

            foreach ($daftarSuratDisetujui as $surat) {
                $tMulai   = Carbon::parse($surat->tanggal_mulai);
                $tSelesai = Carbon::parse($surat->tanggal_selesai);

                if ($batasMulai && $tMulai->lt($batasMulai)) {
                    $tMulai = $batasMulai->copy();
                }
                if ($batasSelesai && $tSelesai->gt($batasSelesai)) {
                    $tSelesai = $batasSelesai->copy();
                }

                $curr = $tMulai->copy();
                while ($curr->lte($tSelesai)) {
                    $tglStr = $curr->toDateString();
                    $tglKey = "{$surat->id_anggota}_{$tglStr}";

                    // Hanya jika BELUM ada presensi fisik pada tanggal ini
                    if (! isset($petaPresensiAda[$tglKey])) {
                        if (! $request->filled('status_kehadiran') || $request->status_kehadiran === 'keterangan') {
                            $barisSurat->push((object) [
                                'id_presensi'      => null,
                                'is_surat'         => true,
                                'id_pengajuan'     => $surat->id_pengajuan,
                                'id_anggota'       => $surat->id_anggota,
                                'anggota'          => $surat->anggota,
                                'tanggal'          => $tglStr,
                                'waktu'            => null,
                                'jenis_presensi'   => 'keterangan',
                                'status_kehadiran' => 'keterangan',
                                'keterangan_surat' => $surat->label_jenis,
                                'file_surat'       => $surat->file_surat,
                                'latitude'         => null,
                                'longitude'        => null,
                                'created_at'       => $surat->created_at,
                            ]);
                        }
                    }
                    $curr->addDay();
                }
            }
        }

        // 4. Jika difilter berdasarkan jenis_keterangan:
        if ($request->filled('jenis_keterangan')) {
            $jenisTarget = PengajuanSurat::daftarJenisKeterangan()[$request->jenis_keterangan] ?? $request->jenis_keterangan;
            $presensiRecords = $presensiRecords->filter(function ($p) use ($jenisTarget) {
                return $p->keterangan_surat === $jenisTarget;
            });
            $barisSurat = $barisSurat->filter(function ($s) use ($jenisTarget) {
                return $s->keterangan_surat === $jenisTarget;
            });
        }

        // Gabungkan dan urutkan berdasarkan tanggal DESC, waktu DESC
        return $presensiRecords->concat($barisSurat)->sortByDesc(function ($item) {
            return $item->tanggal . ' ' . ($item->waktu ?? '00:00:00');
        })->values();
    }
}


<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Pengaturan;
use App\Models\Presensi;
use App\Services\HaversineService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * PresensiController (Admin Panel)
 * Mengelola rekap kehadiran personel, filter periode/anggota,
 * melihat detail berkas foto & tanda tangan, serta ekspor PDF dan Excel.
 */
class PresensiController extends Controller
{
    /**
     * Tampilkan rekap daftar presensi personel dengan filter lengkap.
     */
    public function index(Request $request): View
    {
        $query = Presensi::with('anggota');

        // 1. Filter Tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }

        // 2. Filter Anggota
        if ($request->filled('id_anggota')) {
            $query->where('id_anggota', $request->id_anggota);
        }

        // 3. Filter Jenis Presensi (Masuk / Pulang)
        if ($request->filled('jenis_presensi') && in_array($request->jenis_presensi, ['masuk', 'pulang'])) {
            $query->where('jenis_presensi', $request->jenis_presensi);
        }

        // 4. Filter Status Kehadiran (Tepat Waktu / Terlambat)
        if ($request->filled('status_kehadiran') && in_array($request->status_kehadiran, ['tepat_waktu', 'terlambat'])) {
            $query->where('status_kehadiran', $request->status_kehadiran);
        }

        // Hitung ringkasan data terfilter
        $cloneQuery = clone $query;
        $totalPresensi = $cloneQuery->count();
        $totalMasuk = (clone $query)->where('jenis_presensi', 'masuk')->count();
        $totalPulang = (clone $query)->where('jenis_presensi', 'pulang')->count();
        $totalTerlambat = (clone $query)->where('status_kehadiran', 'terlambat')->count();

        // Data presensi dengan paginasi 15 data
        $presensiList = $query->orderByDesc('tanggal')
            ->orderByDesc('waktu')
            ->paginate(15)
            ->withQueryString();

        // Daftar anggota untuk dropdown filter
        $anggotaList = Anggota::orderBy('nama')->get();

        return view('admin.presensi.index', compact(
            'presensiList',
            'anggotaList',
            'totalPresensi',
            'totalMasuk',
            'totalPulang',
            'totalTerlambat'
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
        $dataPresensi = $this->getQueryTerfilter($request)->get();
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
        $dataPresensi = $this->getQueryTerfilter($request)->get();
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

        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('A3:I3');
        $sheet->mergeCells('A4:I4');

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
        $sheet->getStyle('A6:I6')->applyFromArray($headerStyle);
        $sheet->getRowDimension(6)->setRowHeight(26);

        // Pengisian Data Baris
        $row = 7;
        foreach ($dataPresensi as $index => $p) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, Carbon::parse($p->tanggal)->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, Carbon::parse($p->waktu)->format('H:i:s'));
            $sheet->setCellValueExplicit('D' . $row, $p->anggota->nrp ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $row, $p->anggota->nama ?? '-');
            $sheet->setCellValue('F' . $row, $p->anggota->jabatan ?? '-');
            $sheet->setCellValue('G' . $row, $p->anggota->unit_kerja ?? '-');
            $sheet->setCellValue('H' . $row, strtoupper($p->jenis_presensi));
            $sheet->setCellValue('I' . $row, $p->status_kehadiran === 'tepat_waktu' ? 'TEPAT WAKTU' : 'TERLAMBAT');

            // Alignment center untuk kolom No, Tanggal, Waktu, NRP, Jenis, Status
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Beri warna teks status kehadiran
            if ($p->status_kehadiran === 'terlambat') {
                $sheet->getStyle('I' . $row)->getFont()->getColor()->setRGB('DC2626'); // Merah
            } else {
                $sheet->getStyle('I' . $row)->getFont()->getColor()->setRGB('16A34A'); // Hijau
            }

            $row++;
        }

        // Border seluruh tabel data
        $lastRow = max(7, $row - 1);
        $sheet->getStyle('A7:I' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Auto-width kolom
        foreach (range('A', 'I') as $col) {
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
     * Helper query builder terfilter.
     */
    private function getQueryTerfilter(Request $request)
    {
        $query = Presensi::with('anggota');

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }
        if ($request->filled('id_anggota')) {
            $query->where('id_anggota', $request->id_anggota);
        }
        if ($request->filled('jenis_presensi') && in_array($request->jenis_presensi, ['masuk', 'pulang'])) {
            $query->where('jenis_presensi', $request->jenis_presensi);
        }
        if ($request->filled('status_kehadiran') && in_array($request->status_kehadiran, ['tepat_waktu', 'terlambat'])) {
            $query->where('status_kehadiran', $request->status_kehadiran);
        }

        return $query->orderBy('tanggal', 'asc')->orderBy('waktu', 'asc');
    }
}

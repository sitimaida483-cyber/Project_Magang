<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Presensi Personel Bidtik Polda Kalsel</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
            size: a4 landscape;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #1e293b;
            line-height: 1.3;
        }

        /* Kop Surat Resmi Dinas */
        .kop-surat {
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 14px;
            text-align: center;
        }
        .kop-surat h4 {
            margin: 0;
            font-size: 11pt;
            font-weight: normal;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .kop-surat h3 {
            margin: 2px 0 0 0;
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .kop-surat p {
            margin: 3px 0 0 0;
            font-size: 8.5pt;
            color: #475569;
        }

        /* Judul Laporan */
        .judul-laporan {
            text-align: center;
            margin-bottom: 16px;
        }
        .judul-laporan h2 {
            margin: 0;
            font-size: 12pt;
            text-decoration: underline;
            text-transform: uppercase;
            font-weight: bold;
        }
        .judul-laporan .sub-judul {
            margin: 4px 0 0 0;
            font-size: 9pt;
            color: #334155;
        }

        /* Tabel Data Presensi */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th {
            background-color: #0e2238;
            color: #ffffff;
            font-weight: bold;
            font-size: 8.5pt;
            padding: 7px 5px;
            border: 1px solid #0e2238;
            text-align: center;
            vertical-align: middle;
            text-transform: uppercase;
        }
        table.data-table td {
            font-size: 8.5pt;
            padding: 6px 5px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }

        /* Badge Status */
        .badge-tepat {
            color: #166534;
            font-weight: bold;
        }
        .badge-terlambat {
            color: #991b1b;
            font-weight: bold;
        }
        .badge-masuk {
            color: #1e40af;
            font-weight: bold;
        }
        .badge-pulang {
            color: #0e7490;
            font-weight: bold;
        }

        /* Tanda Tangan */
        .ttd-container {
            width: 100%;
            margin-top: 25px;
        }
        .ttd-box {
            float: right;
            width: 260px;
            text-align: center;
            font-size: 9.5pt;
        }
        .ttd-space {
            height: 65px;
        }
        .clear {
            clear: both;
        }

        /* Footer */
        .footer-cetak {
            margin-top: 15px;
            font-size: 8pt;
            color: #64748b;
            font-style: italic;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT KEPOLISIAN -->
    <div class="kop-surat">
        <h4>KEPOLISIAN NEGARA REPUBLIK INDONESIA</h4>
        <h4>DAERAH KALIMANTAN SELATAN</h4>
        <h3>BIDANG TEKNOLOGI INFORMASI DAN KOMUNIKASI</h3>
        <p>
            {{ $pengaturan->alamat_kantor ?? 'Jl. S. Parman No.16, Antasan Besar, Kec. Banjarmasin Tengah, Kota Banjarmasin, Kalimantan Selatan 70114' }}
        </p>
    </div>

    <!-- JUDUL LAPORAN -->
    <div class="judul-laporan">
        <h2>REKAPITULASI KEHADIRAN PERSONEL</h2>
        <div class="sub-judul">
            <strong>Periode:</strong> {{ $periodeTeks }} &nbsp;|&nbsp;
            <strong>Total Rekaman:</strong> {{ $dataPresensi->count() }} Data
        </div>
    </div>

    <!-- TABEL DATA -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 75px;">TANGGAL</th>
                <th style="width: 65px;">WAKTU</th>
                <th style="width: 90px;">NRP / NIP</th>
                <th>NAMA PERSONEL</th>
                <th style="width: 110px;">JABATAN</th>
                <th style="width: 95px;">SUBBID / UNIT</th>
                <th style="width: 65px;">JENIS</th>
                <th style="width: 95px;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataPresensi as $index => $p)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d/m/Y') }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($p->waktu)->format('H:i:s') }} WITA</td>
                    <td class="text-center text-bold">{{ $p->anggota->nrp ?? '-' }}</td>
                    <td>{{ $p->anggota->nama ?? '-' }}</td>
                    <td>{{ $p->anggota->jabatan ?? '-' }}</td>
                    <td>{{ $p->anggota->unit_kerja ?? '-' }}</td>
                    <td class="text-center">
                        <span class="{{ $p->jenis_presensi === 'masuk' ? 'badge-masuk' : 'badge-pulang' }}">
                            {{ strtoupper($p->jenis_presensi) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="{{ $p->status_kehadiran === 'tepat_waktu' ? 'badge-tepat' : 'badge-terlambat' }}">
                            {{ $p->status_kehadiran === 'tepat_waktu' ? 'TEPAT WAKTU' : 'TERLAMBAT' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada catatan presensi pada periode dan kriteria filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN RESMI -->
    <div class="ttd-container">
        <div class="ttd-box">
            Banjarmasin, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
            <strong>A.n. KEPALA BIDANG TIK POLDA KALSEL</strong><br>
            Administrator Sistem Presensi,
            <div class="ttd-space"></div>
            <strong><u>{{ $namaAdmin }}</u></strong><br>
            <span>Polda Kalimantan Selatan</span>
        </div>
        <div class="clear"></div>
    </div>

    <!-- FOOTER INFORMASI SISTEM -->
    <div class="footer-cetak">
        Dokumen ini diterbitkan secara otomatis melalui Sistem Presensi Web Bidtik Polda Kalimantan Selatan pada {{ $waktuCetak }}.
    </div>

</body>
</html>

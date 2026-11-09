<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Kehadiran Personel - Bidtik Polda Kalsel</title>
    <style>
        @page {
            margin: 1.2cm 1.2cm 1.2cm 1.2cm;
            size: a4 landscape;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #17263F;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat Resmi Kepolisian */
        .kop-surat {
            border-bottom: 2px solid #17263F;
            padding-bottom: 8px;
            margin-bottom: 14px;
            text-align: left;
        }
        .kop-surat h4 {
            margin: 0;
            font-size: 10pt;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #17263F;
        }
        .kop-surat h3 {
            margin: 2px 0 0 0;
            font-size: 11pt;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #17263F;
        }
        .kop-surat p {
            margin: 3px 0 0 0;
            font-size: 8pt;
            color: #52637A;
        }

        /* Judul Laporan */
        .judul-laporan {
            text-align: left;
            margin-bottom: 16px;
        }
        .judul-laporan h2 {
            margin: 0;
            font-size: 13pt;
            text-transform: uppercase;
            font-weight: 700;
            color: #17263F;
            letter-spacing: 0.5px;
        }
        .judul-laporan .sub-judul {
            margin: 4px 0 0 0;
            font-size: 9pt;
            color: #52637A;
        }

        /* Tabel Data Presensi */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th {
            background-color: #FAFCFE;
            color: #17263F;
            font-weight: 700;
            font-size: 8pt;
            padding: 8px 6px;
            border: 1px solid #DCE3ED;
            text-align: left;
            vertical-align: middle;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.data-table td {
            font-size: 8.5pt;
            padding: 7px 6px;
            border: 1px solid #DCE3ED;
            vertical-align: middle;
            color: #17263F;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #F8FAFD;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-bold { font-weight: 700; }

        /* Badge Status */
        .badge-tepat {
            color: #15803D;
            font-weight: 700;
        }
        .badge-terlambat {
            color: #DC2626;
            font-weight: 700;
        }
        .badge-masuk {
            color: #254BD6;
            font-weight: 700;
        }
        .badge-pulang {
            color: #17263F;
            font-weight: 700;
        }

        /* Pengesahan */
        .ttd-container {
            width: 100%;
            margin-top: 25px;
        }
        .ttd-box {
            float: right;
            width: 280px;
            text-align: center;
            font-size: 9pt;
            color: #17263F;
        }
        .ttd-space {
            height: 60px;
        }
        .clear {
            clear: both;
        }

        /* Footer */
        .footer-cetak {
            margin-top: 20px;
            font-size: 8pt;
            color: #52637A;
        }
    </style>
</head>
<body>

    <!-- KOP RESMI KEPOLISIAN (Page 39) -->
    <div class="kop-surat">
        <h4>KEPOLISIAN NEGARA REPUBLIK INDONESIA</h4>
        <h4>DAERAH KALIMANTAN SELATAN</h4>
        <h3>BIDANG TEKNOLOGI INFORMASI DAN KOMUNIKASI</h3>
        <p>
            {{ $pengaturan->alamat_kantor ?? 'Jl. S. Parman No.16, Antasan Besar, Kec. Banjarmasin Tengah, Kota Banjarmasin, Kalimantan Selatan 70114' }}
        </p>
    </div>

    <!-- JUDUL REKAPITULASI (Page 39) -->
    <div class="judul-laporan">
        <h2>REKAPITULASI KEHADIRAN PERSONEL</h2>
        <div class="sub-judul">
            Periode: {{ $periodeTeks }}
        </div>
    </div>

    <!-- TABEL 10 KOLOM (Page 39) -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 25px;">NO</th>
                <th style="width: 70px;">TANGGAL</th>
                <th style="width: 75px;">WAKTU</th>
                <th style="width: 80px;">NRP / NIP</th>
                <th>NAMA PERSONEL</th>
                <th style="width: 110px;">JABATAN</th>
                <th style="width: 95px;">SUBBID / UNIT</th>
                <th class="text-center" style="width: 70px;">JENIS</th>
                <th class="text-center" style="width: 95px;">STATUS</th>
                <th style="width: 90px;">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataPresensi as $index => $p)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/y') }}</td>
                    <td>{{ $p->waktu ? \Carbon\Carbon::parse($p->waktu)->format('H:i') . ' WITA' : '-' }}</td>
                    <td class="text-bold font-monospace">{{ $p->anggota->nrp ?? '-' }}</td>
                    <td class="text-bold">{{ $p->anggota->nama ?? '-' }}</td>
                    <td>{{ $p->anggota->jabatan ?? '-' }}</td>
                    <td>{{ $p->anggota->unit_kerja ?? '-' }}</td>
                    <td class="text-center">
                        @if($p->is_surat ?? false)
                            <span style="color: #254BD6; font-weight: 700;">{{ strtoupper($p->jenis_keterangan ?? 'SURAT') }}</span>
                        @else
                            <span class="{{ $p->jenis_presensi === 'masuk' ? 'badge-masuk' : 'badge-pulang' }}">
                                {{ strtoupper($p->jenis_presensi) }}
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($p->is_surat ?? false)
                            <span class="badge-tepat">DISETUJUI</span>
                        @else
                            <span class="{{ $p->status_kehadiran === 'tepat_waktu' ? 'badge-tepat' : 'badge-terlambat' }}">
                                {{ $p->status_kehadiran === 'tepat_waktu' ? 'TEPAT WAKTU' : 'TERLAMBAT' }}
                            </span>
                        @endif
                    </td>
                    <td>
                        {{ $p->keterangan_surat ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 24px; color: #52637A;">
                        Tidak ada catatan presensi pada periode dan kriteria filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- PENGESAHAN (Page 39) -->
    <div class="ttd-container">
        <div class="ttd-box">
            Mengetahui,<br>
            <strong>Pejabat Pengesahan</strong>
            <div class="ttd-space"></div>
            <strong><u>{{ $namaAdmin ?? 'Administrator' }}</u></strong><br>
            <span>Bid TIK Polda Kalsel</span>
        </div>
        <div class="clear"></div>
    </div>

    <!-- FOOTER INFORMASI CETAK (Page 39) -->
    <div class="footer-cetak">
        Dicetak {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}, {{ \Carbon\Carbon::now()->format('H:i') }} WITA / {{ $namaAdmin ?? 'Administrator' }}
    </div>

</body>
</html>

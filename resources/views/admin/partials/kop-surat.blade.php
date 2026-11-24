{{-- 
    Partial Kop Surat Resmi Kepolisian Daerah Kalimantan Selatan
    Format resmi:
    [LOGO POLRI] (di tengah atas teks kop, sekitar 70-80px)
    KEPOLISIAN NEGARA REPUBLIK INDONESIA (bold, kapital, tengah)
    DAERAH KALIMANTAN SELATAN (bold, kapital, tengah)
    Jalan Bina Praja Timur Sei Tiung Banjarbaru 70734 (garis bawah / underline)
    Banjarbaru, [tanggal unduh] (rata kanan)
--}}
@php
    // Tanggal unduh otomatis menggunakan timezone Asia/Makassar dan locale Indonesia
    $tanggalUnduhTeks = $tanggalUnduhTeks ?? ('Banjarbaru, ' . \Illuminate\Support\Carbon::now('Asia/Makassar')->locale('id')->translatedFormat('d F Y'));

    // Pengambilan dan enkapsulasi file Logo Polri ke Base64 (aman dan stabil untuk DomPDF)
    $logoSrc = null;
    if (!empty($logoPolriBase64)) {
        $logoSrc = $logoPolriBase64;
    } else {
        $logoPath = public_path('images/logo-polri.png');
        if (file_exists($logoPath) && is_readable($logoPath)) {
            $imageData = @file_get_contents($logoPath);
            if ($imageData !== false) {
                $imageMime = mime_content_type($logoPath) ?: 'image/png';
                $logoSrc = 'data:' . $imageMime . ';base64,' . base64_encode($imageData);
            }
        }
    }
@endphp

<div class="kop-surat-container" style="width: 100%; margin-bottom: 14px; font-family: Arial, Helvetica, sans-serif;">
    {{-- 1. Logo Polri (Jika File public/images/logo-polri.png Tersedia) --}}
    @if($logoSrc)
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
            <tr>
                <td style="text-align: center; vertical-align: middle;">
                    <img src="{{ $logoSrc }}" alt="Logo Polri" style="width: 75px; height: auto; max-height: 80px;" />
                </td>
            </tr>
        </table>
    @endif

    {{-- 2. Teks Kop Surat Resmi (Rata Tengah, Huruf Kapital, Dua Baris Pertama Tebal, Alamat Bergaris Bawah) --}}
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="text-align: center; line-height: 1.25;">
                <div style="font-size: 11pt; font-weight: bold; text-transform: uppercase; color: #000000; letter-spacing: 0.5px;">
                    KEPOLISIAN NEGARA REPUBLIK INDONESIA
                </div>
                <div style="font-size: 11pt; font-weight: bold; text-transform: uppercase; color: #000000; letter-spacing: 0.5px; margin-top: 1px;">
                    DAERAH KALIMANTAN SELATAN
                </div>
                <div style="font-size: 9pt; color: #000000; margin-top: 3px;">
                    <u>Jalan Bina Praja Timur Sei Tiung Banjarbaru 70734</u>
                </div>
            </td>
        </tr>
    </table>

    {{-- 3. Baris Tanggal Unduh (Rata Kanan Tepat di Bawah Baris Alamat) --}}
    <table style="width: 100%; border-collapse: collapse; margin-top: 6px;">
        <tr>
            <td style="text-align: right; font-size: 9pt; color: #000000;">
                {{ $tanggalUnduhTeks }}
            </td>
        </tr>
    </table>
</div>

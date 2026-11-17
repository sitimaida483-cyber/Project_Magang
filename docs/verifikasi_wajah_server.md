# Kajian & Rekomendasi Verifikasi Wajah di Sisi Server (Server-Side Face Verification)

**Sistem:** PresTIK (Presensi Digital Bidtik Polda Kalimantan Selatan)  
**Status:** Dokumen Analisis Arsitektur & Rekomendasi (Tahap 3 - Dokumentasi Perencanaan)  
**Tanggal:** 16 November 2026  

---

## 1. Latar Belakang & Urgensi

Saat ini, sistem PresTIK memverifikasi keberadaan wajah di sisi klien (*client-side*) menggunakan pustaka Google MediaPipe yang berjalan di peramban web personel. Verifikasi ini memiliki kelebihan:
- Sangat cepat dan tidak membebani server backend.
- Berjalan langsung di peramban tanpa latensi jaringan.

Namun, verifikasi di sisi peramban memiliki keterbatasan struktural:
1. **Manipulasi Sisi Klien:** Kode JavaScript di peramban dapat diakses dan dimodifikasi oleh pengguna mahir, atau dimanipulasi menggunakan peramban headless/ekstensi.
2. **Kamera Virtual / Injeksi Foto:** Pengguna dapat mengarahkan sumber kamera ke berkas gambar atau video statis menggunakan perisian seperti OBS Virtual Camera atau Android Mock Camera.
3. **Belum Mencocokkan Identitas (*Face Matching / 1:1 Verification*):** MediaPipe di klien saat ini hanya mendeteksi *apakah ada wajah manusia di depan kamera*, bukan *apakah wajah tersebut benar-benar milik Bripda X yang terdaftar*.

Untuk mengatasi kelemahan tersebut secara komprehensif, diperlukan **Verifikasi Wajah di Sisi Server (*Server-Side Face Verification*)**.

---

## 2. Opsi Arsitektur Verifikasi Server

Terdapat dua pendekatan utama yang dapat diadopsi oleh Bidtik Polda Kalsel:

### Opsi 1: Layanan Pihak Ketiga Berbasis Cloud API (SaaS)
Mengirimkan foto selfie ke API pengenalan wajah milik penyedia cloud publik komersial (misalnya: AWS Rekognition, Google Cloud Vision Face API, Azure Face API, atau Face++).

- **Mekanisme Kerja:**
  Backend Laravel mengirimkan berkas foto selfie dan foto master personel via REST API HTTPS ke cloud publik, lalu menerima skor kecocokan (*similarity score*, misal: 98.4%).
- **Kelebihan:**
  - Tidak memerlukan server/komputasi khusus di hosting (backend PHP cukup melakukan HTTP cURL).
  - Akurasi model kecerdasan buatan kelas industri dengan fitur *liveness detection* (deteksi keaslian wajah/anti-spoofing).
  - Waktu implementasi relatif cepat (tersedia SDK resmi).
- **Kekurangan & Risiko:**
  - **Kedaulatan & Privasi Data Polri:** Foto biometrik personel kepolisian keluar ke server pihak ketiga komersial/asing, yang berpotensi melanggar ketentuan kerahasiaan dan keamanan data institusi kepolisian.
  - **Ketergantungan Akses Internet Luar:** Server aplikasi PresTIK wajib selalu memiliki koneksi internet keluar tanpa hambatan firewall.
  - **Biaya Berlangganan Berulang (*Recurring Cost*):** Biaya dihitung per panggilan API (rata-rata $1.00 – $1.50 per 1.000 panggilan). Untuk 100 personel yang presensi 2x sehari (6.000 panggilan/bulan), timbul pengeluaran rutin bulanan.

---

### Opsi 2: Layanan Mikro Mandiri Berbasis Python (Self-Hosted / On-Premise)
Membangun microservice mandiri berbasis Python (FastAPI / Flask) menggunakan pustaka pengenalan wajah bersumber terbuka mutakhir (*open-source*), seperti **InsightFace (ArcFace)** atau **DeepFace**, yang di-deploy pada VPS atau server lokal Polda Kalsel.

- **Mekanisme Kerja:**
  Aplikasi Laravel berkomunikasi secara internal via jaringan lokal (*Private Network / Localhost HTTP*) ke layanan Python:
  `Laravel PHP` $\xrightarrow{\text{POST /verify-face}}$ `Python FastAPI (InsightFace)` $\xrightarrow{\text{JSON result}}$ `Laravel PHP`
- **Kelebihan:**
  - **Kedaulatan Data 100% Terjaga:** Seluruh data biometrik wajah personel tetap berada di dalam infrastruktur server milik Polda Kalsel, tidak pernah dikirim ke pihak luar.
  - **Bebas Biaya Lisensi & API:** Pustaka open-source bebas biaya langganan bulanan tanpa batas jumlah personel maupun presensi.
  - **Dapat Beroperasi di Jaringan Tertutup (*Air-Gapped / Intranet*):** Tetap berjalan normal meski server tidak memiliki akses internet publik.
- **Kekurangan & Kebutuhan Hosting:**
  - Memerlukan instalasi lingkungan Python (Python 3.10+, PyTorch/ONNX Runtime) dan dependensi C++ di server.
  - Membutuhkan alokasi memori RAM minimal 2 GB hingga 4 GB khusus untuk model komputasi inferensi AI.
  - Memerlukan pemeliharaan dua aplikasi (Laravel PHP dan FastAPI Python).

---

### Opsi 3: Ekstensi PHP OpenCV / ONNX Runtime C++ Langsung
Menjalankan inferensi model wajah langsung di dalam proses PHP menggunakan ekstensi OpenCV PHP atau PHP-FFI dengan ONNX Runtime.

- **Kelebihan:** Tidak perlu service Python terpisah.
- **Kekurangan:** Sangat sulit dipasang di lingkungan shared hosting/cPanel umum, berisiko menyebabkan *memory leak* pada PHP-FPM, dan pustaka PHP untuk AI jauh tertinggal dibandingkan ekosistem Python. **Tidak disarankan.**

---

## 3. Matriks Perbandingan Komprehensif

| Kriteria Evaluasi | Opsi 1: Cloud API (AWS/Google/Azure) | Opsi 2: Python Service On-Premise (InsightFace) |
| :--- | :--- | :--- |
| **Kedaulatan Data (Privasi)** | ⚠️ **Rendah**: Foto biometrik keluar ke pihak ketiga | 🛡️ **Sangat Tinggi**: 100% data di server Polda Kalsel |
| **Kesesuaian Standar Polri** | ⚠️ Memerlukan persetujuan kepatuhan keamanan data | ✅ **Sesuai**: Data kedinasan tersimpan internal |
| **Biaya Operasional** | Berbayar per permintaan (~$15 - $50/bulan) | **Gratis** (Hanya biaya sewa VPS biasa) |
| **Kebutuhan Server** | Cukup Shared Hosting / VPS Standar (1 GB RAM) | Memerlukan VPS Linux (RAM minimal 2–4 GB, 2 vCPU) |
| **Ketergantungan Internet** | Wajib internet publik selalu aktif | Dapat berjalan di jaringan lokal / intranet |
| **Latensi Inferensi** | 300 ms – 800 ms (tergantung koneksi ISP) | 150 ms – 300 ms (komunikasi jaringan lokal) |
| **Kemudahan Pemeliharaan** | Mudah (dikelola pihak penyedia cloud) | Memerlukan pemeliharaan service sistem (systemd/supervisor) |

---

## 4. Rekomendasi untuk Bidtik Polda Kalsel

Berdasarkan analisis keamanan institusional kepolisian, pertimbangan privasi data biometrik personel, dan efisiensi anggaran jangka panjang:

### **REKOMENDASI UTAMA: Mengadopsi Opsi 2 (Microservice Python Mandiri dengan InsightFace)**

**Pertimbangan Rekomendasi:**
1. **Keamanan & Regulasi Biometrik:** Wajah personel kepolisian merupakan data biometrik sensitif yang menyangkut identitas aparat penegak hukum. Menyimpan dan memprosesnya secara mandiri (*self-hosted*) adalah langkah terbaik untuk memitigasi risiko kebocoran data biometrik ke pihak eksternal.
2. **Efisiensi Anggaran:** Tidak membebani satuan kerja dengan biaya langganan cloud bulanan.
3. **Kesiapan Infrastruktur VPS:** Mengingat PresTIK saat ini telah memiliki akses VPS Ubuntu Linux (`magang@vpslama`), instalasi microservice FastAPI Python dengan model ArcFace berbobot ringan (MobileFaceNet / Buffalo_s berukuran ~50 MB) sangat layak diimplementasikan dengan kebutuhan RAM sekitar 1–2 GB.

### **Rencana Tahapan Implementasi (Jika Disetujui di Masa Mendatang):**
1. **Pendaftaran Foto Master:** Menambahkan menu di panel Admin untuk mengunggah 1 foto wajah acuan resmi (*enrolled face*) untuk setiap personel.
2. **Pengembangan Microservice:** Membuat skrip FastAPI Python sederhana (~100 baris kode) dengan endpoint `POST /match-face` yang menerima foto selfie dan foto master, lalu mengembalikan nilai persentase kesamaan (*cosine similarity score*).
3. **Integrasi Backend Laravel:** Mengintegrasikan pemanggilan HTTP internal dari `PresensiController` ke microservice sebelum menyimpan status presensi.


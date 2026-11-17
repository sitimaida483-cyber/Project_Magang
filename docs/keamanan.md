# Panduan Keamanan & Deteksi Anomali Presensi PresTIK

Dokumen ini menjelaskan mekanisme penguatan keamanan, aturan deteksi anomali telemetri presensi, panduan konfigurasi ambang batas, serta kepatuhan privasi pada sistem **PresTIK (Presensi Digital Bidtik Polda Kalimantan Selatan)**.

---

## 1. Prinsip Keamanan Sistem Presensi Berbasis Web

Presensi berbasis peramban (*web browser*) beroperasi di lingkungan perangkat klien (HP / laptop personel) yang secara inheren tidak dapat dikontrol 100% oleh server. Beberapa potensi manipulasi yang dapat dilakukan oleh pihak tidak bertanggung jawab meliputi:
1. *Fake GPS / Mock Location* (aplikasi pemalsu koordinat lokasi satelit).
2. *Virtual Camera / Webcam Injection* (mengalirkan video atau gambar statis yang telah disiapkan sebelumnya alih-alih tangkapan kamera nyata).
3. Manipulasi permintaan HTTP / *Replay Attack* menggunakan rekaman request terdahulu.

Tujuan dari sistem perlindungan PresTIK adalah **mempersulit, mendeteksi, dan mencatat kejanggalan secara objektif** tanpa mengganggu kelancaran tugas operasional personel kepolisian yang sah.

---

## 2. Aturan Deteksi Anomali (Tahap 2)

Sistem mengimplementasikan 6 lapisan analisis deteksi kejanggalan (`DeteksiAnomaliService`) pada setiap pengiriman presensi:

### Aturan A: Jarak Terlalu Sempurna ke Titik Kantor
- **Deskripsi:** Perangkat GPS fisik di dunia nyata selalu memiliki fluktuasi/deviasi alami beberapa meter akibat cuaca, gedung, dan pergerakan satelit. Jika jarak posisi presensi ke titik pusat koordinat kantor tercatat sangat presisi (default: `< 1.0 meter`), maka terindikasi kuat menggunakan aplikasi Fake GPS yang mematok pin koordinat tepat di titik acuan.
- **Tindakan:** Ditandai dengan catatan *"Jarak GPS ke titik kantor luar biasa presisi, terindikasi koordinat palsu / Fake GPS"*.

### Aturan B: Anomali Akurasi Sinyal GPS
- **Deskripsi:** API Geolocation peramban mengembalikan parameter `position.coords.accuracy` (dalam satuan meter).
  - Jika akurasi kosong atau `<= 0`: Perangkat tidak melaporkan kualitas sinyal.
  - Jika akurasi sangat kecil (`<= 1.0 meter`): Nilai akurasi ini tidak realistis untuk sensor GPS ponsel sipil (biasanya berkisar antara 5–30 meter), seringkali merupakan *hardcoded value* dari emulator atau modul Mock Location.
  - Jika akurasi sangat buruk (`> 150.0 meter`): Sinyal satelit tidak akurat, deviasi lokasi terlalu lebar.
- **Tindakan:** Ditandai sebagai anomali akurasi GPS.

### Aturan C: Koordinat Kembar Identik pada Hari yang Sama
- **Deskripsi:** Sinyal GPS alami memiliki derau mikro pada digit desimal ke-5 dan ke-6 (presisi sub-meter). Presensi dari perangkat berbeda atau waktu berbeda yang memiliki koordinat desimal (hingga 6 angka di belakang koma) identik persis terindikasi memasukkan koordinat statis yang disalin (*copy-paste*).
- **Tindakan:** Memeriksa seluruh rekaman pada tanggal bersangkutan dan menandai kejanggalan jika ditemukan koordinat kembar identik.

### Aturan D: Penggunaan Foto Selfie Bekas / Berulang
- **Deskripsi:** Setiap foto selfie yang dikirimkan personel didekode secara ketat, dibersihkan metadata/EXIF-nya, di-*re-encode* ulang ke format JPEG murni, lalu dihitung nilai *hash* kriptografis SHA-256 (`foto_hash`).
- **Tindakan:** Jika seorang personel mengirimkan foto yang memiliki nilai hash SHA-256 identik persis dengan foto presensi terdahulunya, sistem menandai upaya penggunaan foto lama (*photo reuse / photo injection*).

### Aturan E: Kejanggalan Rentang Waktu Presensi
- **Deskripsi:**
  - Presensi Pulang yang diajukan kurang dari batas wajar (default: `< 60 menit`) setelah presensi Masuk tercatat.
  - Presensi Masuk yang baru dikirimkan setelah jam pulang kantor resmi (`jam_keluar` pada Pengaturan Kantor).
- **Tindakan:** Ditandai sebagai kejanggalan durasi dinas/kehadiran.

### Aturan F: Batasan Subnet Jaringan Dinas Kantor (Opsional)
- **Deskripsi:** Pengaturan daftar blok CIDR jaringan dinas Polda Kalsel (misal: Wi-Fi dinas atau VPN kepolisian).
- **Tindakan:** Secara *default* fitur ini nonaktif (kosong). Jika diisi pada konfigurasi, presensi dari IP di luar subnet dinas akan ditandai.

---

## 3. Mode Operasional Anomali (`PRESENSI_ANOMALI_MODE`)

PresTIK mendukung dua mode penanganan anomali yang dapat dikonfigurasi melalui file `.env`:

1. **Mode `"catat"` (Default / Direkomendasikan):**
   - Presensi personel **tetap diterima dan berhasil dicatat**, sehingga personel di lapangan tidak terblokir saat terjadi kendala sinyal atau cuaca buruk.
   - Kolom `flag_anomali` otomatis diatur ke `true` dan daftar penyebab kejanggalan disimpan pada kolom `catatan_anomali`.
   - Pada panel Admin (Dashboard Ringkasan, Rekap Absensi, dan Detail Presensi), muncul lencana kuning **"Perlu Ditinjau"** agar pimpinan/administrator dapat melakukan verifikasi manual.

2. **Mode `"tolak"`:**
   - Presensi yang terindikasi memiliki anomali langsung ditolak oleh server dengan pesan kesalahan umum: *"Presensi tidak dapat diproses karena data sinyal lokasi atau perangkat Anda tidak memenuhi syarat verifikasi."*
   - Hanya gunakan mode ini apabila seluruh personel telah menggunakan perangkat dengan sinyal GPS stabil dan kebijakan dinas menghendaki penegakan mutlak.

---

## 4. Panduan Mengubah Ambang Batas Deteksi

Seluruh parameter ambang batas disimpan terpusat di `config/presensi.php` dan dapat disesuaikan langsung lewat `.env` tanpa mengubah kode program:

```env
# Mode penanganan anomali: 'catat' atau 'tolak'
PRESENSI_ANOMALI_MODE=catat

# Aturan A: Batas jarak minimal yang dicurigai sebagai Fake GPS (meter)
ANOMALI_JARAK_MIN_METER=1.0

# Aturan B: Batas akurasi minimal & maksimal (meter)
ANOMALI_AKURASI_MIN_METER=1.0
ANOMALI_AKURASI_MAX_METER=150.0

# Aturan C: Cek duplikasi koordinat 6 desimal pada hari yang sama (true/false)
ANOMALI_CEK_KOORDINAT_DUPLIKAT=true

# Aturan D: Cek foto selfie bekas/kembar (true/false)
ANOMALI_CEK_FOTO_DUPLIKAT=true

# Aturan E: Durasi minimal antara presensi masuk dan pulang (menit)
ANOMALI_PULANG_MIN_MENIT=60

# Aturan F: Daftar subnet IP kantor (opsional, pisahkan koma)
# PRESENSI_ALLOWED_SUBNETS=192.168.1.0/24,10.0.0.0/8
```

---

## 5. Catatan Privasi & Kepatuhan Data Personel

Sistem mencatat parameter telemetri perangkat pada setiap kali presensi dilakukan:
1. **Alamat IP (`ip_address`):** Diperoleh secara sah melalui proxy terpercaya (`trustedproxy`). Digunakan untuk memastikan asal jaringan pengirim.
2. **User-Agent Peramban (`user_agent`):** Informasi jenis perangkat lunak peramban dan model sistem operasi perangkat.
3. **Akurasi GPS (`akurasi_gps`):** Estimasi margin kesalahan sensor GPS dalam satuan meter.
4. **Hash SHA-256 Foto (`foto_hash`):** Nilai sidik jari digital berkas foto untuk mendeteksi pengunggahan berkas duplikat.

**Ketentuan Privasi:**
- Data telemetri disimpan semata-mata untuk kebutuhan **audit integritas kedinasan Polri**, pembuktian kehadiran sah, dan investigasi internal jika terjadi sanggahan absensi.
- Data telemetri hanya dapat diakses oleh Administrator yang memiliki hak akses resmi melalui panel terautentikasi dan tidak disebarluaskan ke pihak ketiga.
- Berkas fisik swafoto dan tanda tangan tersimpan pada direktori penyimpanan privat (`storage/app/private_presensi/`) yang tidak dapat diakses secara publik melalui URL web biasa.


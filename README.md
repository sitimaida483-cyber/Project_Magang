# Sistem Presensi Web Bidtik Polda Kalimantan Selatan

Sistem Presensi Web resmi untuk **Bidang Teknologi Informasi dan Komunikasi (Bidtik) Kepolisian Daerah Kalimantan Selatan**. Sistem ini dirancang untuk mempermudah pencatatan kehadiran personel Polri/PNS secara daring, akurat, dan terverifikasi secara spasial (geofencing) serta biometrik (swafoto & tanda tangan digital).

---

## Daftar Isi
1. [Fitur Utama](#fitur-utama)
2. [Arsitektur & Keamanan](#arsitektur--keamanan)
3. [Struktur Database](#struktur-database)
4. [Persyaratan Sistem](#persyaratan-sistem)
5. [Panduan Instalasi & Konfigurasi](#panduan-instalasi--konfigurasi)
6. [Kredensial Default](#kredensial-default)
7. [Menjalankan Background Scheduler](#menjalankan-background-scheduler)
8. [Panduan Pengujian di Perangkat Mobile (HP)](#panduan-pengujian-di-perangkat-mobile-hp)

---

## Fitur Utama

### 1. Panel Publik Presensi Anggota (`/presensi`)
- **Pencarian NRP Realtime:** Anggota cukup memasukkan NRP/NIP untuk memuat nama, pangkat/jabatan, dan unit kerja tanpa proses login akun yang rumit.
- **Validasi Geofencing Spasial (Formula Haversine):** Sistem memeriksa koordinat GPS perangkat secara *realtime*. Presensi hanya dapat dikirim jika anggota berada di dalam radius sah yang ditentukan admin dari titik pusat kantor (misal 100 meter).
- **Kamera Depan Otomatis (Swafoto / Selfie):** Menggunakan API kamera browser (`facingMode: 'user'`) untuk memverifikasi kehadiran fisik personel di lokasi.
- **Tanda Tangan Digital:** Personel membubuhkan paraf/tanda tangan elektronik langsung pada kanvas layar sentuh atau kursor mouse menggunakan `signature_pad`.
- **Web Push Notification:** Otomatis meminta izin notifikasi browser pada presensi pertama untuk menerima pengingat kehadiran terjadwal.

### 2. Panel Administrasi Terpusat (`/admin`)
- **Dashboard Ringkasan WITA:** Menampilkan metrik harian (*Presensi Masuk, Presensi Pulang, Personel Terlambat, Total Anggota Terdaftar*) serta tabel aktivitas hari ini dalam waktu Indonesia Tengah (WITA).
- **Pengaturan Geofence Kantor & Jam Operasional (`/admin/pengaturan`):**
  - Titik koordinat kantor dapat digeser secara interaktif menggunakan peta Leaflet.js (*drag marker*).
  - Radius kehadiran dapat diubah dinamis dengan pratinjau lingkaran instan.
  - Penentuan jam operasional masuk, batas toleransi keterlambatan, dan jam pulang.
  - Tombol deteksi koordinat GPS otomatis perangkat admin.
- **Manajemen Data Anggota (`/admin/anggota`):**
  - Kelola data personel (NRP, Nama, Jabatan, Unit Kerja, No HP, Status Aktif).
  - *Integritas Data:* Anggota yang memiliki riwayat presensi tidak dapat dihapus permanen untuk mencegah hilangnya data audit (*disarankan nonaktifkan status*).
- **Rekapitulasi Absensi & Ekspor Dokumen (`/admin/presensi`):**
  - Filter multi-kriteria: rentang tanggal, personel, jenis presensi (masuk/pulang), dan status kehadiran (tepat waktu/terlambat).
  - Halaman detail kehadiran lengkap dengan foto selfie, tanda tangan digital, dan peta GPS jarak ke kantor.
  - **Cetak Laporan PDF Resmi Dinas:** Dokumen A4 Landscape siap cetak dengan Kop Surat Kepolisian Daerah Kalimantan Selatan Bidang TIK dan kolom tanda tangan pengesahan.
  - **Ekspor Dokumen Excel (.xlsx):** Format spreadsheet dengan pewarnaan status dan pemformatan sel resmi.
- **Peta Spasial Sebaran Presensi (`/admin/peta`):**
  - Peta interaktif Leaflet.js yang memplot seluruh lokasi presensi anggota pada tanggal tertentu.
  - Pin dengan penanda warna: 🟢 Hijau (Tepat Waktu), 🔴 Merah (Terlambat), 🔵 Biru (Pulang).
  - Popup interaktif dengan foto personel, identitas, waktu presensi, dan tautan ke detail kehadiran.
  - Tombol kontrol cepat *"Fokus Titik Kantor"* dan *"Tampilkan Semua Titik"*.
- **Audit Trail Log Aktivitas (`/admin/log`):**
  - Mencatat setiap aksi administratif (login, logout, modifikasi pengaturan kantor, manipulasi data personel) lengkap dengan alamat IP, waktu presensi WITA, dan detail keterangan aksi.

---

## Arsitektur & Keamanan

- **Penyimpanan Berkas Privat:** Foto selfie dan tanda tangan presensi disimpan di direktori privat (`storage/app/private_presensi/`), bukan symlink publik. Berkas hanya dapat diakses melalui rute aman terautentikasi oleh admin login.
- **Proteksi Brute-Force:** Pembatasan percobaan login admin maksimum 5 kali per menit menggunakan Laravel Rate Limiter.
- **Timezone & Lokalisasi:** Dikonfigurasi penuh ke Zona Waktu Indonesia Tengah (`Asia/Makassar` / WITA) dan bahasa Indonesia (`id`).
- **Web Push Tanpa Pihak Ketiga Berbayar:** Menggunakan protokol Web Push VAPID murni berbasis Service Worker browser (`minishlink/web-push`).

---

## Struktur Database

Sistem dibangun di atas 7 tabel relasional utama:

1. **`admin`** (PK: `id_admin`): Kredensial dan akun administrator sistem.
2. **`anggota`** (PK: `id_anggota`): Profil personel kepolisian / PNS Bidtik.
3. **`presensi`** (PK: `id_presensi`): Catatan riwayat presensi masuk/pulang, titik GPS, foto, dan tanda tangan.
4. **`pengaturan`** (PK: `id_pengaturan`): Konfigurasi titik koordinat kantor, radius meter, dan jam kerja.
5. **`perangkat_notifikasi`** (PK: `id_perangkat`): Endpoint langganan Web Push browser perangkat personel.
6. **`notifikasi`** (PK: `id_notifikasi`): Riwayat pesan notifikasi yang dikirimkan.
7. **`log_aktivitas`** (PK: `id_log`): Jejak audit keamanan setiap tindakan admin di sistem.

---

## Persyaratan Sistem

- **PHP:** Versi 8.2 atau yang lebih baru (Disarankan PHP 8.4)
- **Ekstensi PHP Wajib:** `pdo_mysql`, `openssl`, `mbstring`, `fileinfo`, `gd`, `zip`, `curl`
- **Database:** MySQL 8.0+ atau MariaDB 10.4+
- **Composer:** Versi 2.x
- **Browser:** Chrome, Edge, Firefox, atau Safari versi terbaru dengan dukungan Camera & Geolocation API.

---

## Panduan Instalasi & Konfigurasi

### 1. Kloning Repositori & Pasang Dependensi
```bash
git clone <url-repo-anda>
cd Project_Magang
composer install
```

### 2. Konfigurasi Lingkungan (`.env`)
Salin file template `.env.example` menjadi `.env` jika belum ada:
```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan konfigurasi koneksi database MySQL Anda di `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=Project_Magang
DB_USERNAME=root
DB_PASSWORD=
```

Pastikan pengaturan zona waktu dan bahasa telah tersetting:
```env
APP_TIMEZONE=Asia/Makassar
APP_LOCALE=id
```

### 3. Migrasi Database & Seeding Data Awal
Jalankan perintah migrasi beserta seeder untuk menginisialisasi seluruh tabel dan data pengujian:
```bash
php artisan migrate:fresh --seed
```

### 4. Tautkan Berkas Publik
```bash
php artisan storage:link
```

### 5. Jalankan Web Server Lokal
```bash
php artisan serve
```
Akses aplikasi melalui peramban di: `http://127.0.0.1:8000`

---

## Kredensial Default

### Akun Administrator Panel
- **URL Login:** `http://127.0.0.1:8000/admin/login`
- **Username:** `admin`
- **Password:** `password123`

### Contoh NRP Personel (Halaman Presensi Publik)
Untuk melakukan uji coba presensi di `http://127.0.0.1:8000/presensi`, gunakan salah satu NRP contoh berikut:
- `92010450` — Bripka Ahmad Fauzi, S.H. (Subbid Tekinfo)
- `94080231` — Briptu Siti Nurhaliza (Subbid Tekkom)
- `88050112` — Aipda Hendra Wijaya (Subbid Tekinfo)
- `97120344` — Bripda Rian Pratama (Subbid Wabprof)
- `198504122010011002` — Pembina M. Syarifuddin, S.Kom. (PNS Bidtik)

---

## Menjalankan Background Scheduler

Sistem dilengkapi tugas otomatis (*Scheduled Task*) untuk mengirimkan Web Push pengingat presensi 15 menit sebelum jam masuk dan jam pulang kepada seluruh personel yang telah mengaktifkan notifikasi:

```bash
# Menjalankan worker scheduler secara terus menerus (Development)
php artisan schedule:work

# Atau menjalankan perintah pengingat secara manual
php artisan presensi:kirim-pengingat
```

Pada lingkungan produksi (Server Linux/Windows), daftarkan perintah `php artisan schedule:run` ke dalam Cron Job sistem per 1 menit:
```bash
* * * * * cd /path/to/Project_Magang && php artisan schedule:run >> /dev/null 2>&1
```

---

## Panduan Pengujian di Perangkat Mobile (HP)

Karena browser membatasi akses **Kamera Depan** dan **Geolocation GPS** pada jaringan non-HTTPS kecuali `localhost`:

1. Pastikan komputer dan ponsel terhubung ke jaringan Wi-Fi yang sama.
2. Jalankan server Laravel dengan mengikat IP lokal komputer Anda:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```
3. Akses via ponsel dengan memasukkan `http://IP-KOMPUTER-ANDA:8000/presensi`.
4. *Tips:* Untuk menguji fitur kamera dan GPS di ponsel dengan enkripsi HTTPS penuh, Anda dapat menggunakan alat tunneling seperti **ngrok**:
   ```bash
   ngrok http 8000
   ```
   Buka URL HTTPS yang disediakan oleh ngrok di browser ponsel Anda untuk pengalaman presensi mobile yang lancar dan optimal.

---

## Tim Pengembang
Dikembangkan untuk **Bidang Teknologi Informasi dan Komunikasi (Bidtik) Kepolisian Daerah Kalimantan Selatan**.

# Product Requirements Document (PRD) - UT Tes Formatif

---

## 1. Pendahuluan

### 1.1 Visi & Tujuan Produk
**"UT Tes Formatif"** adalah platform aplikasi web pendidikan interaktif yang dirancang untuk memfasilitasi mahasiswa Universitas Terbuka (UT) di seluruh Indonesia dalam melaksanakan evaluasi belajar mandiri, kuis tes formatif per modul Buku Materi Pokok (BMP), dan simulasi Ujian Akhir Semester (UAS). Aplikasi ini bertujuan meningkatkan kesiapan dan prestasi akademik mahasiswa melalui pengujian mandiri yang dapat diakses kapan saja dan di mana saja, didukung oleh transparansi skor dan papan peringkat (*leaderboard*).

### 1.2 Ruang Lingkup Sistem (Scope)
Sistem ini mencakup tiga portal utama:
1. **Portal Publik:** Beranda katalog mata kuliah, papan peringkat global & per modul/UAS, formulir saran, regulasi privasi/kebijakan, dan fasilitas unduh aplikasi APK Android.
2. **Portal Mahasiswa:** Otentikasi sesi terproteksi, penjelajahan katalog BMP, pengerjaan kuis formatif per modul berbasis timer, simulasi UAS berbobot SKS, riwayat evaluasi & kunci pembahasan, bookmark mata kuliah favorit, pengaturan profil & pas foto, serta donasi/paket premium.
3. **Panel Dasbor Administrator:** Manajemen master data mata kuliah & pengunggahan sampul WebP, pemeliharaan bank soal formatif, audit pengguna mahasiswa & aktivasi premium, publikasi pop-up pengumuman, pemantauan log aktivitas & skor, analisis statistik kota, serta pengelolaan metode pembayaran donasi.

---

## 2. Arsitektur & Tumpukan Teknologi (Tech Stack)

* **Backend Engine:** CodeIgniter 4 (PHP 8.2+) dengan arsitektur MVC, Middleware Filters (`adminAuth`, `authApi`, `invalidchars`, `maintenance`), dan RESTful `ResourceController` JSON.
* **Frontend Interface:** Vue.js 3 (via CDN `vue.global.prod.js`) yang diintegrasikan secara modular pada setiap view HTML, Bootstrap 5.3 (CDN), Bootstrap Icons 1.11, dan Vanilla CSS kustom per mata kuliah.
* **Komunikasi Data Asinkron:** Axios / Fetch API yang terpusat melalui `public/js/global_mixin.js`.
* **Penyimpanan Berkas Terproteksi:** Bank Soal JSON dan Sampul Buku WebP disimpan di dalam `writable/MK/` dan dialirkan melalui controller streaming [Dokumen.php](file:////192.168.1.4/www/budi_ut/app/Controllers/Dokumen.php) guna mencegah pencurian data dan eksploitasi path traversal.
* **Basis Data Relasional:** MySQL/MariaDB (`budi_ut`).
* **Integrasi Bot & Alerting:** Bot Telegram API untuk notifikasi aktivitas login/logout, registrasi, dan perubahan profil secara real-time.
* **Lingkungan Peladen:** Armbian OS (Debian 12 bookworm on Amlogic S905x), Web Server Apache/2.4.68 (Debian), domain resmi `https://ut.budi.ai.id/`, port web utama `:2023`, SMB share `\\192.168.1.4\www\budi_ut`.

---

## 3. Profil Pengguna (User Personas)

1. **Pengunjung / Tamu Publik:**
   * Menjelajahi beranda dan mencari mata kuliah secara real-time.
   * Melihat papan peringkat prestasi mahasiswa nasional (Global, Modul, UAS).
   * Mengirimkan kritik/saran melalui formulir aspirasi publik.
   * Mengunduh file installer APK Android.
2. **Mahasiswa Terdaftar:**
   * Melakukan login cepat berbasis nomor handphone (otomatis terdaftar jika baru).
   * Mengerjakan kuis formatif per modul dengan timer mundur dan skor seketika.
   * Mengikuti simulasi UAS dengan kuota soal acak proporsional berdasarkan jumlah SKS.
   * Meninjau kunci jawaban, pembahasan, dan grafik riwayat nilai kuis.
   * Menandai mata kuliah ke dalam daftar "Favorit Saya".
   * Mengunggah pas foto diri dan mengatur identitas daerah UPBJJ-UT.
   * Mengajukan akses keanggotaan paket premium melalui transfer/e-wallet.
3. **Administrator Sistem:**
   * Mengelola CRUD master data mata kuliah dan mengunggah gambar sampul.
   * Menambah dan menyunting bank soal kuis JSON.
   * Mengaudit akun pengguna mahasiswa dan memberikan status premium.
   * Menerbitkan dan menonaktifkan broadcast pengumuman sistem.
   * Meninjau saran masyarakat dan memeriksa log aktivitas sistem.
   * Mengelola rekening metode pembayaran dan paket donasi.

---

## 4. Kebutuhan Fungsional (Functional Requirements)

### 4.1 Modul Publik & Beranda
* **FR-PUB-01 (Katalog & Pencarian):** Menampilkan kartu mata kuliah dengan fitur pencarian real-time (`GET /`, `GET /api/mk/search`).
* **FR-PUB-02 (Papan Peringkat):** Menampilkan Leaderboard Global (`GET /prestasi`), Leaderboard per Modul (`GET /peringkat/(:segment)/MODUL-(:num)`), dan Leaderboard UAS (`GET /peringkat-uas/(:segment)`).
* **FR-PUB-03 (Unduh APK):** Melayani unduhan paket aplikasi Android dan mencatat metrik total unduhan (`GET /unduh`, `POST /api/apk/download`, `GET /api/apk/count`).
* **FR-PUB-04 (Kotak Saran):** Formulir pengiriman aspirasi publik (GET /saran, POST /api/saran/create).
* **FR-PUB-05 (Donasi & Info):** Halaman informasi donasi (`GET /donasi`, `GET /api/donasi/data`).

### 4.2 Modul Evaluasi & Kuis (Core Feature)
* **FR-QUIZ-01 (Relasi SKS & Jumlah Modul):**
  * SKS 2 = 6 modul (`modul1.json` s.d. `modul6.json`).
  * SKS 3 = 9 modul (`modul1.json` s.d. `modul9.json`).
  * SKS 4 = 12 modul (`modul1.json` s.d. `modul12.json`).
* **FR-QUIZ-02 (Pengerjaan Kuis Formatif):** Soal interaktif per modul dengan timer (`GET /(:segment)/MODUL-(:num)`, `GET /api/dokumen/soal/(:segment)/(:num)`, `POST /api/skor/create`).
* **FR-QUIZ-03 (Simulasi UAS):** Menarik soal acak dari seluruh modul JSON (`GET /(:segment)/uas-simulasi`, `GET /api/mk/uas-questions/(:segment)`).
* **FR-QUIZ-04 (Evaluasi & Kunci):** Halaman hasil evaluasi dan pembahasan (`GET /(:segment)/uas-hasil`).
* **FR-QUIZ-05 (Freemium Lock):** Memblokir akses ke modul yang tercantum di `modul_premium` bagi mahasiswa non-premium (`403 Forbidden`).

### 4.3 Modul Mahasiswa (Dasbor Pribadi)
* **FR-MHS-01 (Riwayat):** Menampilkan rekapitulasi nilai kuis formatif dan UAS (`GET /riwayat`, `GET /api/skor/user/(:num)`).
* **FR-MHS-02 (Favorit):** Fitur bookmark kartu mata kuliah (`GET /favorit`, `POST /api/favorit/toggle`, `GET /api/favorit/list/(:num)`).
* **FR-MHS-03 (Profil):** Pengaturan nama, daerah UPBJJ, dan unggah foto profil WebP (`GET /profil`, `POST /api/profil/update/(:num)`, `POST /api/profil/uploadFoto/(:num)`).

### 4.4 Modul Administrator (Panel Dasbor)
* **FR-ADM-01 (Statistik):** Dasbor ringkasan metrik pengguna dan pendaftar (`GET /admin/dashboard`, `GET /api/dashboard/chart-pendaftar`, `GET /admin/top-cities`).
* **FR-ADM-02 (Master MK):** CRUD data mata kuliah dan upload cover WebP (`GET /admin/matakuliah`, `POST /api/mk/create`, `PUT /api/mk/update/(:num)`, `DELETE /api/mk/delete/(:num)`, `POST /api/dokumen/uploadBmp/(:num)`).
* **FR-ADM-03 (Users):** Manajemen akun dan aktivasi premium (`GET /admin/users`, `GET /api/users`, `PUT /api/users/premium/(:num)`, `DELETE /api/users/delete/(:num)`).
* **FR-ADM-04 (Pengumuman):** Manajemen pop-up broadcast (`GET /admin/pengumuman`, `POST /api/pengumuman/create`, `PUT /api/pengumuman/update/(:num)`, `DELETE /api/pengumuman/delete/(:num)`).
* **FR-ADM-05 (Donasi):** Manajemen metode transfer rekening dan paket premium (`GET /admin/donasi`, `/api/donasi/metode/*`, `/api/donasi/paket/*`).
* **FR-ADM-06 (Audit Logs):** Pemantauan jejak audit trails sistem (`GET /admin/logs`).

---

## 5. Kebutuhan Non-Fungsional (Non-Functional Requirements)

1. **Keamanan (Security):**
   * Seluruh pembacaan berkas fisik wajib lolos sanitasi `basename()` dan `strtoupper()` untuk menolak serangan Path-Traversal (`../../`).
   * Berkas `.env`, bank soal, dan cadangan SQL tidak boleh terekspos ke direktori publik.
   * Middleware filter wajib memisahkan rute mahasiswa (`authApi`) dan admin (`adminAuth`).
2. **Kinerja (Performance):**
   * Waktu muat halaman pertama kali di bawah 1.5 detik pada jaringan standar.
   * cURL notifikasi Telegram dibatasi timeout maksimal 2 detik agar tidak menghambat aliran HTTP response ke klien.
3. **Ketersediaan (Availability):**
   * Mendukung deteksi lingkungan multi-host pada port `:2023` (LAN lokal `192.168.1.4`, VPN `10.147.17.40`, dan domain `https://ut.budi.ai.id/`) tanpa perlu konfigurasi ulang.

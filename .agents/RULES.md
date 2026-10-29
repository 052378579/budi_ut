# RULES.md - Aturan Baku Pengembangan Sistem & Tata Kelola Kode

Dokumen ini memuat aturan mutlak (*immutable rules*) yang wajib dipatuhi oleh setiap pengembang dan agen AI saat membaca, merancang, atau memodifikasi kode pada proyek **UT Tes Formatif** (`budi_ut`).

> [!NOTE]
> Profil peladen dan alur orientasi dokumen dikelola secara terpusat pada [INDEX.md](file:////192.168.1.4/www/budi_ut/.agents/INDEX.md).  
> Struktur direktori fisik dan taksonomi peran agen dirinci pada [AGENTS.md](file:////192.168.1.4/www/budi_ut/.agents/AGENTS.md).

---

## 1. Aturan Fundamental Pengembang (Core Rules)

1. **Bahasa Dokumentasi & Komunikasi:**
   * Seluruh dokumentasi, komentar kode, pesan commit, respons API, dan antarmuka pengguna wajib menggunakan **Bahasa Indonesia** yang baku, jelas, dan profesional.
2. **Framework Backend:**
   * Wajib menggunakan **CodeIgniter 4** (PHP 8.2+) dengan standar arsitektur **Models, Views, Controllers (MVC)** murni dan **RESTful API** (`ResourceController`).
3. **Frontend Stack:**
   * Antarmuka pengguna wajib menggunakan **Vue.js 3** (via CDN `vue.global.prod.js`) dipadukan dengan **Bootstrap 5.3** dan Bootstrap Icons.
   * **DILARANG** menambahkan TailwindCSS kecuali ada instruksi eksplisit tertulis dari pemilik sistem.
4. **Hierarki Template Layout:**
   * Seluruh tampilan web pengguna dan admin **WAJIB** menggunakan sistem tata letak terpusat pada direktori [app/Views/layout/](file:////192.168.1.4/www/budi_ut/app/Views/layout/) (`personal_template.php`, `admin_layout.php`, `modul_layout.php`).
   * Setiap view harus menerapkan pola section CodeIgniter:
     ```php
     <?= $this->extend('layout/personal_template') ?>
     <?= $this->section('content') ?>
     <!-- Konten View -->
     <?= $this->endSection() ?>
     ```
5. **Perlindungan Dokumen Induk:**
   * **DILARANG** memodifikasi [`.agents/INDEX.md`](file:////192.168.1.4/www/budi_ut/.agents/INDEX.md) dan [`.agents/AGENTS.md`](file:////192.168.1.4/www/budi_ut/.agents/AGENTS.md) tanpa perintah eksplisit dari pengguna.

---

## 2. Aturan Baku Perutean & Filter Middleware (Routing Rules)

1. **Pemisahan Kelompok Rute:**
   * Seluruh rute antarmuka pengguna ditempatkan pada blok web utama.
   * Seluruh rute pertukaran data JSON dan streaming berkas **WAJIB** diawali dengan prefiks `/api/` dalam `$routes->group('api', ...)`.
2. **Proteksi Middleware Filter:**
   * Endpoint mutasi data mahasiswa (skor, favorit, profil) **wajib** dilindungi filter `authApi`.
   * Endpoint operasional admin (CRUD MK, users, pengumuman, donasi) **wajib** dilindungi filter `adminAuth`.
   * Filter `invalidchars` dan `secureheaders` harus selalu aktif secara global di `Filters.php`.
   * Filter `maintenance` diterapkan global dengan pengecualian khusus (`except`) pada rute admin, auth API, dan test-error.
3. **ATURAN MUTLAK PERUTEAN DINAMIS (CATCH-ALL):**
   > [!CRITICAL]
   > Dua rute dinamis di bawah ini **WAJIB SELALU DITEMPATKAN DI BARIS PALING AKHIR** pada berkas [app/Config/Routes.php](file:////192.168.1.4/www/budi_ut/app/Config/Routes.php):
   > ```php
   > $routes->get('(:segment)', 'MkController::courseIndex/$1');
   > $routes->get('(:segment)/MODUL-(:num)', 'MkController::index/$1/$2');
   > ```
   > Menempatkan rute baru di bawah blok catch-all ini akan menyebabkan URL tersebut tertangkap sebagai parameter kode mata kuliah (`$1`) sehingga memicu kegagalan 404.

---

## 3. Aturan Keamanan & Akses Berkas (Security Protocols)

1. **Proteksi Anti Path-Traversal:**
   * Penanganan parameter berkas fisik di [Dokumen.php](file:////192.168.1.4/www/budi_ut/app/Controllers/Dokumen.php) atau controller lainnya **wajib** menggunakan fungsi `basename()` dan pemaksaan huruf kapital (`strtoupper()`).
   * Dilarang menerima input berkas mentah tanpa sanitasi yang dapat memungkinkan eksploitasi direktori mundur seperti `../../`.
2. **Penyimpanan Berkas Privat:**
   * Bank Soal Kuis (JSON) dan Gambar Cover Buku (WebP) **DILARANG KERAS** diletakkan di dalam folder publik `public/`.
   * Seluruh aset master wajib disimpan pada `writable/MK/` dan `writable/PROFIL/` serta dialirkan secara aman via *byte stream controller*.
3. **Hak Akses & Kredensial (.env):**
   * Berkas `.env` tidak boleh dipublikasikan atau dapat dibaca langsung oleh peramban.
   * Pastikan DocumentRoot web server selalu menunjuk ke subfolder `/public`, bukan ke root proyek.

---

## 4. Aturan Pemrograman Basis Data (Database Rules)

1. **Definisi Model Eksplisit:**
   * Setiap Model wajib mendefinisikan properti secara eksplisit:
     * `$table` (nama tabel)
     * `$primaryKey` (kunci utama)
     * `$allowedFields` (daftar kolom yang diizinkan untuk insert/update)
     * `$useTimestamps = true` (jika tabel memiliki `created_at` dan `updated_at`).
2. **Anti SQL Injection:**
   * Dilarang melakukan kueri SQL mentah (*raw query*) dengan penggabungan string yang rentan SQL Injection. Selalu gunakan Query Builder CI4 atau *parameter binding*.
3. **Pembersihan Sesi Otomatis:**
   * Jangan mematikan MySQL Event `evt_clean_expired_sessions` yang bertugas membersihkan token sesi usang secara berkala.

---

## 5. Aturan Integrasi Eksternal & Performa (Performance Rules)

1. **Komunikasi Non-Blocking:**
   * Pemanggilan cURL eksternal ke Telegram Bot API atau WAHA gateway **wajib** menyematkan batas waktu (*timeout*) maksimal 2 detik dan opsi `CURLOPT_NOSIGNAL = 1`.
2. **Penanganan Kegagalan Senyap (Silent Failure):**
   * Kegagalan jaringan pada integrasi bot atau GeoJS tidak boleh melempar eksepsi 500 fatal ke peramban pengguna; catat kesalahan secara senyap ke `writable/logs/`.

---

## 6. Integritas Berkas Kritis & SOP Vibe Coding

1. **Integritas Berkas Kritis:**
   * Dilarang memindahkan lokasi bank soal di `writable/MK/` atau pas foto di `writable/PROFIL/`.
   * Skrip utilitas server (`spark`, `builds`, `preload.php`) dilarang dimodifikasi tanpa konfirmasi arsitektur.
2. **SOP Komunikasi Cepat:**
   * Prosedur komunikasi inisiasi cepat dengan pengguna harus selalu merujuk pada standar di [vibe_coding.md](file:////192.168.1.4/www/budi_ut/vibe_coding.md).

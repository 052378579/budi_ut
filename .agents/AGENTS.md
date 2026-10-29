# AGENTS.md - Panduan Lengkap Peran, Arsitektur & Orkestrasi Agen AI

> **Proyek:** UT Tes Formatif (`budi_ut`)  
> **Versi:** 2.6.3  
> **Terakhir Diperbarui:** 22 Oktober 2026  
> **Status:** Sumber Kebenaran Utama (*Single Source of Truth*) Peran, Arsitektur MVC & Tata Kelola Agen AI

---

## 1. Identitas Sistem & Lingkup Kerja Agen

- **Nama Proyek:** `UT Tes Formatif` (Sistem Pembelajaran Mandiri, Evaluasi Belajar & Kuis BMP Mahasiswa Universitas Terbuka).
- **Peran Agen:** Asisten Rekayasa Perangkat Lunak Penuh (*Full-Stack Autonomous Coding & System Engineering Agent*).
- **Bahasa Komunikasi & Kode:** Bahasa Indonesia (resmi, teknis, lugas, dan terstruktur).
- **Jalur Akses Berkas Resmi:** SMB Share Windows UNC `\\192.168.1.4\www\budi_ut\` (Sisi Linux: `/var/www/budi_ut/`). Dilarang menggunakan drive virtual `U:\`.
- **Rujukan Dokumen Induk:** Profil lengkap jaringan peladen (IP, port `:2023`, domain), master indeks seluruh berkas `.agents/`, serta alur orientasi onboarding agen dikelola secara terpusat pada dokumen induk [INDEX.md](file:////192.168.1.4/www/budi_ut/.agents/INDEX.md). Dokumen `AGENTS.md` ini fokus sebagai rujukan spesifik peran agen, struktur fisik direktori, arsitektur MVC CodeIgniter 4, skema database, katalog rute, dan aturan eksekusi.

---

## 2. Taksonomi & Spesialisasi Peran Agen AI

Ketika menjalankan tugas yang luas atau kompleks, agen mengadopsi taksonomi peran spesialis berikut:

```text
                  +-----------------------------------------+
                  |         Core Orchestrator Agent         |
                  |      (Arsitektur & Konteks Global)      |
                  +--------------------+--------------------+
                                       |
     +-----------------+---------------+---------------+-----------------+
     |                 |                               |                 |
     v                 v                               v                 v
+--------------+ +--------------+               +--------------+ +--------------+
|Backend Agent | |Frontend Agent|               | DevOps Agent | |Security Agent|
| (CI4 & REST) | |(Vue 3 & BS 5)|               | (Linux & DB) | |(Anti-Travers)|
+--------------+ +--------------+               +--------------+ +--------------+
```

### 2.1 Core Orchestrator Agent
- Menguasai konteks proyek makro, menjaga integritas dokumen tata kelola di `.agents/`, mengarahkan alur kerja, dan memastikan setiap perubahan terverifikasi anti-regresi.

### 2.2 Backend Specialist Agent
- Merancang dan memelihara *Model* (`app/Models/`), *Web Controller* (`app/Controllers/`), *RESTful API* (`app/Controllers/Api/`), dan *Filters/Middleware* (`app/Filters/`).
- Memastikan seluruh API mengembalikan format JSON standar dan status HTTP yang tepat (`200 OK`, `201 Created`, `400 Bad Request`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`, `500 Internal Error`).
- Menjaga kepatuhan terhadap perutean di [app/Config/Routes.php](file:////192.168.1.4/www/budi_ut/app/Config/Routes.php) dan middleware di [app/Config/Filters.php](file:////192.168.1.4/www/budi_ut/app/Config/Filters.php).

### 2.3 Frontend Specialist Agent
- Membangun antarmuka berbasis Bootstrap 5.3 yang responsif, *mobile-first*, dan ramah sentuhan.
- Mengelola reaktivitas Vue.js 3 (CDN), state management lokal via `global_mixin.js`, siklus hidup komponen (`mounted`, `computed`, `methods`), serta transaksi data asinkron via Axios/Fetch.
- Mematuhi hierarki template layout pada [app/Views/layout/](file:////192.168.1.4/www/budi_ut/app/Views/layout/) (`personal_template.php`, `admin_layout.php`, `modul_layout.php`).

### 2.4 DevOps & Infrastructure Agent
- Memelihara integritas berkas di `/var/www/budi_ut/` pada server Linux Armbian (`192.168.1.4` / `10.147.17.40` port `:2023`).
- Memastikan izin berkas (*permissions*) aman (`.env` berstatus 640/600, folder `writable/` berstatus 775 milik `www-data:www-data`).
- Mengelola database MySQL/MariaDB `budi_ut`, event scheduler pembersih sesi (`evt_clean_expired_sessions`), serta cadangan berkas di `.bckup/`.

### 2.5 Automation & Integration Agent (n8n, WAHA & Telegram)
- Memantau dan mengintegrasikan notifikasi Telegram (`telegram_helper.php`) untuk aktivitas login, registrasi, logout, donasi, dan pembaruan profil secara non-blocking (timeout 2 detik).
- Menyiapkan alur integrasi WhatsApp HTTP API (WAHA) dan workflow automation n8n.

### 2.6 Security Auditor Agent
- Memverifikasi proteksi **Anti Path-Traversal** di [Dokumen.php](file:////192.168.1.4/www/budi_ut/app/Controllers/Dokumen.php) saat mengakses berkas fisik bank soal dan gambar cover (`basename()` dan `strtoupper()`).
- Mencegah paparan file sensitif (`.env`, berkas cadangan database, kredensial bot) ke URL publik browser.
- Menjaga sanitasi input form dengan filter `invalidchars`, proteksi CSRF/CORS, dan verifikasi otentikasi ketat (`authApi` dan `adminAuth`).

---

## 3. Struktur Direktori Fisik Proyek Terkini

Struktur berkas fisik pada repositori `/var/www/budi_ut/` (diakses melalui SMB UNC `\\192.168.1.4\www\budi_ut\`):

```text
/var/www/budi_ut/ (\\192.168.1.4\www\budi_ut\)
├── .agents/                        # PUSAT DOKUMENTASI SISTEM & PANDUAN LENGKAP AGEN AI
│   ├── AGENTS.md                   # Taksonomi peran, arsitektur MVC, struktur direktori, katalog rute & aturan agen
│   ├── INDEX.md                    # Peta navigasi induk seluruh dokumentasi (Master Entry Point)
│   ├── MEMORY.md                   # Rekam jejak keputusan arsitektur & catatan anti-regresi sistemik
│   ├── PRD.md                      # Dokumen Kebutuhan Produk & pemetaan fitur fungsional
│   ├── RULES.md                    # Aturan mutlak pengembangan, perutean & keamanan
│   ├── Skills.md                   # Matriks kompetensi teknis yang dikuasai agen
│   ├── StyleGuide.md               # Panduan gaya penulisan kode PHP (PSR-12), JS Vue 3, SQL, CSS
│   ├── Task.md                     # Pemantauan status pengerjaan tugas & roadmap
│   ├── Tautan.md                   # Matriks rute web/API lengkap, host peladen & path server
│   └── Telegram.md                 # Dokumentasi notifikasi bot Telegram & template pesan
├── app/                            # Kode Sumber Aplikasi CodeIgniter 4
│   ├── Config/                     # Konfigurasi sistem (Routes.php, Filters.php, App.php, Database.php)
│   ├── Controllers/                # Controller Tampilan Web & Operasi Dokumen (10 Controller)
│   │   ├── Api/                    # RESTful API Controllers (14 Controller Layanan Data)
│   │   │   ├── ApkApi.php          # Hitung metrik dan log unduh paket APK Android
│   │   │   ├── AuthApi.php         # Otentikasi mahasiswa (auto-register HP) & login admin
│   │   │   ├── DashboardApi.php    # Agregasi data chart pendaftar mingguan/bulanan admin
│   │   │   ├── DonasiApi.php       # Layanan data metode pembayaran dan paket donasi/premium
│   │   │   ├── FavoritApi.php      # Manajemen toggle & daftar mata kuliah favorit mahasiswa
│   │   │   ├── MkApi.php           # CRUD Master MK & Bank Soal acak simulasi UAS
│   │   │   ├── PengaturanApi.php   # Status dan kendali mode pemeliharaan sistem (Maintenance)
│   │   │   ├── Pengumuman.php      # Controller pengumuman versi legacy/alternatif
│   │   │   ├── PengumumanApi.php   # CRUD dan distribusi maklumat broadcast aktif pop-up
│   │   │   ├── ProfilApi.php       # Biodata akun mahasiswa & konversi unggah WebP pas foto
│   │   │   ├── SaranApi.php        # Penerimaan aspirasi dan saran masukan mahasiswa
│   │   │   ├── SkorApi.php         # CRUD nilai tes formatif/UAS, riwayat & papan peringkat
│   │   │   ├── StatistikController.php # Statistik geolokasi kota dan sebaran pengguna
│   │   │   └── UsersApi.php        # Manajemen akun pengguna & aktivasi status premium
│   │   ├── AdminController.php     # Controller dasbor web administrator
│   │   ├── BaseController.php      # Controller dasar induk CodeIgniter 4
│   │   ├── Dokumen.php             # Streaming aman bank soal, cover buku, dan pas foto
│   │   ├── FavoritController.php   # Tampilan web katalog mata kuliah favorit mahasiswa
│   │   ├── Home.php                # Tampilan web beranda publik / katalog mata kuliah
│   │   ├── MkController.php        # Lembar pengerjaan kuis formatif & simulasi UAS
│   │   ├── ProfilController.php    # Tampilan web profil saya & ganti pas foto
│   │   ├── RunTrigger.php          # Utilitas migrasi/eksekusi DDL trigger database
│   │   ├── SaranPublikController.php # Tampilan web formulir aspirasi & saran publik
│   │   └── SkorController.php      # Tampilan web riwayat nilai & papan peringkat
│   ├── Filters/                    # Middleware Keamanan & Request Lifecycle (4 Filter Aktif)
│   │   ├── AdminApiFilter.php      # Filter otentikasi role admin untuk endpoint API
│   │   ├── AdminAuthFilter.php     # Filter proteksi halaman web dasbor admin (/admin/*)
│   │   ├── AuthApiFilter.php       # Filter verifikasi sesi aktif mahasiswa pada API
│   │   └── MaintenanceFilter.php   # Intersepsi mode perbaikan sistem (kecuali rute admin)
│   ├── Helpers/                    # Utilitas & Helper
│   │   └── telegram_helper.php     # Fungsi pengiriman pesan notifikasi Telegram non-blocking
│   ├── Models/                     # Model Akses Database CI4 (9 Model Entitas)
│   │   ├── AuditTrail.php          # Log jejak rekam audit aktivitas sistem
│   │   ├── FavoritMhsModel.php     # Relasi bookmark mata kuliah mahasiswa
│   │   ├── IpLokasi.php            # Cache geolokasi IP pengguna (GeoJS)
│   │   ├── MetodePembayaranModel.php # Master data saluran pembayaran donasi
│   │   ├── MkModel.php             # Master data mata kuliah & kurikulum BMP
│   │   ├── PaketPremiumModel.php   # Data paket donasi dan durasi langganan premium
│   │   ├── PengumumanModel.php     # Master maklumat pop-up beranda
│   │   ├── SkorMhsModel.php        # Rekam nilai kuis formatif dan simulasi UAS
│   │   └── UsersMhsModel.php       # Master akun mahasiswa dan admin
│   └── Views/                      # Antarmuka Pengguna (HTML + Vue.js Templates)
│       ├── admin/                  # Tampilan Dasbor Administrator
│       │   ├── dashboard.php       # Ringkasan analitik metrik utama & chart
│       │   ├── donasi.php          # Kelola saluran pembayaran & paket donasi
│       │   ├── logs_view.php       # Pemantauan log audit trail aktivitas
│       │   ├── mata_kuliah.php     # CRUD master data mata kuliah & upload BMP
│       │   ├── pengumuman.php      # Form & daftar siaran pengumuman pop-up
│       │   ├── saran_view.php      # Peninjauan kotak saran & masukan mahasiswa
│       │   ├── skor_view.php       # Pemantauan distribusi skor nilai mahasiswa
│       │   └── users_view.php      # Manajemen pengguna & pemberian status premium
│       ├── desktop/                # Tampilan Khusus Simulasi UAS Desktop/PC
│       │   ├── uas_hasil_pc.php    # Evaluasi hasil & kunci pembahasan mode layar lebar
│       │   └── uas_simulasi_pc.php # Lembar pengerjaan simulasi UAS mode desktop
│       ├── errors/                 # Templat Halaman Kesalahan Framework
│       │   ├── cli/                # Tampilan error terminal CLI CodeIgniter
│       │   └── html/               # Tampilan error web browser (production.php, error_404.php)
│       ├── halaman/                # Halaman Statis Publik & Legalitas SEO
│       │   ├── contact.php         # Halaman kontak tim pengembang
│       │   ├── donasi.php          # Informasi donasi sukarela & rekening dukungan
│       │   ├── peringkat_global.php # Papan peringkat nasional seluruh mahasiswa
│       │   ├── privacy.php         # Kebijakan privasi (AdSense requirement)
│       │   └── saran_publik.php    # Formulir aspirasi publik
│       ├── layout/                 # Master Template Tata Letak Terpusat
│       │   ├── admin_footer.php    # Footer dasbor administrator
│       │   ├── admin_header.php    # Header & navigasi dasbor administrator
│       │   ├── admin_layout.php    # Master template dasbor admin
│       │   ├── modul_footer.php    # Footer lembar ujian kuis
│       │   ├── modul_header.php    # Header lembar ujian kuis
│       │   ├── modul_layout.php    # Master template lembar kuis & UAS
│       │   ├── personal_footer.php # Footer umum portal mahasiswa
│       │   ├── personal_header.php # Navbar & modal login/registrasi global
│       │   └── personal_template.php # Master template portal mahasiswa
│       ├── statistics/             # Tampilan Statistik Khusus
│       │   └── top_cities.php      # Peta dan tabel sebaran kota mahasiswa
│       ├── beranda_saya.php        # Beranda utama & katalog pemilihan mata kuliah
│       ├── favorit_saya.php        # Tampilan daftar mata kuliah yang disematkan
│       ├── modul_index.php         # Tampilan daftar modul dalam satu mata kuliah
│       ├── peringkat_view.php      # Papan peringkat skor per modul kuis
│       ├── profil_saya.php         # Pengaturan akun, biodata & pas foto profil
│       ├── riwayat_saya.php        # Riwayat kuis formatif dan simulasi UAS
│       ├── tes_formatif.php        # Lembar pengerjaan kuis formatif interaktif
│       ├── uas_hasil.php           # Lembar hasil & pembahasan simulasi UAS mobile
│       ├── uas_simulasi.php        # Lembar simulasi UAS mobile
│       └── unduh_apk.php           # Landing page unduh aplikasi Android APK
├── public/                         # DocumentRoot Publik Web Server
│   ├── css/                        # Lembar gaya Vanilla CSS kustom
│   ├── js/                         # Skrip logika frontend Vue.js 3
│   │   ├── admin/                  # Skrip modul dasbor admin
│   │   ├── global_mixin.js         # State reaktif global (auth, modal login, kirim saran)
│   │   ├── scripts.js              # Utilitas antarmuka umum
│   │   └── unduh.js                # Logika halaman unduh APK
│   ├── favicon.ico                 # Ikon situs web
│   ├── index.php                   # Titik masuk utama seluruh HTTP request
│   └── robots.txt                  # Pengaturan crawler mesin pencari
├── writable/                       # Direktori Penyimpanan Terproteksi (Akses Privat)
│   ├── MK/                         # Bank Soal JSON & Sampul WebP ({KODE_BMP}/modul{X}.json)
│   ├── PROFIL/                     # Berkas foto profil mahasiswa format WebP
│   ├── logs/                       # Log rotasi CodeIgniter
│   └── session/                    # Berkas penyimpanan sesi PHP
├── vendor/                         # Dependensi Composer (CodeIgniter 4 framework & libraries)
├── builds                          # Utilitas CLI CI4 untuk toggle branch stabilitas
├── composer.json                   # Konfigurasi dependensi PHP (bebas dari autoload-dev)
├── composer.lock                   # Snapshot penguncian versi dependensi Composer
├── .env                            # Konfigurasi lingkungan server (terproteksi hak akses 640)
├── .gitignore                      # Daftar pengecualian pelacakan Git
├── LICENSE                         # Lisensi perangkat lunak MIT
├── phpunit.dist.xml                # Konfigurasi bawaan runner pengujian PHPUnit
├── preload.php                     # Skrip PHP OPcache preloader akselerasi server
├── README.md                       # Dokumentasi pengantar repositori
├── spark                           # Utilitas antarmuka CLI CodeIgniter 4
└── vibe_coding.md                  # SOP Inisiasi Cepat / Prompt Pembuka Pengguna untuk Agen AI
```

---

## 4. Tumpukan Teknologi & Arsitektur Sistem MVC CodeIgniter 4

| Komponen | Teknologi | Penjelasan Teknis |
| :--- | :--- | :--- |
| **Backend Framework** | CodeIgniter 4 (v4.7+) | PHP 8.2+, pola arsitektur MVC murni, RESTful `ResourceController`, Filters middleware. |
| **Frontend Reactive** | Vue.js 3 (CDN) | `vue.global.prod.js`, reaktif tanpa Node.js build tools, state management terpusat via `global_mixin.js`. |
| **Styling & Icons** | Bootstrap 5.3 + Vanilla CSS | Bootstrap Icons 1.11, palet tema warna dinamis per mata kuliah (`theme_color`). |
| **Pangkalan Data** | MySQL / MariaDB | Database `budi_ut`, trigger validasi nomor HP, pembersihan sesi otomatis via `evt_clean_expired_sessions`. |
| **Penyimpanan Terproteksi** | `writable/MK/` & `writable/PROFIL/` | Bank Soal JSON & Sampul WebP dialirkan secara aman melalui `Dokumen.php`. |
| **Notifikasi Real-Time** | Telegram Bot API | `telegram_helper.php` mengirimkan log aktivitas (login, logout, registrasi, donasi) secara non-blocking. |
| **Geolokasi Pengguna** | GeoJS API (`get.geojs.io`) | Background shutdown function mendeteksi kota/koordinat dan dicatat ke tabel `ip_lokasi`. |

### 4.1 Pemisahan Peran MVC pada Proyek Ini:
1. **Controllers (`app/Controllers/`):**
   - **Web Controllers:** Bertugas mengelola alur navigasi dan merender tampilan HTML (`view()`). Terintegrasi dengan layout template (`personal_template.php`, `admin_layout.php`, `modul_layout.php`).
   - **API Controllers (`app/Controllers/Api/`):** Menggunakan `ResourceController` untuk menyediakan layanan data murni format JSON (`$this->respond()`, `$this->fail()`) tanpa merender HTML.
2. **Models (`app/Models/`):**
   - Mengenkapsulasi skema tabel database, aturan validasi, dan relasi data menggunakan Query Builder CodeIgniter 4.
3. **Views (`app/Views/`):**
   - Berkas antarmuka pengguna berbasis PHP yang menyematkan template Vue.js 3 (CDN). Tidak memuat kueri database langsung.
4. **Filters / Middleware (`app/Filters/`):**
   - Mencegat request sebelum (*before*) dan sesudah (*after*) controller dijalankan:
     - `adminAuth`: Memvalidasi hak akses role admin pada dasbor web (`/admin/*`).
     - `authApi`: Memvalidasi sesi aktif pengguna pada endpoint privat API (`/api/skor/*`, `/api/favorit/*`, `/api/profil/*`, `/api/dokumen/soal/*`).
     - `maintenance`: Mengalihkan seluruh traffic non-admin ke halaman pemeliharaan ketika mode maintenance diaktifkan.
     - `invalidchars` & `secureheaders`: Sanitasi karakter berbahaya dan penyematan HTTP security headers secara global.

---

## 5. Basis Data & Entitas Utama

Skema relasi database `budi_ut` terdiri dari entitas-entitas utama berikut:

- **`mata_kuliah`**: Master kurikulum kuis. Kolom: `id_mk`, `kode_mk` (Unique), `kode_bmp`, `nama_mk`, `program_studi`, `jumlah_sks` (2=6 modul, 3=9 modul, 4=12 modul), `img_bmp`, `theme_color`, `modul_premium`.
- **`users_mhs`**: Master akun pengguna. Kolom: `id_user`, `no_hp` (Unique), `nama_user`, `nim_mhs` (9 digit), `kode_ut` (FK ke `ut_daerah`), `img_mhs`, `password` (khusus role admin), `role` (`admin`/`mahasiswa`), `is_premium` (0/1).
- **`skor_mhs`**: Rekam jejak nilai kuis. Kolom: `id_skor`, `id_user`, `id_mk`, `jenis_ujian` (`formatif`/`uas`), `modul_ke`, `skor` (DECIMAL 5,2), `waktu_selesai`. Indeks: `idx_leaderboard_formatif` dan `idx_leaderboard_uas`.
- **`favorit_mhs`**: Bookmark mata kuliah mahasiswa. Kolom: `id_favorit`, `id_user`, `id_mk`. Unique constraint: `(id_user, id_mk)`.
- **`saran_mhs`**: Kotak saran dan masukan pengguna. Kolom: `id_saran`, `id_user` (FK ke `users_mhs`), `kategori` (`fitur`, `soal`, `bug`, `desain`, `lainnya`), `isi_saran`, `perangkat_info`, `created_at`.
- **`ut_daerah`**: Master 40 kantor UPBJJ-UT seluruh Indonesia (`kode_ut`, `nama_ut`).
- **`audit_trails`**: Catatan riwayat audit keamanan dan aktivitas administratif.
- **`ip_lokasi`**: Caching geolokasi IP pengguna untuk mencegah redundansi panggilan API eksternal GeoJS.
- **`pengumuman`**: Distribusi maklumat pop-up beranda (`judul`, `konten`, `tgl_mulai`, `tgl_selesai`, `is_aktif`).
- **`user_sessions`**: Token sesi aktif pengguna, dibersihkan otomatis per jam oleh MySQL Event `evt_clean_expired_sessions`.
- **`metode_pembayaran`** & **`paket_premium`**: Pengelolaan saluran donasi sukarela (QRIS/Transfer) dan aktivasi paket fitur premium.

---

## 6. Katalog Routing & Layanan API Lengkap

### 6.1 Halaman Web (Antarmuka Pengguna & Admin)

#### A. Halaman Publik (Tanpa Login)
- `GET /` : Merender Beranda / Katalog MK (`Home::index`).
- `GET /test-error` : Rute simulasi pengujian error 500 dan halaman maintenance (`views/errors/html/production.php`).
- GET /coming-soon : Halaman Pengumuman Fitur Baru (iews/errors/html/coming_soon.php).
- `GET /unduh` : Informasi & Unduh Paket APK Android (`unduh_apk`).
- `GET /prestasi` : Papan Peringkat Global Nasional (`SkorController::globalLeaderboardView`).
- `GET /peringkat/(:segment)/MODUL-(:num)` : Papan Peringkat spesifik per modul kuis (`SkorController::leaderboardView`).
- `GET /peringkat-uas/(:segment)` : Papan Peringkat khusus Simulasi UAS (`SkorController::leaderboardUasView`).
- `GET /saran` : Formulir Aspirasi Publik (`SaranPublikController::index`).
- `GET /donasi` : Halaman Informasi Donasi & Dukungan Pembayaran (`views/halaman/donasi`).
- `GET /privacy-policy` : Halaman Kebijakan Privasi (Legalitas SEO / Google AdSense).
- `GET /contact-us` : Halaman Kontak Tim Pengembang.

#### B. Dasbor Mahasiswa Pribadi
- `GET /riwayat` : Halaman Riwayat Evaluasi & Nilai Mahasiswa (`SkorController::riwayatView`).
- `GET /favorit` : Katalog Mata Kuliah yang disematkan (`FavoritController::index`).
- `GET /profil` : Halaman Pengaturan Akun, Pas Foto & Profil (`ProfilController::index`).

#### C. Dasbor Administrator (Terproteksi Filter `adminAuth`)
- `GET /admin` / `GET /admin/dashboard` : Dasbor Utama Statistik Admin (`AdminController::dashboard`).
- `GET /admin/matakuliah` : Halaman CRUD Master Data Mata Kuliah (`AdminController::matakuliah`).
- `GET /admin/skor` : Pemantauan Nilai Mahasiswa & Analisis Skor (`AdminController::skor`).
- `GET /admin/users` : Manajemen Pengguna & Hak Akses Premium (`AdminController::users`).
- `GET /admin/pengumuman` : Manajemen Pop-up Pengumuman (`AdminController::pengumumanView`).
- `GET /admin/saran` : Peninjauan Kotak Saran Mahasiswa (`AdminController::saran`).
- `GET /admin/logs` : Pemantauan Log Audit Trail Aktivitas (`AdminController::logs`).
- `GET /admin/top-cities` : Statistik Persebaran Kota Pengguna (`AdminController::topCities`).
- `GET /admin/donasi` : Manajemen Metode Pembayaran & Paket Donasi (`views/admin/donasi`).

#### D. Area Ujian / Evaluasi (Dinamis / Catch-All)
*(WAJIB SELALU DITEMPATKAN DI BARIS PALING AKHIR SISTEM ROUTING)*
- `GET /(:segment)` : Merender Halaman Daftar Modul pada Mata Kuliah (`MkController::courseIndex`).
- `GET /(:segment)/MODUL-(:num)` : Lembar Kuis Formatif Interaktif Berbasis Timer (`MkController::index`).
- `GET /(:segment)/uas-simulasi` : Lembar Simulasi Ujian Akhir Semester (`MkController::uasSimulasi`).
- `GET /(:segment)/uas-hasil` : Evaluasi Kuis & Kunci Jawaban Pembahasan (`MkController::uasHasil`).

---

### 6.2 RESTful API & Storage Streaming (`/api/*`)

#### A. Otentikasi & Sesi
- `POST /api/auth/masuk` : Otentikasi Mahasiswa (auto-register jika no HP baru) & Admin (password terenkripsi).
- `POST /api/auth/keluar` : Logout & pembersihan sesi PHP + notifikasi bot Telegram.
- `GET /api/auth/validate` : Konfirmasi keaktifan sesi di browser klien.

#### B. Manajemen Berkas Storage Terproteksi (`Dokumen.php`)
- `GET /api/dokumen/view/(:segment)` : Metadata satu MK untuk inisialisasi antarmuka ujian.
- `GET /api/dokumen/cover/(:segment)` : Streaming binary WebP sampul buku dari `writable/MK/`.
- `GET /api/dokumen/soal/(:segment)/(:num)` : Streaming JSON bank soal (terproteksi `authApi` & filter freemium).
- `POST /api/dokumen/uploadBmp/(:num)` : Endpoint upload cover buku WebP oleh Admin.
- `GET /api/dokumen/profil/(:segment)` : Streaming binary foto profil mahasiswa dari `writable/PROFIL/`.

#### C. Master Data Mata Kuliah & Informasi Publik
- `GET /api/mk` : Daftar seluruh mata kuliah.
- `GET /api/mk/search` : Pencarian dinamis berbasis kode atau nama MK.
- `GET /api/mk/show/(:segment)` : Rincian satu MK tertentu.
- `GET /api/mk/image/(:segment)` : Render gambar MK.
- `GET /api/mk/uas-questions/(:segment)` : Menarik kumpulan soal teracak dari seluruh modul JSON untuk UAS.
- `GET /api/daerah/list` : Daftar 40 wilayah kantor UPBJJ-UT.
- `GET /api/pengumuman/active` : Mengambil broadcast aktif untuk modal pop-up beranda (`PengumumanApi::getActive`).
- `GET /api/apk/count` & `POST /api/apk/download` : Hitung metrik unduhan & log unduh APK Android.
- `GET /api/donasi/data` : Data metode pembayaran & paket donasi aktif.
- `POST /api/saran/create` : Endpoint penyimpanan saran & masukan mahasiswa (`SaranApi::create`).

#### D. Endpoint Terproteksi Mahasiswa (Filter: `authApi`)
- `GET /api/skor` : Rangkuman data skor pengguna.
- `POST /api/skor/create` : Simpan hasil pengerjaan kuis / simulasi UAS.
- `DELETE /api/skor/delete/(:num)` : Hapus salah satu riwayat nilai tes.
- `GET /api/skor/user/(:num)` : Riwayat lengkap tes milik pengguna.
- `GET /api/skor/ranking/(:num)/(:num)` : Top 10 per modul mata kuliah.
- `POST /api/favorit/toggle` : Tambah / hapus bookmark mata kuliah favorit.
- `GET /api/favorit/ids/(:num)` : Array ID mata kuliah yang difavoritkan (state reaktif).
- `GET /api/favorit/list/(:num)` : Katalog kartu mata kuliah favorit pengguna.
- `GET /api/profil/show/(:num)` : Ambil biodata profil pengguna.
- `POST /api/profil/update/(:num)` : Perbarui identitas profil pengguna.
- `POST /api/profil/uploadFoto/(:num)` : Unggah pas foto profil (otomatis konversi ke WebP).

#### E. Endpoint Terproteksi Administrator (Filter: `adminAuth`)
- `POST /api/mk/create` : Tambah data mata kuliah baru.
- `PUT /api/mk/update/(:num)` : Perbarui master data mata kuliah.
- `DELETE /api/mk/delete/(:num)` : Hapus mata kuliah dari sistem.
- `GET /api/users` : Daftar seluruh akun mahasiswa.
- `POST /api/users/create` : Tambah akun baru secara manual.
- `DELETE /api/users/delete/(:num)` : Hapus akun pengguna secara permanen.
- `PUT /api/users/premium/(:num)` : Ubah status akun mahasiswa menjadi Premium.
- `GET /api/pengumuman` : Daftar seluruh riwayat pengumuman.
- `POST /api/pengumuman/create`, `PUT /api/pengumuman/update/(:num)`, `DELETE /api/pengumuman/delete/(:num)`.
- `GET /api/dashboard/chart-pendaftar` : Data grafik pendaftar mingguan/bulanan.
- `GET /api/statistik/topCities` : Statistik geolokasi kota pengguna.
- `GET /api/donasi/metode`, `POST /api/donasi/metode/create`, `PUT /api/donasi/metode/update/(:num)`, `DELETE /api/donasi/metode/delete/(:num)`.
- `GET /api/donasi/paket`, `POST /api/donasi/paket/create`, `PUT /api/donasi/paket/update/(:num)`, `DELETE /api/donasi/paket/delete/(:num)`.
- `GET /api/pengaturan/maintenance` & `PUT /api/pengaturan/maintenance` : Kontrol mode pemeliharaan sistem.
- PUT /api/pengaturan/coming-soon : Kontrol mode pengumuman fitur baru (Coming Soon) sistem.

---

## 7. Protokol & Aturan Eksekusi Agen

1. **Integritas Dokumen Induk (INDEX.md Protection):**
   - **DILARANG MENGUBAH [`.agents/INDEX.md`](file:////192.168.1.4/www/budi_ut/.agents/INDEX.md)** tanpa instruksi khusus dari pengguna. Dokumen tersebut adalah *Master Single Entry Point* acuan seluruh agen.
2. **Standar Akses Berkas Jaringan (Jalur SMB UNC):**
   - Seluruh operasi berkas wajib menggunakan jalur jaringan UNC `\\192.168.1.4\www\budi_ut\`.
   - Dilarang keras merujuk pada huruf drive virtual yang telah dihapus seperti `U:\`.
3. **Prinsip "Do No Harm":**
   - Sebelum memodifikasi skrip inti atau skema database, selalu verifikasi ketersediaan cadangan di `.bckup/`.
   - Dilarang menjalankan perintah destruktif seperti `DROP TABLE`, `TRUNCATE`, atau menghapus direktori `writable/MK/` tanpa konfirmasi eksplisit pengguna.
4. **Kaidah Perutean Dinamis (Catch-All):**
   - Rute `(:segment)` dan `(:segment)/MODUL-(:num)` **WAJIB SELALU DI BARIS PALING AKHIR** pada [Routes.php](file:////192.168.1.4/www/budi_ut/app/Config/Routes.php) agar tidak membajak (*hijack*) rute statis web atau rute API.
5. **Keamanan Anti Path-Traversal:**
   - Setiap akses berkas fisik di [Dokumen.php](file:////192.168.1.4/www/budi_ut/app/Controllers/Dokumen.php) wajib menggunakan `basename()` dan pemaksaan huruf kapital `strtoupper()`.
6. **Penyimpanan Berkas Privat:**
   - Bank Soal JSON dan Sampul WebP dilarang diletakkan di direktori publik `public/`. Seluruh aset wajib disimpan di direktori privat terproteksi `writable/MK/{KODE_BMP}/`.
7. **Standar Eksekusi Asinkron & Non-Blocking:**
   - Seluruh integrasi eksternal (Telegram Bot API, WAHA) wajib menggunakan batas waktu koneksi (*timeout*) maksimal 2 detik secara non-blocking agar tidak menghambat latensi respons HTTP pengguna.
8. **Konsistensi Gaya Kode:**
   - Mematuhi standar PSR-12: PascalCase untuk nama kelas, camelCase untuk method, snake_case untuk tabel/kolom/view, dan kebab-case untuk URL rute web.
   - Gunakan Query Builder CI4 (hindari raw SQL query yang rentan SQL Injection).
9. **Integritas Konteks & SOP Vibe Coding:**
   - Sebelum melakukan perombakan besar, agen wajib memeriksa [PRD.md](file:////192.168.1.4/www/budi_ut/.agents/PRD.md), [RULES.md](file:////192.168.1.4/www/budi_ut/.agents/RULES.md), dan [MEMORY.md](file:////192.168.1.4/www/budi_ut/.agents/MEMORY.md).

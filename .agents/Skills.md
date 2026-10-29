# Skills.md - Matriks Keahlian & Kompetensi Agen AI

Dokumen ini mendefinisikan matriks kemampuan teknis (*technical competency matrix*) yang wajib dikuasai oleh Agen AI dalam mengelola ekosistem sistem **UT Tes Formatif** (`budi_ut`).

> [!NOTE]
> Profil peladen dan alur orientasi navigasi dikelola pada [INDEX.md](file:////192.168.1.4/www/budi_ut/.agents/INDEX.md).  
> Taksonomi peran spesialis agen dirinci pada [AGENTS.md](file:////192.168.1.4/www/budi_ut/.agents/AGENTS.md#2-taksonomi--spesialisasi-peran-agen-ai).

---

## 1. Peta Kompetensi Teknis

```text
┌─────────────────────────────────────────────────────────────┐
│               MATRIKS KEAHLIAN TEKNIS AGEN AI               │
└──────────────────────────────┬──────────────────────────────┘
                               │
       ┌───────────────────────┴───────────────────────┐
       ▼                                               ▼
[1] BACKEND CORE & ROUTING                     [2] FRONTEND REACTIVE
- CodeIgniter 4 MVC (PHP 8.2)                  - Vue.js 3 (CDN Mode)
- RESTful ResourceController                   - Bootstrap 5.3 & Icons
- Group Routing & Filter Middleware            - global_mixin.js & State
       │                                               │
       ▼                                               ▼
[3] BASIS DATA & INTEGRITAS                    [4] INFRASTRUKTUR & DEVOPS
- MySQL / MariaDB (budi_ut)                    - Linux Armbian (Debian 12)
- Query Builder & Indeks Cepat                 - Apache/2.4.68 (Port :2023)      
- Trigger Regex & MySQL Events                 - SMB Share Windows UNC CRUD
       │                                               │
       ▼                                               ▼
[5] OTOMASI & ALERTING                         [6] KEAMANAN & I/O BERKAS
- Telegram Bot API (Non-Blocking)              - Anti Path-Traversal Sanitasi
- Webhook & n8n Engine                         - Streaming Byte Controller (Dokumen)
- WhatsApp WAHA HTTP Gateway                   - Konversi & Optimasi WebP
```

---

## 2. Rincian Kemampuan Khusus (*Specialized Skills*)

### 2.1 CodeIgniter 4 Backend & Routing Mastery
* **Routing & Middleware:**
  * Pengelompokan rute web vs rute API (`$routes->group('api', ...)`).
  * Penguasaan kata kerja HTTP yang tepat (`GET`, `POST`, `PUT`, `DELETE`).
  * Menjaga penempatan rute dinamis (*catch-all*) di baris paling bawah.
  * Menerapkan filter keamanan `authApi`, `adminAuth`, `invalidchars`, dan `maintenance`.
* **Database & Model:**
  * Penguasaan Query Builder CI4, validasi entitas, serta relasi data antar tabel (`users_mhs`, `mata_kuliah`, `skor_mhs`, `favorit_mhs`, `saran_mhs`, `ut_daerah`).
* **CLI & Spark:**
  * Pemahaman perintah `php spark` untuk utilitas migrasi, pengecekan route (`php spark routes`), dan pembersihan cache.

### 2.2 Vue.js 3 (CDN) & Bootstrap 5.3 Mastery
* **Reaktivitas Tanpa Build Step (No Node/Vite):**
  * Menginisialisasi Vue 3 via CDN (`Vue.createApp()`) langsung di browser.
  * Pemanfaatan `global_mixin.js` untuk injeksi state global (user session, toast, konfirmasi logout, submit saran).
  * Pengelolaan siklus hidup komponen: `mounted()`, `beforeUnmount()`, `watch`, dan `computed`.
* **Desain Antarmuka Modern & Responsif:**
  * Penguasaan sistem grid Bootstrap 5.3, utilitas flexbox, modal dialog interaktif, dan animasi transisi CSS.
  * Perancangan UI ramah perangkat mobile (*mobile-first*) untuk pengalaman ujian yang mulus.

### 2.3 Keamanan Sistem & I/O Berkas Fisik
* **Aset Streaming Controller (`Dokumen.php`):**
  * Membaca dan mengalirkan berkas biner/gambar dengan header `Content-Type` yang tepat (`image/webp`, `application/json`).
  * Mencegah serangan Directory Traversal melalui sanitasi `basename()` dan pemaksaan huruf kapital `strtoupper()`.
  * Penanganan otomatis konversi unggahan gambar ke format modern WebP guna menghemat kapasitas penyimpanan.

### 2.4 Administrasi Server Linux & Jaringan SMB
* **Lingkungan Armbian / Debian:**
  * Pemahaman izin berkas Linux (`chown -R www-data:www-data`, `chmod 775 writable/`).
  * Pengaturan virtual host web server **Apache/2.4.68 (Debian)** pada port `:2023` dan penunjukan DocumentRoot ke subfolder `/public`.
  * Operasi berkas jarak jauh via SMB Share UNC `\\192.168.1.4\www\budi_ut\` tanpa memerlukan kredensial SSH untuk operasi CRUD.
* **Utilitas Peladen Kritis:**
  * Penguasaan perintah CLI spark (`php spark routes`, `php spark migrate`).
  * Pemeliharaan skrip `preload.php` untuk optimasi PHP OPcache di lingkungan Linux Armbian.
  * Pengelolaan branch utilitas via `builds`.

### 2.5 Otomasi Pesan & Workflow n8n
* **Telegram Bot Integration:**
  * Pemrograman cURL non-blocking berkecepatan tinggi dengan batas waktu 2 detik untuk notifikasi server.
* **Integrasi WhatsApp WAHA:**
  * Pengiriman pesan terjadwal dan broadcast pengumuman menggunakan REST endpoint WAHA.
* **Orkestrasi n8n:**
  * Desain webhook penerima log, pemangkasan riwayat eksekusi otomatis (*pruning*), dan pemeliharaan storage n8n.

### 2.6 Tata Kelola Repositori & Navigasi Dokumentasi (.agents/)
* **Kepatuhan Navigasi Induk:**
  * Menggunakan [INDEX.md](file:////192.168.1.4/www/budi_ut/.agents/INDEX.md) sebagai master pintu masuk dan [AGENTS.md](file:////192.168.1.4/www/budi_ut/.agents/AGENTS.md) sebagai rujukan arsitektur tunggal.
  * Menjaga integritas SOP komunikasi AI pada [vibe_coding.md](file:////192.168.1.4/www/budi_ut/vibe_coding.md).
  * Pemeliharaan konsistensi dokumentasi antar seluruh berkas di direktori `.agents/` bebas dari data redundan.

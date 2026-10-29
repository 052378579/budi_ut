# Tautan.md - Direktori Matriks URL, Endpoint & Dokumentasi Eksternal

Dokumen ini menghimpun matriks URL akses cepat peramban (*browser direct links*), spesifikasi payload RESTful API, path berkas konfigurasi peladen, dan tautan dokumentasi resmi eksternal untuk sistem **UT Tes Formatif** (`budi_ut`).

> [!NOTE]
> Profil jaringan peladen (IP, port, domain) dikelola pada [INDEX.md](file:////192.168.1.4/www/budi_ut/.agents/INDEX.md#1-profil-lingkungan-sistem--peladen).  
> Katalog perutean lengkap CodeIgniter 4 dikelola pada [AGENTS.md](file:////192.168.1.4/www/budi_ut/.agents/AGENTS.md#6-katalog-routing--layanan-api-lengkap).

---

## 1. Matriks URL Akses Cepat Peramban (Direct Browser Links)

Gunakan tautan langsung berikut untuk verifikasi fungsionalitas antarmuka di peramban web:

### 1.1 Antarmuka Publik & Kuis Mahasiswa
| Fitur / Halaman | Tautan Domain Resmi Publik | Tautan Jaringan Lokal (Port :2023) |
| :--- | :--- | :--- |
| **Beranda & Pencarian MK** | [ut.budi.ai.id/](https://ut.budi.ai.id/) | [192.168.1.4:2023/](http://192.168.1.4:2023/) |
| **Papan Peringkat Global** | [ut.budi.ai.id/prestasi](https://ut.budi.ai.id/prestasi) | [192.168.1.4:2023/prestasi](http://192.168.1.4:2023/prestasi) |
| **Unduh Aplikasi Android APK** | [ut.budi.ai.id/unduh](https://ut.budi.ai.id/unduh) | [192.168.1.4:2023/unduh](http://192.168.1.4:2023/unduh) |
| **Formulir Aspirasi / Saran** | [ut.budi.ai.id/saran](https://ut.budi.ai.id/saran) | [192.168.1.4:2023/saran](http://192.168.1.4:2023/saran) |
| **Informasi Donasi & Rekening** | [ut.budi.ai.id/donasi](https://ut.budi.ai.id/donasi) | [192.168.1.4:2023/donasi](http://192.168.1.4:2023/donasi) |
| **Kebijakan Privasi (AdSense)** | [ut.budi.ai.id/privacy-policy](https://ut.budi.ai.id/privacy-policy) | [192.168.1.4:2023/privacy-policy](http://192.168.1.4:2023/privacy-policy) |
| **Kontak Pengembang** | [ut.budi.ai.id/contact-us](https://ut.budi.ai.id/contact-us) | [192.168.1.4:2023/contact-us](http://192.168.1.4:2023/contact-us) |
| **Simulasi Uji Error 500** | [ut.budi.ai.id/test-error](https://ut.budi.ai.id/test-error) | [192.168.1.4:2023/test-error](http://192.168.1.4:2023/test-error) |
| **Segera Hadir / Fitur Baru** | [ut.budi.ai.id/coming-soon](https://ut.budi.ai.id/coming-soon) | [192.168.1.4:2023/coming-soon](http://192.168.1.4:2023/coming-soon) |
| **Riwayat Nilai Mahasiswa** | [ut.budi.ai.id/riwayat](https://ut.budi.ai.id/riwayat) | [192.168.1.4:2023/riwayat](http://192.168.1.4:2023/riwayat) |
| **Mata Kuliah Favorit Saya** | [ut.budi.ai.id/favorit](https://ut.budi.ai.id/favorit) | [192.168.1.4:2023/favorit](http://192.168.1.4:2023/favorit) |
| **Profil & Ganti Pas Foto** | [ut.budi.ai.id/profil](https://ut.budi.ai.id/profil) | [192.168.1.4:2023/profil](http://192.168.1.4:2023/profil) |

### 1.2 Antarmuka Area Ujian (Contoh Mata Kuliah EKSI4312)
| Fitur / Halaman | Tautan Domain Resmi Publik | Tautan Jaringan Lokal (Port :2023) |
| :--- | :--- | :--- |
| **Daftar Modul Mata Kuliah** | [ut.budi.ai.id/EKSI4312](https://ut.budi.ai.id/EKSI4312) | [192.168.1.4:2023/EKSI4312](http://192.168.1.4:2023/EKSI4312) |
| **Lembar Kuis Modul 1** | [ut.budi.ai.id/EKSI4312/MODUL-1](https://ut.budi.ai.id/EKSI4312/MODUL-1) | [192.168.1.4:2023/EKSI4312/MODUL-1](http://192.168.1.4:2023/EKSI4312/MODUL-1) |
| **Peringkat Kuis Modul 1** | [ut.budi.ai.id/peringkat/EKSI4312/MODUL-1](https://ut.budi.ai.id/peringkat/EKSI4312/MODUL-1) | [192.168.1.4:2023/peringkat/EKSI4312/MODUL-1](http://192.168.1.4:2023/peringkat/EKSI4312/MODUL-1) |
| **Lembar Simulasi UAS** | [ut.budi.ai.id/EKSI4312/uas-simulasi](https://ut.budi.ai.id/EKSI4312/uas-simulasi) | [192.168.1.4:2023/EKSI4312/uas-simulasi](http://192.168.1.4:2023/EKSI4312/uas-simulasi) |
| **Hasil & Evaluasi UAS** | [ut.budi.ai.id/EKSI4312/uas-hasil](https://ut.budi.ai.id/EKSI4312/uas-hasil) | [192.168.1.4:2023/EKSI4312/uas-hasil](http://192.168.1.4:2023/EKSI4312/uas-hasil) |
| **Papan Peringkat UAS** | [ut.budi.ai.id/peringkat-uas/EKSI4312](https://ut.budi.ai.id/peringkat-uas/EKSI4312) | [192.168.1.4:2023/peringkat-uas/EKSI4312](http://192.168.1.4:2023/peringkat-uas/EKSI4312) |

### 1.3 Antarmuka Dasbor Administrator (Filter: `adminAuth`)
| Modul Administrator | Tautan Domain Resmi Publik | Tautan Jaringan Lokal (Port :2023) |
| :--- | :--- | :--- |
| **Dasbor Analitik Utama** | [ut.budi.ai.id/admin/dashboard](https://ut.budi.ai.id/admin/dashboard) | [192.168.1.4:2023/admin/dashboard](http://192.168.1.4:2023/admin/dashboard) |
| **Master Mata Kuliah & BMP** | [ut.budi.ai.id/admin/matakuliah](https://ut.budi.ai.id/admin/matakuliah) | [192.168.1.4:2023/admin/matakuliah](http://192.168.1.4:2023/admin/matakuliah) |
| **Pemantauan Skor Mahasiswa** | [ut.budi.ai.id/admin/skor](https://ut.budi.ai.id/admin/skor) | [192.168.1.4:2023/admin/skor](http://192.168.1.4:2023/admin/skor) |
| **Manajemen Pengguna & Premium**| [ut.budi.ai.id/admin/users](https://ut.budi.ai.id/admin/users) | [192.168.1.4:2023/admin/users](http://192.168.1.4:2023/admin/users) |
| **Siaran Pop-up Pengumuman** | [ut.budi.ai.id/admin/pengumuman](https://ut.budi.ai.id/admin/pengumuman) | [192.168.1.4:2023/admin/pengumuman](http://192.168.1.4:2023/admin/pengumuman) |
| **Peninjauan Kotak Saran** | [ut.budi.ai.id/admin/saran](https://ut.budi.ai.id/admin/saran) | [192.168.1.4:2023/admin/saran](http://192.168.1.4:2023/admin/saran) |
| **Log Jejak Audit Trail** | [ut.budi.ai.id/admin/logs](https://ut.budi.ai.id/admin/logs) | [192.168.1.4:2023/admin/logs](http://192.168.1.4:2023/admin/logs) |
| **Statistik Sebaran Kota** | [ut.budi.ai.id/admin/top-cities](https://ut.budi.ai.id/admin/top-cities) | [192.168.1.4:2023/admin/top-cities](http://192.168.1.4:2023/admin/top-cities) |
| **Kelola Saluran Donasi** | [ut.budi.ai.id/admin/donasi](https://ut.budi.ai.id/admin/donasi) | [192.168.1.4:2023/admin/donasi](http://192.168.1.4:2023/admin/donasi) |

---

## 2. Matriks Spesifikasi Payload RESTful API

Berikut adalah format muatan data (*payload schema*) pada endpoint mutasi data utama:

### 2.1 Masuk / Login Mahasiswa
- **Endpoint:** `POST /api/auth/masuk`
- **Request Body (JSON):**
  ```json
  {
    "no_hp": "08123456789",
    "password": "" // Diisi jika role admin
  }
  ```
- **Response Sukses (200 OK):**
  ```json
  {
    "status": "success",
    "message": "Login berhasil.",
    "user": {
      "id_user": 1,
      "nama_user": "Mahasiswa UT",
      "no_hp": "08123456789",
      "role": "mahasiswa",
      "is_premium": 0
    }
  }
  ```

### 2.2 Pengiriman Skor Ujian (Formatif & UAS)
- **Endpoint:** `POST /api/skor/create` (Filter: `authApi`)
- **Request Body (JSON):**
  ```json
  {
    "id_user": 1,
    "id_mk": 15,
    "jenis_ujian": "formatif", // "formatif" atau "uas"
    "modul_ke": 1,
    "skor": 85.50
  }
  ```

### 2.3 Pengiriman Aspirasi & Kotak Saran
- **Endpoint:** `POST /api/saran/create`
- **Request Body (JSON):**
  ```json
  {
    "id_user": 1,
    "kategori": "fitur", // "fitur", "soal", "bug", "desain", "lainnya"
    "isi_saran": "Mohon tambahkan pembahasan pada modul 3."
  }
  ```

### 2.4 Toggle Bookmark Mata Kuliah Favorit
- **Endpoint:** `POST /api/favorit/toggle` (Filter: `authApi`)
- **Request Body (JSON):**
  ```json
  {
    "id_user": 1,
    "id_mk": 15
  }
  ```

---

## 3. Path Berkas Konfigurasi Peladen Armbian

Lokasi berkas konfigurasi kritis pada sistem peladen Linux (`192.168.1.4`):

| Layanan / Konfigurasi | Path Berkas pada Peladen | Fungsi Utama |
| :--- | :--- | :--- |
| **Apache Site Config** | `/etc/apache2/sites-available/000-default.conf` | VirtualHost Port :2023 & DocumentRoot `/public` |
| **Apache Main Config** | `/etc/apache2/apache2.conf` | Konfigurasi AllowOverride & MPM Prefork/Worker |
| **PHP CLI Configuration** | `/etc/php/8.2/cli/php.ini` | Eksekusi CLI spark & memory_limit |
| **PHP Apache Module Config** | `/etc/php/8.2/apache2/php.ini` | OPcache setting & upload_max_filesize |
| **MariaDB Server Config** | `/etc/mysql/mariadb.conf.d/50-server.cnf` | Konfigurasi bind-address & event scheduler |
| **Environment Rahasia Proyek** | `/var/www/budi_ut/.env` | Kredensial DB, BaseURL, Bot Telegram (chmod 640) |
| **Log Rotasi CodeIgniter** | `/var/www/budi_ut/writable/logs/` | Log runtime error & kegagalan cURL |
| **Penyimpanan Bank Soal** | `/var/www/budi_ut/writable/MK/` | Bank Soal JSON & Sampul WebP |
| **Penyimpanan Foto Profil** | `/var/www/budi_ut/writable/PROFIL/` | Pas foto WebP mahasiswa teroptimasi |

---

## 4. Tautan Dokumentasi Resmi Pustaka Eksternal

Referensi resmi pustaka dan teknologi yang diadopsi pada sistem:

- **CodeIgniter 4 Framework:** [codeigniter.com/user_guide/](https://codeigniter.com/user_guide/)
- **Vue.js 3 Reactive Engine:** [vuejs.org/guide/introduction.html](https://vuejs.org/guide/introduction.html)
- **Bootstrap 5.3 UI Components:** [getbootstrap.com/docs/5.3/](https://getbootstrap.com/docs/5.3/)
- **Bootstrap Icons 1.11:** [icons.getbootstrap.com/](https://icons.getbootstrap.com/)
- **Telegram Bot API Official Reference:** [core.telegram.org/bots/api](https://core.telegram.org/bots/api)
- **WAHA (WhatsApp HTTP API):** [waha.devlike.pro/](https://waha.devlike.pro/)
- **n8n Workflow Automation Docs:** [docs.n8n.io/](https://docs.n8n.io/)
- **GeoJS Geolocation Lookup:** [geojs.io/docs/v1/endpoints/geo/](https://geojs.io/docs/v1/endpoints/geo/)

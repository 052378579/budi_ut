# MEMORY.md - Memori Sistem & Jejak Keputusan Arsitektur

Dokumen ini merekam memori jangka panjang (*long-term memory*), keputusan historis re-desain (*architecture decision records*), evolusi struktur basis data, dan konsistensi sistem **UT Tes Formatif** (`budi_ut`).

> [!NOTE]
> Profil peladen dan alur orientasi navigasi dikelola pada [INDEX.md](file:////192.168.1.4/www/budi_ut/.agents/INDEX.md).  
> Arsitektur sistem terkini dan aturan eksekusi agen dirinci pada [AGENTS.md](file:////192.168.1.4/www/budi_ut/.agents/AGENTS.md).

---

## 1. Kronologi Rekayasa & Re-Desain Sistem

### Skenario 1: Penyelarasan Naming Database (Lama vs. Baru)
* **Konteks:** Sistem sebelumnya menggunakan skema lama (`ut_tesformatif 2026.05 30.sql`) yang kemudian direfaktor menjadi database `budi_ut.sql`.
* **Keputusan Arsitektur:**
  * Seluruh nama tabel dan kolom diselaraskan ke konvensi standar stabil:
    * `users_mhs` : Akun mahasiswa dan admin (`id_user`, `no_hp`, `nama_user`, `role`, `is_premium`, `nim_mhs`, `kode_ut`).
    * `mata_kuliah` : Master Mata Kuliah / BMP (`id_mk`, `kode_mk`, `kode_bmp`, `nama_mk`, `jumlah_sks`, `theme_color`, `modul_premium`).
    * `skor_mhs` : Hasil evaluasi kuis (`id_skor`, `id_user`, `id_mk`, `jenis_ujian`, `modul_ke`, `skor`, `waktu_selesai`).
    * `favorit_mhs` : Bookmark mata kuliah (`id_favorit`, `id_user`, `id_mk`, `created_at`).
    * `saran_mhs` : Masukan mahasiswa (`id_saran`, `id_user`, `kategori`, `isi_saran`, `perangkat_info`, `created_at`).
    * `ut_daerah` : Master 40 UPBJJ-UT (`no`, `kode_ut`, `nama_ut`, `alamat`, `website`).

### Skenario 2: Migrasi Penyimpanan Berkas ke Folder Privat (`writable/MK/`)
* **Konteks:** Berkas JSON bank soal dan gambar cover sebelumnya berada di direktori publik `public/img/` dan `public/modul/`.
* **Keputusan Arsitektur:**
  * Seluruh bank soal JSON dan cover WebP dipindahkan ke direktori privat `writable/MK/{KODE_BMP}/`.
  * Pembacaan berkas dilayani eksklusif via controller streaming [Dokumen.php](file:////192.168.1.4/www/budi_ut/app/Controllers/Dokumen.php) dengan sanitasi anti path-traversal `basename()` dan `strtoupper()`.

### Skenario 3: Penambahan Fitur Donasi & Paket Premium
* **Konteks:** Diperlukan fasilitas dukungan operasional peladen mandiri dan pembatasan modul kuis berbayar (*freemium*).
* **Keputusan Arsitektur:**
  * Penambahan tabel `metode_pembayaran` dan `paket_premium`.
  * Penambahan kolom `modul_premium` pada tabel `mata_kuliah` dan `is_premium` pada `users_mhs`.

### Skenario 4: Pembersihan `autoload-dev` & Direktori `tests/`
* **Konteks:** Adanya direktori `tests/_support` yang memicu peringatan autoloader saat CI4 dijalankan.
* **Keputusan Arsitektur:**
  * Blok `autoload-dev` dihapus dari [composer.json](file:////192.168.1.4/www/budi_ut/composer.json) agar bersih dan stabil.

### Skenario 5: Alur Kerja Modifikasi Berkas via SMB Share Tanpa Kredensial SSH
* **Konteks:** Pengembang dan agen AI membutuhkan akses langsung (CRUD) pada berkas proyek.
* **Keputusan Arsitektur:**
  * Folder `/var/www/budi_ut` diekspos melalui SMB Share Windows UNC: `\\192.168.1.4\www\budi_ut\`.
  * Operasi berkas dilakukan melalui jaringan SMB tanpa memerlukan password SSH root. Sesi SSH hanya dipakai untuk perintah sistemik Linux (`spark`, `composer`, `git`).

### Skenario 6: Penyelarasan Port Peladen & Domain Resmi (`https://ut.budi.ai.id`, Port :2023)
* **Konteks:** Terjadi ambiguitas port antara `:8003` dan `:2023`, serta domain `ut.budi.biz.id` vs `ut.budi.ai.id`.
* **Keputusan Arsitektur:**
  * Peladen web aktif untuk aplikasi **UT Tes Formatif** secara resmi melayani pada **Port :2023** berbasis Apache/2.4.68 (Debian).
  * Domain publik resmi terkonfigurasi pada **`https://ut.budi.ai.id/`**.
  * Konfigurasi baseURL di [App.php](file:////192.168.1.4/www/budi_ut/app/Config/App.php) mendeteksi host/port secara dinamis.

### Skenario 7: Sentralisasi Dokumentasi Agen AI ke Direktori `.agents/`
* **Konteks:** Menghindari fragmentasi dokumentasi antara root repositori dan folder konfigurasi agen.
* **Keputusan Arsitektur:**
  * Seluruh dokumen panduan, aturan, matriks kemampuan, dan referensi sistem disatukan di dalam direktori [`.agents/`](file:////192.168.1.4/www/budi_ut/.agents/).
  * Berkas redundan di root (`AGENTS.md`, `ROUTES.md`, `INDEX_.md`) dihapus permanen.

### Skenario 8: Peleburan dan Sentralisasi Dokumentasi Routing
* **Konteks:** Dokumentasi perutean sebelumnya berada di root (`ROUTES.md`).
* **Keputusan Arsitektur:**
  * Seluruh pemetaan rute Web dan RESTful API dilebur ke [AGENTS.md](file:////192.168.1.4/www/budi_ut/.agents/AGENTS.md).

### Skenario 9: Konsolidasi Master Index Dokumen dan Penghapusan INDEX_.md
* **Konteks:** Terdapat berkas draf redundan INDEX_.md berdampingan dengan INDEX.md.
* **Keputusan Arsitektur:**
  * Seluruh materi dari INDEX_.md dilebur ke dalam [INDEX.md](file:////192.168.1.4/www/budi_ut/.agents/INDEX.md) sebagai master gerbang utama, dan berkas `INDEX_.md` dihapus permanen.

### Skenario 10: Eliminasi Redundansi Dokumen .agents/ (Master Guide Separation)
* **Konteks:** Terjadi tumpang tindih (*redundancy*) data profil peladen, tabel 10 dokumen, dan diagram onboarding di dalam `AGENTS.md`, `Tautan.md`, dan `RULES.md`.
* **Keputusan Arsitektur:**
  * [INDEX.md](file:////192.168.1.4/www/budi_ut/.agents/INDEX.md) ditetapkan sebagai **satu-satunya dokumen induk acuan** untuk profil peladen, master indeks dokumen, dan diagram alur orientasi onboarding.
  * [AGENTS.md](file:////192.168.1.4/www/budi_ut/.agents/AGENTS.md) ditetapkan sebagai **satu-satunya dokumen arsitektur teknis** untuk taksonomi peran agen, inventaris pohon fisik berkas 100%, arsitektur MVC, entitas database, katalog perutean, dan aturan eksekusi agen.
  * Seluruh berkas pendukung (`Tautan.md`, `RULES.md`, `Skills.md`, `StyleGuide.md`, `Task.md`, `PRD.md`, `Telegram.md`) direfaktor agar bebas dari duplikasi data dan fokus pada fungsinya masing-masing.

---

## 2. Peta Memori Komponen Kritis

1. **Routing Dinamis Catch-All (PENTING):**
   * Di baris paling bawah [app/Config/Routes.php](file:////192.168.1.4/www/budi_ut/app/Config/Routes.php), terdapat rute dinamis:
     ```php
     $routes->get('(:segment)', 'MkController::courseIndex/$1');
     $routes->get('(:segment)/MODUL-(:num)', 'MkController::index/$1/$2');
     ```
   * **Peringatan Memori:** Rute dinamis ini **WAJIB SELALU DITEMPATKAN DI BARIS PALING AKHIR**. Jika diletakkan di atas rute lain (seperti `/riwayat`, `/favorit`, `/unduh`), rute tersebut akan tertangkap sebagai parameter kode mata kuliah (`$1`) sehingga memicu error 404.

2. **Deteksi Lingkungan Otomatis (Host & Port :2023):**
   * Logika penentuan `CI_ENVIRONMENT` dan `baseURL` mendeteksi host/port secara dinamis:
     * Port `2023` pada `127.0.0.1`, `192.168.1.4`, atau `10.147.17.40` -> Mode **development/LAN**.
     * Domain `ut.budi.ai.id` -> Mode **production** dengan `baseURL` `https://ut.budi.ai.id/`.

3. **Inisialisasi Vue.js 3 di Header & Footer:**
   * Elemen root Vue adalah `<div id="ut" v-cloak>` yang berada di [personal_header.php](file:////192.168.1.4/www/budi_ut/app/Views/layout/personal_header.php) dan ditutup di [personal_footer.php](file:////192.168.1.4/www/budi_ut/app/Views/layout/personal_footer.php).
   * Script CDN Vue 3 (`vue.global.prod.js`) dan [global_mixin.js](file:////192.168.1.4/www/budi_ut/public/js/global_mixin.js) selalu dimuat sebelum `$this->renderSection('scripts')`.

---

## 3. Catatan Anti-Regresi

* **JANGAN** menghapus `register_shutdown_function` pada [AuthApi.php](file:////192.168.1.4/www/budi_ut/app/Controllers/Api/AuthApi.php) karena bertugas menarik data lokasi GeoIP tanpa memperlambat respons login ke klien.
* **JANGAN** mematikan filter `invalidchars` di [app/Config/Filters.php](file:////192.168.1.4/www/budi_ut/app/Config/Filters.php) karena menjaga sanitasi payload JSON global dari injeksi karakter kontrol berbahaya.
* **JANGAN** mengubah hak akses folder `writable/` menjadi read-only, karena sistem memerlukan akses tulis untuk pas foto profil, log, session file, dan cache.

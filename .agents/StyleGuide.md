# StyleGuide.md - Panduan Gaya Kode & Standar Desain

Dokumen ini mendefinisikan konvensi penulisan kode (*coding style guide*), tata nama berkas, standar basis data, konvensi perutean URL, dan aturan desain antarmuka untuk proyek **UT Tes Formatif** (`budi_ut`).

> [!NOTE]
> Profil peladen dan alur orientasi dokumen dikelola pada [INDEX.md](file:////192.168.1.4/www/budi_ut/.agents/INDEX.md).  
> Struktur fisik berkas proyek selaras pada [AGENTS.md](file:////192.168.1.4/www/budi_ut/.agents/AGENTS.md#3-struktur-direktori-fisik-proyek-terkini).

---

## 1. Standar Penulisan PHP & Routing (Backend)

Sistem mengadopsi standar **PSR-12** dengan penyesuaian konvensi CodeIgniter 4:

### 1.1 Penamaan Berkas & Namespace
* **Nama Berkas Kelas:** Menggunakan format **PascalCase** dan wajib sesuai dengan nama kelas:
  * Controller: `AdminController.php`, `MkController.php`, `Dokumen.php`.
  * API Controller: `AuthApi.php`, `MkApi.php`, `SkorApi.php`, `SaranApi.php` (dalam namespace `App\Controllers\Api`).
  * Model: `UsersMhsModel.php`, `MkModel.php`, `SkorMhsModel.php`, `FavoritMhsModel.php`.
* **Nama Berkas Non-Kelas:** Menggunakan format **snake_case**:
  * View: `beranda_saya.php`, `modul_index.php`, `tes_formatif.php`, `uas_simulasi.php`.
  * Helper: `telegram_helper.php`.

### 1.2 Konvensi Perutean URL (Routes)
* **URL Halaman Web:** Menggunakan format **kebab-case** dengan huruf kecil:
  * Contoh: `/privacy-policy`, `/contact-us`, `/peringkat-uas/(:segment)`.
* **URL RESTful API:** Diawali dengan `/api/` diikuti kata benda entitas tunggal/jamak atau aksi spesifik:
  * Contoh: `/api/mk`, `/api/mk/search`, `/api/skor/create`, `/api/favorit/toggle`, `/api/saran/create`.
* **Placeholder Segmen Dinamis:**
  * Gunakan `(:segment)` untuk parameter teks/string (misal: kode mata kuliah, token, nama file).
  * Gunakan `(:num)` untuk parameter angka/integer ID (misal: ID mata kuliah, ID user, nomor modul).

### 1.3 Metode & Variabel
* **Nama Method:** Menggunakan format **camelCase** yang diawali kata kerja:
  * `public function getActive()`, `public function toggleFavorite()`, `public function validateSession()`.
* **Nama Variabel & Properti:** Menggunakan **camelCase**:
  * `$userModel`, `$dataMk`, `$isLoggingOut`.
* **Type Hinting & Return Types:**
  * Wajib menyematkan type hinting pada parameter method dan tipe balikan jika memungkinkan:
    ```php
    public function view(string $kodeMk = null): ResponseInterface
    ```

---

## 2. Standar Penulisan Berkas & Direktori Storage

* **Direktori Modul BMP:** Menggunakan huruf kapital murni (**UPPERCASE**) sesuai kode mata kuliah:
  * `writable/MK/EKSI4312/`
  * `writable/MK/MSIM4201/`
* **Nama Berkas di Dalam Folder BMP:**
  * Berkas Sampul: `{KODE_BMP}.webp` (contoh: `EKSI4312.webp`).
  * Berkas Soal: `modul{X}.json` dengan huruf kecil (contoh: `modul1.json`, `modul2.json`, dst.).
* **Direktori Pas Foto Profil:**
  * `writable/PROFIL/{id_user}_{hash}.webp` (selalu simpan dalam format WebP teroptimasi).
* **Organisasi Berkas Skrip Frontend:**
  * Skrip modul klien: `public/js/{nama_fitur}.js` (`snake_case`).
  * Skrip modul admin: `public/js/admin/{nama_fitur}.js`.
* **Berkas Utilitas & Panduan Repositori:**
  * `vibe_coding.md` : Panduan baku prompt inisiasi sesi AI.
  * `.agents/` : Wadah terpusat dokumentasi tata kelola sistem.
  * `spark`, `builds`, `preload.php` : Skrip utilitas CLI dan OPcache di root repositori.

---

## 3. Standar Penulisan JavaScript & Vue.js 3 (Frontend)

Karena Vue.js 3 digunakan via **CDN**, kode JS dieksekusi langsung oleh browser tanpa kompilator Webpack/Vite:

### 3.1 Struktur Komponen Vue
* **Inisialisasi Aplikasi:**
  Gunakan pemisahan aplikasi yang jelas dengan mixin global:
  ```javascript
  const app = Vue.createApp({
      mixins: [globalMixin],
      data() {
          return {
              isLoading: false,
              daftarMk: [],
              searchKeyword: ''
          };
      },
      computed: {
          filteredMk() {
              return this.daftarMk.filter(item => 
                  item.nama_mk.toLowerCase().includes(this.searchKeyword.toLowerCase())
              );
          }
      },
      mounted() {
          this.fetchData();
      },
      methods: {
          async fetchData() {
              this.isLoading = true;
              try {
                  const res = await fetch('/api/mk');
                  const json = await res.json();
                  this.daftarMk = json.data || [];
              } catch (err) {
                  this.showToast('Gagal memuat data.', 'danger');
              } finally {
                  this.isLoading = false;
              }
          }
      }
  });

  app.mount('#ut');
  ```

### 3.2 Penamaan Properti Vue & Event
* Properti state boolean wajib diawali kata sifat atau kata tanya: `isLoading`, `isSubmitting`, `hasCompleted`.
* Nama event handler diawali kata kerja: `handleSubmit()`, `selectAnswer()`, `confirmLogout()`.

---

## 4. Standar Penulisan Basis Data (SQL)

* **Nama Tabel:** Menggunakan huruf kecil dengan pemisah garis bawah (**snake_case**):
  * `users_mhs`, `mata_kuliah`, `skor_mhs`, `favorit_mhs`, `saran_mhs`, `ut_daerah`, `audit_trails`, `ip_lokasi`, `pengumuman`, `user_sessions`, `metode_pembayaran`, `paket_premium`.
* **Kunci Utama (Primary Key):**
  * Disarankan menggunakan format `id_{nama_tabel}`:
  * Contoh: `id_user` pada `users_mhs`, `id_mk` pada `mata_kuliah`, `id_skor` pada `skor_mhs`, `id_saran` pada `saran_mhs`.
* **Kolom Waktu (Timestamps):**
  * Wajib menyematkan `created_at datetime DEFAULT CURRENT_TIMESTAMP` dan `updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`.
* **Presisi Desimal Nilai:**
  * Kolom nilai kuis wajib menggunakan tipe data presisi desimal: `decimal(5,2)` (misalnya nilai `87.50`), bukan integer bulat.

---

## 5. Standar Desain Antarmuka (UI / UX)

* **Palet Warna Utama:**
  * Biru Resmi UT: `#004da1` (Primary Brand Color).
  * Kuning Aksen UT: `#ffc107` / `#ffb300` (Accent & Leaderboard Star).
  * Latar Belakang Tubuh: `#f8f9fa` (Light Clean Gray).
  * Teks Utama: `#212529` (Dark Slate).
* **Komponen & Reaktivitas:**
  * Tombol wajib memiliki efek umpan balik hover dan indikator pemuatan (*spinner*) saat proses asinkron berlangsung.
  * Elemen formulir harus dilengkapi atribut `autocomplete` dan label ramah pembaca layar (*screen reader*).
  * Desain harus responsif di semua ukuran layar (Mobile 360px hingga Desktop 1920px).

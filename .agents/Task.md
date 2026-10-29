# Task.md - Manajemen Tugas & Peta Jalan (Roadmap)

Dokumen ini memantau status pengerjaan fitur, perbaikan bug (*bug fixes*), tugas pemeliharaan (*maintenance*), dan peta jalan masa depan untuk proyek **UT Tes Formatif** (`budi_ut`).

> [!NOTE]
> Profil peladen dan alur orientasi navigasi dikelola pada [INDEX.md](file:////192.168.1.4/www/budi_ut/.agents/INDEX.md).  
> Struktur direktori fisik dan taksonomi peran agen dirinci pada [AGENTS.md](file:////192.168.1.4/www/budi_ut/.agents/AGENTS.md).

---

## 1. Status Pengerjaan Terkini (*Work In Progress & Backlog*)

| ID Tugas | Deskripsi Pekerjaan | Prioritas | Status | Penanggung Jawab |
| :--- | :--- | :---: | :---: | :--- |
| **TASK-SYS-01** | Analisis menyeluruh kode dan peladen `budi_ut` | Tinggi | Selesai (Completed) | AI Agent |
| **TASK-SYS-02** | Pembersihan konfigurasi `autoload-dev` di `composer.json` | Sedang | Selesai (Completed) | AI Agent |
| **TASK-DOC-01** | Peleburan & sinkronisasi dokumentasi ke direktori `.agents/` | Tinggi | Selesai (Completed) | AI Agent |
| **TASK-DOC-02** | Penghapusan berkas redundan `AGENTS.md` di root repositori | Rendah | Selesai (Completed) | AI Agent |
| **TASK-DOC-03** | Peleburan `ROUTES.md` ke `.agents/` & penghapusan di root | Sedang | Selesai (Completed) | AI Agent |
| **TASK-DOC-04** | Peleburan `INDEX_.md` ke sistem `.agents/` & penghapusan di root | Sedang | Selesai (Completed) | AI Agent |
| **TASK-DOC-05** | Audit menyeluruh, penyelarasan fisik AGENTS.md, dan eliminasi redundansi data lintas berkas `.agents/` | Tinggi | Selesai (Completed) | Core Orchestrator |
| **TASK-UI-01**  | Redesain halaman pemeliharaan (*Maintenance Mode*) kontras tinggi & perbaikan tipografi baris | Tinggi | Selesai (Completed) | Frontend Specialist |
| **TASK-UI-02**  | Pembuatan template 'Segera Hadir dengan Fitur Baru' (`coming_soon.php`) | Sedang | Selesai (Completed) | Frontend Specialist |
| **TASK-UI-03**  | Integrasi rute publik khusus `/coming-soon` & bypass filter maintenance | Sedang | Selesai (Completed) | Core Web Agent |
| **TASK-ADM-01** | Penambahan switch Coming Soon di dasbor admin & penguncian hak akses khusus id_user=1 | Tinggi | Selesai (Completed) | Core & Security Agent |
| **TASK-UI-04**  | Eliminasi titik ekstra ". " & perbaikan rendering pilihan ganda pada Simulasi UAS Desktop (`uas_simulasi_pc.php`) | Tinggi | Selesai (Completed) | Frontend Specialist |
| **TASK-PY-01**  | Skrip ekstraksi bank soal otomatis dari PDF BMP UT (`scripts/bmp_extractor.py`) | Tinggi | Backlog | Python & AI Specialist |
| **TASK-PY-02**  | Mesin diagnostik skor & rekomendasi modul lemah personal mahasiswa | Sedang | Backlog | Data Analyst Agent |
| **TASK-PY-03**  | Generator paket latihan cetak PDF format A4 (*Printable Mock-Exam*) via Python ReportLab | Sedang | Backlog | Python Specialist |
| **TASK-PY-04**  | Auditor kualitas bank soal otomatis & deteksi soal duplikat (`scripts/audit_bank_soal.py`) | Sedang | Backlog | Python Specialist |
| **TASK-PY-05**  | Mesin prediktor nilai akhir mata kuliah & target skor UAS UT | Sedang | Backlog | Data Analyst Agent |
| **TASK-PY-06**  | Computerized Adaptive Testing (*CAT-Lite*) adaptasi tingkat kesulitan soal | Rendah | Backlog | AI & Algorithm Specialist |
| **TASK-PY-07**  | Generator audio podcast ringkasan modul UT via Text-to-Speech (TTS) | Sedang | Backlog | Audio & NLP Specialist |
| **TASK-PY-08**  | Gamifikasi kuis: streak counter harian & sistem liga belajar mingguan | Rendah | Backlog | Engagement Specialist |
| **TASK-PY-09**  | Kalender akademik pintar UT & sinkronisasi jadwal belajar via berkas `.ics` | Rendah | Backlog | Automation Agent |
| **TASK-AI-01**   | Pencarian semantik materi BMP & penjelasan soal (*semantic search*) | Sedang | Backlog | AI Specialist |
| **TASK-BOT-01**  | Optimasi webhook & kuis interaktif Telegram daemon | Sedang | Backlog | Automation Agent |
| **TASK-WAHA-01** | Integrasi WhatsApp WAHA via n8n workflow | Sedang | Backlog | DevOps Agent |

---

## 2. Riwayat Tugas yang Telah Diselesaikan (*Completed Tasks*)

- [x] **Task-Core-01:** Refaktorisasi database dari skema lama ke skema stabil `budi_ut` dengan relasi lengkap dan indeks performa.
- [x] **Task-Core-02:** Migrasi berkas bank soal dan cover buku dari direktori publik ke `writable/MK/` dengan controller `Dokumen.php`.
- [x] **Task-Core-03:** Penerapan proteksi Anti Path-Traversal dengan `basename()` dan `strtoupper()` pada seluruh streaming berkas.
- [x] **Task-Core-04:** Penambahan trigger validasi regex nomor HP Indonesia dan pencegahan spam pendaftaran (`before_insert_users_mhs`).
- [x] **Task-Core-05:** Penambahan background process GeoJS API untuk pencatatan kota dan geolokasi pengguna ke `ip_lokasi`.
- [x] **Task-Core-06:** Implementasi MySQL Event Scheduler `evt_clean_expired_sessions` pembersih sesi kedaluwarsa per jam.
- [x] **Task-Core-07:** Penambahan rute pengujian error 500 dan halaman maintenance produksi (`/test-error`).
- [x] **Task-Doc-01:** Konsolidasi master entry point `INDEX.md` dan penyelarasan 100% berkas fisik MVC di `AGENTS.md`.
- [x] **Task-Doc-02:** Eliminasi redundansi data masif lintas seluruh berkas pendukung (`Tautan.md`, `RULES.md`, `Skills.md`, `StyleGuide.md`, `MEMORY.md`, `Task.md`).
- [x] **Task-UI-01:** Redesain antarmuka halaman pemeliharaan (`production.php`) dengan gaya Dark Glassmorphism kontras tinggi, logo resmi UT, badge status, tombol aksi berikon, dan eliminasi pembungkusan kata (orphan words).
- [x] **Task-UI-02:** Pembuatan halaman baru 'Segera Hadir dengan Fitur Baru' (`coming_soon.php`) mengadopsi 100% gaya visual dan layout Dark Glassmorphism dari `production.php`.
- [x] **Task-UI-03:** Pendaftaran dan aktivasi rute publik khusus `/coming-soon` pada `Routes.php`, `Filters.php` (pengecualian maintenance), serta sinkronisasi katalog `AGENTS.md` dan `Tautan.md`.
- [x] **Task-Adm-01:** Implementasi switch Coming Soon sejajar sebelum Toggle Maintenance di dasbor admin, auto-seed database `pengaturan_sistem`, intersepsi berjenjang di `MaintenanceFilter`, dan penguncian hak akses Dasbor Admin mutlak khusus `id_user = 1` tabel `users_mhs`.
- [x] **Task-UI-04:** Eliminasi karakter titik ekstra ". ", penyelarasan huruf abjad A, B, C, D satu kali rapi, pemulihan fungsi klik baris opsi, dan penambahan selected state visual pada `app/Views/desktop/uas_simulasi_pc.php`.

---

## 3. Peta Jalan Fitur Mendatang (*Future Roadmap - Hybrid PHP & Python 3.11*)

1. **Pilar 1: Otomasi Bank Soal & Pemrosesan Dokumen BMP (Python 3.11):**
   * **Ekstraktor Soal Otomatis (`TASK-PY-01`):** Parser `pdfplumber` mengekstrak soal tes formatif di akhir Kegiatan Belajar (soal, opsi A-B-C-D, kunci jawaban) dari PDF modul BMP UT langsung menjadi JSON di `writable/MK/`.
   * **Auditor Kualitas Soal (`TASK-PY-04`):** Deteksi otomatis soal duplikat (*Levenshtein distance*), verifikasi kunci jawaban valid, dan laporan kesehatan bank soal di dasbor admin.

2. **Pilar 2: Pembelajaran Adaptif & Analitik Diagnostik Personal:**
   * **Mesin Diagnostik Skor (`TASK-PY-02`):** Analisis kelemahan per modul dari tabel `skor_mhs` untuk rekomendasi personal sebelum UAS.
   * **Prediktor Nilai Akhir & IPK UT (`TASK-PY-05`):** Simulasi perhitungan nilai Tuton (30%) + UAS (70%) dan kalkulasi target skor minimal UAS (syarat kelulusan UAS >= 30).
   * **Computerized Adaptive Testing / CAT-Lite (`TASK-PY-06`):** Ujian adaptif cerdas yang menyesuaikan tingkat kesulitan soal secara real-time berdasarkan akurasi jawaban mahasiswa.

3. **Pilar 3: Aksesibilitas Belajar Mobile & Audio (Belajar di Mana Saja):**
   * **Audio Podcast Modul UT via TTS (`TASK-PY-07`):** Generasi ringkasan audio 3-5 menit per modul menggunakan Text-to-Speech vokal alami Indonesia (`id-ID-ArdiNeural`/`id-ID-GadisNeural`) untuk mahasiswa yang sedang bekerja atau komuter.
   * **Generator Lembar Latihan Cetak A4 (`TASK-PY-03`):** Generator PDF siap cetak berbasis `reportlab` format A4 lengkap dengan Lembar Jawaban Ujian (LJU) mandiri untuk belajar offline.

4. **Pilar 4: Gamifikasi, Kalender, & Retensi Pengguna:**
   * **Streak Counter & Liga Mingguan (`TASK-PY-08`):** Gamifikasi gaya Duolingo dengan pelacakan streak belajar harian dan sistem liga perunggu-perak-emas-berlian berdasarkan XP pengerjaan kuis.
   * **Kalender Pintar Akademik UT / iCal Sync (`TASK-PY-09`):** Sinkronisasi kalender `.ics` ke Google/Apple Calendar berisi jadwal sesi Tuton 1-8, batas unggah tugas, dan target penyelesaian kuis modul.

5. **Pilar 5: Asisten Cerdas Multikanal (Telegram & WhatsApp):**
   * **Bot Kuis Mandiri (`TASK-BOT-01` / `TASK-WAHA-01`):** Daemon Python `asyncio` untuk kuis kilat 5 menit langsung dari aplikasi perpesanan (`/kuis EKMA4116`) dengan tombol pilihan interaktif.

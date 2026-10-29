# IMPLEMENTATION PLAN: Sistem Validasi Ketat Pendaftaran User
**Proyek:** Google Antigravity  
**Dokumen:** Rencana Implementasi Validasi Data (*Data Integrity & Anti-Spam*)  
**Status:** Perencanaan  

---

## 1. Ringkasan Eksekutif (Executive Summary)
Proyek **Google Antigravity** membutuhkan mekanisme pendaftaran (*registration*) yang aman dan bersih dari data rucah (*dummy/spam*). Dokumen ini menguraikan rencana implementasi validasi ketat pada input `no_hp` (dan data profil lainnya) yang diterapkan secara berlapis pada *Front-End* (Klien), *Back-End* (Server), dan *Database* (Storage) guna memastikan integritas data, mengurangi beban server, dan memberikan *User Experience* (UX) yang baik.

## 2. Tujuan Implementasi (Objectives)
1. **Integritas Data:** Memastikan 100% data nomor HP yang masuk ke dalam sistem adalah nomor provider seluler Indonesia yang logis dan valid.
2. **Efisiensi Server:** Mencegah permintaan (*request*) yang berisi data sampah (*spam*) mencapai pemrosesan database.
3. **Peningkatan UX:** Memberikan umpan balik visual secara *real-time* kepada pengguna saat mereka memasukkan format data yang salah di *browser*.
4. **Keamanan Ekstra:** Membangun benteng pertahanan terakhir di database untuk mencegah injeksi data kotor melalui eksploitasi API (*API Bypassing*).

---

## 3. Matriks Aturan Validasi (Validation Rules Matrix)

Berikut adalah standarisasi aturan (berdasarkan *Regular Expression*) yang **wajib** digunakan secara identik di semua lapisan aplikasi:

| ID Aturan | Deskripsi Aturan | Kondisi Valid | Kondisi Ditolak (Error) | Regex Pengecekan |
| :--- | :--- | :--- | :--- | :--- |
| **VR-01** | **Karakter Dasar** | Hanya angka (0-9) | Mengandung huruf/simbol/spasi | `^[0-9]+$` |
| **VR-02** | **Panjang String** | 10 hingga 13 digit | Kurang dari 10, lebih dari 13 | Evaluasi properti `.length` |
| **VR-03** | **Prefix Provider** | Diawali "08" + kode valid | Prefix asal (mis: 0844, 0800) | `^08(1[1-9]\|2[1-3]\|3[1-3]\|38\|5[1-35-9]\|7[7-8]\|8[1-9]\|9[5-9])` |
| **VR-04** | **Anti-Spam Kembar**| Kombinasi angka wajar | Angka sama 4x beruntun (mis: 2222) | `([0-9])\1{3,}` *(Tolak jika Match)* |
| **VR-05** | **Anti-Spam Blok** | Kombinasi acak wajar | Blok diulang 3x (mis: 121212) | `([0-9]{2,3})\1{2,}` *(Tolak jika Match)* |
| **VR-06** | **Anti-Spam Sekuensial**| Kombinasi acak wajar | Urutan *keyboard* (mis: 123456) | `(012345\|123456\|...\|543210)` *(Tolak jika Match)* |

---

## 4. Fase 1: Implementasi Front-End (Client-Side)
Lapisan ini bertugas memberikan respons cepat (*fail-fast*) kepada pengguna.

### 4.1. Strategi UI/UX
*   Tampilkan pesan error secara spesifik di bawah kolom input berwarna merah.
*   Gunakan event `oninput` atau `onkeyup` untuk memvalidasi data saat pengguna sedang mengetik (*real-time*).
*   Gunakan HTML5 atribut: `type="tel"`, `maxlength="13"`, `autocomplete="tel"`.
*   Tombol *Submit* ("Daftar") dalam keadaan *disabled* (mati) dan baru akan *enabled* jika seluruh form tervalidasi dengan benar.

### 4.2. Kebutuhan Teknis (JavaScript)
*   Buat *Validation Engine Module* yang berisi *Regex* di atas.
*   Fungsi *sanitizer* internal: Otomatis menghapus karakter non-angka saat pengguna melakukan *paste* (tempel) teks.

---

## 5. Fase 2: Implementasi Back-End (Server-Side)
Lapisan ini adalah kunci keamanan utama. Semua data dari Front-End **harus** dianggap tidak aman (*Zero Trust Policy*).

### 5.1. Strategi Pemrosesan API
1.  **Sanitization:** Trim (potong spasi ujung) dan bersihkan karakter non-numerik menggunakan fungsi bawaan *backend* (misal: `preg_replace` di PHP atau `Regex.Replace` di C#).
2.  **Validation:** Jalankan *Regex Pattern Matching* persis seperti di spesifikasi matriks aturan.
3.  **Error Handling:**
    *   Jika data **tidak valid**, API mengembalikan status HTTP `400 Bad Request`.
    *   Sertakan format JSON yang jelas: `{ "status": "error", "code": "VAL_ERR_03", "message": "Format provider tidak terdaftar." }`
4.  **Rate Limiting:** Terapkan pembatasan permintaan (misal: maks 5 permintaan registrasi per IP per jam) untuk menghindari serangan peretasan API (*brute-force* pembuatan akun dummy).

---

## 6. Fase 3: Implementasi Database (Storage-Level)
Lapisan ini merupakan penjaga gawang (*Gatekeeper*) jika terjadi kecacatan logika pada level kode Back-End.

### 6.1. Pengamanan Skema
*   Implementasi `TRIGGER BEFORE INSERT` dan `TRIGGER BEFORE UPDATE` pada tabel `users_mhs`.
*   Trigger akan melempar `SIGNAL SQLSTATE '45000'` jika terdeteksi data yang melanggar batas Regex. (Sesuai dengan *script SQL Trigger* saat ini).
*   Gunakan Constraint `UNIQUE KEY` pada kolom `no_hp` untuk memastikan tidak ada duplikasi data.

---

## 7. Rencana Pengujian (Quality Assurance & Testing)

Tim QA wajib menjalankan skenario pengujian berikut sebelum fitur dirilis (*Go-Live*):

| Skenario Pengujian (Test Case) | Input Contoh | Ekspektasi Hasil (FE & BE) |
| :--- | :--- | :--- |
| Normal / Valid | `081283731599` | Lolos (Status HTTP 200/201) |
| Di bawah minimal digit (<10) | `0812345` | Ditolak (Error Panjang Data) |
| Di atas maksimal digit (>13) | `08123456789012`| Teks terpotong di FE / Lolos max length, tapi Ditolak |
| Mengandung Spasi/Huruf | `0812 34A` | Ditolak (Karakter Ilegal) |
| Prefix tidak valid | `084411223344` | Ditolak (Provider tidak dikenal) |
| Kembar Beruntun (Bot Test) | `081222227890` | Ditolak (Indikasi Spam) |
| Blok Berulang (Bot Test) | `08121212890` | Ditolak (Indikasi Spam) |
| Berurutan (Bot Test) | `0891234567` | Ditolak (Indikasi Dummy) |
| API Bypassing (Via Postman) | `{ "no_hp": "000" }`| Back-End menolak dengan 400 Bad Request (Trigger DB tidak tereksekusi) |

---

## 8. Jadwal Rilis (Rollout Timeline)
*   **Tahap 1 (Hari 1-2):** Penyesuaian kode Back-End API & Penambahan unit test (*Zero Trust Backend*).
*   **Tahap 2 (Hari 3-4):** Pengembangan UI/UX Front-End & integrasi *real-time validation*.
*   **Tahap 3 (Hari 5):** QA Testing menyeluruh (End-to-End Test).
*   **Tahap 4 (Hari 6):** Deployment ke *Production* Google Antigravity & Monitoring Log Error selama 24 jam pertama.
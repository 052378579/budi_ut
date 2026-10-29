# Telegram.md - Dokumentasi Sistem Notifikasi Bot Telegram

Dokumen ini menjelaskan arsitektur, konfigurasi, alur kerja, rute pemicu (*trigger routes*), dan format pesan sistem notifikasi **Bot Telegram** pada aplikasi **UT Tes Formatif** (`budi_ut`).

---

## 1. Ikhtisar & Arsitektur Notifikasi

Sistem notifikasi Bot Telegram berfungsi sebagai sarana pemantauan aktivitas *real-time* bagi administrator terhadap transaksi penting, kejadian keamanan (*security events*), dan log operasional pengguna tanpa perlu membuka dasbor web secara terus-menerus.

```
â”Œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”
â”‚     Aplikasi Web (CI4)          â”‚
â”‚   AuthApi / ProfilApi / UsersApiâ”‚
â””â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”¬â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”˜
                 â”‚
                 â”‚ (Helper: send_telegram_notif)
                 â”‚ cURL Non-Blocking
                 â”‚ (Timeout 2s, NOSIGNAL=1, HTML)
                 â–¼
â”Œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”
â”‚    Telegram Bot API Gateway     â”‚
â”‚   api.telegram.org/bot<TOKEN>   â”‚
â””â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”¬â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”˜
                 â”‚
                 â–¼
â”Œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”
â”‚     Kanal / Admin Telegram      â”‚
â”‚      Chat ID: 8784856529        â”‚
â””â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”˜
```

---

## 2. Kredensial & Konfigurasi Lingkungan

Konfigurasi dibaca secara terpusat melalui berkas lingkungan [`.env`](file:////192.168.1.4/www/budi_ut/.env):

| Variabel Lingkungan | Deskripsi | Status |
| :--- | :--- | :--- |
| `TELEGRAM_BOT_TOKEN` | Token otentikasi Bot dari @BotFather | Terkonfigurasi via `.env` |
| `TELEGRAM_CHAT_ID` | ID obrolan / Grup penerima notifikasi | Terkonfigurasi (`8784856529`) |

*Implementasi kode helper:* [app/Helpers/telegram_helper.php](file:////192.168.1.4/www/budi_ut/app/Helpers/telegram_helper.php)

---

## 3. Optimasi Kinerja & Ketahanan (Non-Blocking)

Pengiriman notifikasi ke Telegram menggunakan cURL dengan pertimbangan performa:
1. **Timeout Rendah (2 Detik):** Mencegah proses aplikasi web mengalami keterlambatan (*lag/freeze*) apabila koneksi internet ke Telegram melambat.
2. **`CURLOPT_NOSIGNAL = 1`:** Memastikan stabilitas saat dieksekusi di server berbasis multi-thread / proses Linux.
3. **Pencatatan Log Kegagalan:** Jika cURL mengalami eror jaringan, sistem tidak melempar eksepsi fatal ke peramban pengguna, melainkan mencatatnya secara senyap di `writable/logs/log-*.log`.

---

## 4. Matriks Rute Pemicu Notifikasi (*Trigger Routes*)

| Endpoint Rute API | Controller & Method | Kondisi Pemicu |
| :--- | :--- | :--- |
| `POST /api/auth/masuk` | `AuthApi::masuk` | Mahasiswa berhasil masuk atau terdaftar baru |
| `POST /api/auth/keluar` | `AuthApi::keluar` | Mahasiswa menekan tombol konfirmasi logout |
| `POST /api/profil/update/(:num)` | `ProfilApi::update` | Mahasiswa memperbarui nama atau UPBJJ-UT |
| `POST /api/profil/uploadFoto/(:num)` | `ProfilApi::uploadFoto` | Mahasiswa memperbarui pas foto diri |
| `POST /api/donasi/metode/create` | `DonasiApi::createMetode` | Admin menambahkan rekening tujuan donasi baru |

---

## 5. Peta Skenario Notifikasi & Template Pesan

### 5.1 Login / Masuk Mahasiswa (`AuthApi::masuk`)
Dipicu saat mahasiswa berhasil masuk:
```html
ðŸ”” <b>LOG AKTIVITAS: MASUK (LOGIN)</b>
ðŸ‘¤ <b>Nama:</b> Budi Santoso
ðŸ“± <b>No HP:</b> 08123456789
ðŸ“ <b>Lokasi:</b> Jakarta, ID (180.252.x.x)
â° <b>Waktu:</b> 2026-10-21 21:00:00 WIB
ðŸ›ï¸ <b>UPBJJ:</b> UT Jakarta
```

### 5.2 Logout Mahasiswa (`AuthApi::keluar`)
Dipicu saat mahasiswa menekan tombol "Keluar" dari modal konfirmasi:
```html
ðŸšª <b>LOG AKTIVITAS: KELUAR (LOGOUT)</b>
ðŸ‘¤ <b>Nama:</b> Budi Santoso
ðŸ“± <b>No HP:</b> 08123456789
â±ï¸ <b>Durasi Sesi:</b> 45 Menit
```

### 5.3 Registrasi Mahasiswa Baru
Dipicu saat nomor HP baru pertama kali masuk ke sistem:
```html
âœ¨ <b>PENGGUNA BARU TERDAFTAR</b>
ðŸ‘¤ <b>Nama:</b> Siti Rahma
ðŸ“± <b>No HP:</b> 085712345678
ðŸ›ï¸ <b>Daerah:</b> UT Bandung
ðŸŒ <b>IP Address:</b> 114.124.x.x
```

### 5.4 Perubahan Biodata / Profil (`ProfilApi::update`)
Dipicu saat mahasiswa memperbarui data diri atau mengganti pas foto:
```html
ðŸ“ <b>PEMBARUAN PROFIL MAHASISWA</b>
ðŸ‘¤ <b>Nama:</b> Budi Santoso
ðŸ”„ <b>Perubahan:</b> Nama Lengkap & UPBJJ
```

### 5.5 Transaksi Donasi & Paket Premium
Dipicu saat mahasiswa mengonfirmasi pengajuan donasi / premium:
```html
ðŸ’Ž <b>KONFIRMASI DONASI / PREMIUM</b>
ðŸ‘¤ <b>Nama:</b> Ahmad Dahlan
ðŸ“¦ <b>Paket:</b> Akses Premium Lifetime
ðŸ’° <b>Nominal:</b> Rp 50.000
ðŸ’³ <b>Metode:</b> GoPay (081313428000)
```

---

## 6. Rencana Pengembangan Lanjutan (Webhook Interaktif)

1. **Perintah Interaktif Dua Arah (/commands):**
   * `/status` : Menampilkan jumlah user online saat ini dari tabel `user_sessions`.
   * `/skor_hari_ini` : Rekapitulasi tes formatif yang dikerjakan hari ini.
   * `/approve_premium <id>` : Mengaktifkan status premium mahasiswa langsung dari aplikasi Telegram.
2. **Integrasi Alur Webhook:**
   * Menyiapkan route `POST /api/telegram/webhook` yang divalidasi dengan rahasia *secret token*.

---

## 7. Referensi Silang Tata Kelola (.agents/)

* **Gerbang Utama & Peta Navigasi:** [INDEX.md](file:////192.168.1.4/www/budi_ut/.agents/INDEX.md)
* **Peran Automation & Integration Agent:** [AGENTS.md](file:////192.168.1.4/www/budi_ut/.agents/AGENTS.md)
* **Matriks Seluruh Endpoint Rute:** [Tautan.md](file:////192.168.1.4/www/budi_ut/.agents/Tautan.md)
* **Aturan Non-Blocking & Error Handling:** [RULES.md](file:////192.168.1.4/www/budi_ut/.agents/RULES.md)
* **Peta Jalan Fitur Bot:** [Task.md](file:////192.168.1.4/www/budi_ut/.agents/Task.md)
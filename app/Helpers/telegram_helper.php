<?php

/**
 * telegram_helper
 * Helper khusus untuk mengirimkan notifikasi aktivitas sistem ke Bot Telegram.
 */

if (!function_exists('send_telegram_notif')) {
    
    /**
     * Fungsi untuk mengirim pesan ke Telegram
     * @param string $message Pesan yang ingin dikirim (Mendukung format HTML dasar)
     * @return string|bool Mengembalikan response dari Telegram atau false jika gagal
     */
    function send_telegram_notif($message)
    {
        // =====================================================================
        // KREDENSIAL BOT TELEGRAM
        // Dibaca murni dari berkas lingkungan .env peladen (STRICTLY CONFIDENTIAL)
        // DILARANG KERAS menaruh hardcoded token atau chat ID di berkas ini!
        // =====================================================================
        
        $token  = env('TELEGRAM_BOT_TOKEN') ?: (getenv('TELEGRAM_BOT_TOKEN') ?: ($_ENV['TELEGRAM_BOT_TOKEN'] ?? null));
        $chatId = env('TELEGRAM_CHAT_ID') ?: (getenv('TELEGRAM_CHAT_ID') ?: ($_ENV['TELEGRAM_CHAT_ID'] ?? null));

        // Jika kredensial belum disetel di .env, batalkan eksekusi secara aman (fail gracefully)
        if (empty($token) || empty($chatId)) {
            log_message('error', 'Notifikasi Telegram dibatalkan: TELEGRAM_BOT_TOKEN atau TELEGRAM_CHAT_ID belum disetel di .env');
            return false;
        }

        // =====================================================================

        // Endpoint API Telegram
        $url = "https://api.telegram.org/bot" . $token . "/sendMessage";

        // Parameter payload yang akan dikirim
        $payload = [
            'chat_id'    => $chatId,
            'text'       => $message,
            'parse_mode' => 'HTML' // Memungkinkan penggunaan tag <b>, <i>, <code>, <pre>, dll.
        ];

        // Inisiasi cURL
        $ch = curl_init();
        
        // Konfigurasi opsi cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Mematikan verifikasi SSL (Berguna jika dijalankan di localhost/XAMPP tanpa sertifikat)
        // Disarankan diubah menjadi true jika sudah di-deploy ke server production
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        
        // Set batas waktu eksekusi maksimal 2 detik agar aplikasi tidak hang jika API Telegram lambat
        curl_setopt($ch, CURLOPT_TIMEOUT, 2); 
        
        // OPTIMASI NON-BLOCKING:
        // Set agar cURL tidak perlu menunggu body response secara penuh sehingga sangat cepat.
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        
        // Eksekusi cURL
        $result = curl_exec($ch);
        
        // Tangkap pesan error jika cURL gagal tereksekusi
        $error = curl_error($ch);
        
        // Tutup koneksi cURL
        curl_close($ch);

        // Jika terjadi kesalahan koneksi cURL, catat ke dalam log CodeIgniter 4
        if ($error) {
            log_message('error', 'Gagal mengirim notifikasi Telegram: ' . $error);
            return false;
        }

        // Kembalikan hasil respons dari server Telegram (format JSON string)
        return $result;
    }
}

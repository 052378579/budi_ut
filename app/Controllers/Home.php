<?php

namespace App\Controllers;

use App\Models\MkModel;

/**
 * Home Controller
 * * Menangani tampilan halaman depan (Landing Page) utama aplikasi.
 * Controller ini bertugas memuat seluruh daftar mata kuliah yang aktif 
 * dari database untuk kemudian diserahkan dan dikelola oleh komponen Vue.js 
 * pada sisi antarmuka pengguna (Client-side).
 */
class Home extends BaseController
{
    /**
     * Menampilkan Beranda Utama Aplikasi
     * URL: localhost/
     * * @return string Rendered HTML View
     */
    public function index()
    {
        // 3. Siapkan paket data informasi untuk dikirim ke lapisan View
        // Payload data MK kini ditarik secara terpisah via AJAX (CSR) untuk meringankan beban SSR HTML
        $data = [
            'title'            => 'Beranda'
        ];

        // 4. Render dan kembalikan struktur halaman ke file view beranda utama
        return view('beranda_saya', $data);
    }

    /**
     * Menampilkan Halaman Pengumuman Segera Hadir / Fitur Baru
     * URL: /coming-soon
     * 
     * @return string Rendered HTML View
     */
    public function comingSoon()
    {
        return view('errors/html/coming_soon');
    }
}

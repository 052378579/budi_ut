<?php

namespace App\Controllers;

use App\Controllers\BaseController;

/**
 * FavoritController
 * * Bertugas khusus untuk merender (menampilkan) file View HTML halaman bookmark.
 * Halaman ini menyajikan daftar kartu mata kuliah yang telah disematkan oleh mahasiswa.
 * Sinkronisasi data aktual dan interaksi tombol ditangani di sisi klien oleh Vue.js 3 
 * yang berkomunikasi dengan RESTful endpoint FavoritApi.
 */
class FavoritController extends BaseController
{
    /**
     * Menampilkan Halaman Mata Kuliah Favorit Saya
     * URL: localhost/favorit
     * * @return mixed Redirect ke halaman beranda jika belum login, atau merender HTML View
     */
    public function index()
    {
        // ========================================================
        // FITUR BARU: PROTEKSI HALAMAN (WAJIB LOGIN)
        // ========================================================
        // Memastikan sesi mahasiswa aktif sehingga komponen Vue.js dapat menarik 
        // koleksi ID mata kuliah favorit berdasarkan variabel penanda 'id_user' yang valid.
        if (!session()->get('id_user')) {
            return redirect()->to('/')
                             ->with('error', 'Akses ditolak! Silakan masukkan Nama dan Nomor Handphone Anda terlebih dahulu untuk mengakses menu favorit.');
        }

        // Kumpulan payload data penunjang layout template
        $data = [
            'title' => 'Favorit'
        ];

        // Render struktur halaman ke file view favorit (favorit_saya)
        return view('favorit_saya', $data);
    }
}
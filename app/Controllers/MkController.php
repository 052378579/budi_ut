<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MkModel;

/**
 * MkController
 * Mengelola logika tampilan utama perkuliahan, direktori modul kuis (Tes Formatif), 
 * serta modul Ujian Akhir Semester (Simulasi UAS) untuk Mahasiswa.
 */
class MkController extends BaseController
{
    /**
     * Menampilkan Halaman Beranda Mata Kuliah (Daftar Modul Otomatis)
     * URL: localhost/(:segment) -> Contoh: localhost/STSI4201
     * Memindai direktori writable/MK/{kode_bmp}/ untuk menyusun daftar kuis yang tersedia.
     */
    public function courseIndex($kodeMk)
    {
        // 1. Proteksi Halaman: Wajib Login
        if (!session()->get('id_user')) {
            return redirect()->to('/')->with('error', 'Akses ditolak! Silakan masukkan Nama dan Nomor Handphone Anda terlebih dahulu.');
        }

        // 2. Ambil data Mata Kuliah dari database
        $mkModel = new MkModel();
        $mkData = $mkModel->getByKode($kodeMk);

        if (!$mkData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Mata kuliah dengan kode $kodeMk tidak ditemukan.");
        }

        // 3. Persiapkan jalur fisik ke folder penyimpanan bank soal JSON
        // PERBAIKAN KRITIKAL: Gunakan $mkData['kode_bmp'] bukan $kodeMk
        // Ini memastikan folder yang dipindai selalu sesuai dengan nama fisik direktori BMP di server.
        $path = WRITEPATH . 'MK/' . $mkData['kode_bmp'] . '/';
        $availableModules = [];

        // 4. Pindai folder berkas kuis secara dinamis
        if (is_dir($path)) {
            $files = scandir($path);
            foreach ($files as $file) {
                if (preg_match('/^modul(\d+)\.json$/i', $file, $matches)) {
                    $availableModules[] = (int) $matches[1];
                }
            }
        }

        // 5. Urutkan nomor modul secara ascending (1, 2, 3, dst)
        sort($availableModules);

        // 6. Kirim paket data ke View
        $data = [
            'title'     => $mkData['nama_mk'],
            'kodeMk'    => $kodeMk,
            'mkData'    => $mkData,
            'modules'   => $availableModules, // Akan terisi [1, 2, 3, 4, ...] jika berkasnya ada
            'id_user'   => session()->get('id_user'),
            'nama_user' => session()->get('nama_user')
        ];

        return view('modul_index', $data);
    }

    /**
     * Menampilkan Halaman Utama Kuis / Tes Formatif
     * URL: localhost/(:segment)/MODUL-(:num) -> Contoh: localhost/STSI4201/MODUL-1
     */
    public function index($kodeMk, $modul)
    {
        // 1. Proteksi Halaman: Cegah pengerjaan tanpa login session
        if (!session()->get('id_user')) {
            return redirect()->to('/')->with('error', 'Akses ditolak! Silakan login terlebih dahulu untuk mengerjakan soal.');
        }

        // 2. Ambil metadata untuk sinkronisasi ID MK komponen Vue.js
        $mkModel = new MkModel();
        $mkData = $mkModel->getByKode($kodeMk);

        if (!$mkData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Mata kuliah tidak ditemukan.");
        }

        // 3. Bundling payload data untuk View kuis
        $data = [
            'title'   => "{$kodeMk} - Modul " . (int)$modul,
            'idMk'    => $mkData['id_mk'],
            'kodeMk'  => $kodeMk,
            'modul'   => (int)$modul,
            'id_user' => session()->get('id_user')
        ];

        return view('tes_formatif', $data);
    }


    /**
     * Menampilkan Halaman Simulasi UAS (Ujian Akhir Semester)
     * URL: localhost/(:segment)/uas-simulasi -> Contoh: localhost/STSI4201/uas-simulasi
     */
    public function uasSimulasi($kodeMk)
    {
        // 1. Proteksi Halaman: Wajib Login
        if (!session()->get('id_user')) {
            return redirect()->to('/')->with('error', 'Akses ditolak! Silakan login terlebih dahulu untuk mengikuti simulasi UAS.');
        }

        // 2. Ambil metadata mata kuliah
        $mkModel = new MkModel();
        $mkData = $mkModel->getByKode($kodeMk);

        if (!$mkData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Mata kuliah tidak ditemukan.");
        }

        $data = [
            'title'  => 'Simulasi UAS - ' . $mkData['nama_mk'],
            'kodeMk' => $kodeMk,
            'mkData' => $mkData
        ];

        // 3. Deteksi Perangkat Cerdas (User-Agent Routing)
        $agent = $this->request->getUserAgent();

        if ($agent->isMobile()) {
            // Render antarmuka ringan untuk HP/Smartphone
            return view('uas_simulasi', $data); 
        } else {
            // Render antarmuka SUO Klasik (Versi Desktop)
            return view('desktop/uas_simulasi_pc', $data); 
        }
    }

    /**
     * Menampilkan Halaman Hasil Evaluasi Simulasi UAS
     * URL: localhost/(:segment)/uas-hasil -> Contoh: localhost/STSI4201/uas-hasil
     */
    public function uasHasil($kodeMk)
    {
        // 1. Proteksi Halaman: Wajib Login
        if (!session()->get('id_user')) {
            return redirect()->to('/')->with('error', 'Akses ditolak! Sesi Anda telah berakhir.');
        }

        // 2. Ambil data Mata Kuliah
        $mkModel = new MkModel();
        $mkData = $mkModel->getByKode($kodeMk);

        if (!$mkData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Mata kuliah tidak ditemukan.");
        }

        $data = [
            'title'  => 'Hasil Simulasi UAS - ' . $mkData['nama_mk'],
            'kodeMk' => $kodeMk,
            'mkData' => $mkData
        ];

        // 3. Deteksi Perangkat Cerdas (User-Agent Routing)
        $agent = $this->request->getUserAgent();

        if ($agent->isMobile()) {
            // Render laporan skor versi HP
            return view('uas_hasil', $data);
        } else {
            // Render laporan skor versi SUO Desktop
            return view('desktop/uas_hasil_pc', $data);
        }
    }
}
<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MkModel;
use CodeIgniter\API\ResponseTrait;

/**
 * Controller Dokumen
 * Bertugas secara khusus mengelola manajemen aset statis & dinamis (I/O) 
 * di dalam area terproteksi folder 'writable/MK/' dan 'writable/PROFIL/'.
 * Menangani streaming gambar cover BMP, pembacaan bank soal kuis JSON, unggah gambar, dan streaming pas foto profil.
 */
class Dokumen extends BaseController
{
    use ResponseTrait;

    protected $mkModel;

    public function __construct()
    {
        $this->mkModel = new MkModel();
    }

    /**
     * ROUTING SKENARIO 1: Menampilkan Data Metadata Berdasarkan Kode MK
     * URL: GET /dokumen/view/(:segment) -> Contoh: /dokumen/view/STSI4201
     * * Digunakan oleh Vue.js untuk mengambil detail informasi MK sebelum kuis dimulai.
     */
    public function view($kodeMk = null)
    {
        if (!$kodeMk) {
            return $this->failNotFound('Parameter Kode Mata Kuliah tidak dilampirkan.');
        }

        $dataMK = $this->mkModel->getByKode(strtoupper($kodeMk));

        if (!$dataMK) {
            return $this->failNotFound("Mata Kuliah dengan kode {$kodeMk} tidak ditemukan.");
        }

        return $this->respond($dataMK, 200);
    }

    /**
     * ROUTING SKENARIO 2: Secure Stream Image Cover (.webp) Berdasarkan Kode BMP
     * Mengadopsi struktur: writable/MK/{kode_bmp}/{kode_bmp}.webp
     * URL: GET /dokumen/cover/(:segment) -> Contoh: /dokumen/cover/SKOM4316
     * * Mem-bypass restriksi keamanan bawaan CI4 pada folder writable agar gambar 
     * dapat tampil di tag <img> pada browser klien.
     */
    public function cover($kodeBmp = null)
    {
        if (!$kodeBmp) {
            return $this->fail('Parameter Kode BMP tidak valid atau kosong.', 400);
        }

        // KEAMANAN (Anti Path-Traversal): Memastikan parameter murni berupa nama file/folder
        // Mencegah serangan eksploitasi URL mundur seperti: /dokumen/cover/../../../etc/passwd
        // Serta memaksa menjadi UPPERCASE untuk mencocokkan nama direktori di server Linux
        $kodeBmp = strtoupper(basename((string)$kodeBmp));

        // Menyusun path fisik berdasarkan struktur direktori baru
        $pathFile = WRITEPATH . 'MK/' . $kodeBmp . '/' . $kodeBmp . '.webp';
        
        // Path fallback jika cover spesifik BMP belum diunggah admin
        $fallbackPath = WRITEPATH . 'MK/default.webp';

        // Pengecekan eksistensi berkas fisik cover utama
        if (!file_exists($pathFile)) {
            $pathFile = $fallbackPath;
        }

        // Pengecekan eksistensi berkas cover default (sebagai jaring pengaman terakhir)
        if (!file_exists($pathFile)) {
            // Daripada mengembalikan 404 yang memicu pesan error merah di console,
            // kita lakukan redirect ke layanan placeholder image.
            return $this->response->redirect('https://placehold.co/450x600/eeeeee/999999.webp?text=Cover+BMP\\nBelum+Ada');
        }

        // Membaca binary data berkas gambar WebP
        $fileData = file_get_contents($pathFile);
        
        // Render binary ke browser klien disertai optimasi Header Cache
        return $this->response
                    ->setHeader('Content-Type', 'image/webp')
                    ->setHeader('Cache-Control', 'max-age=2592000, public') // Simpan di cache browser selama 30 hari
                    ->setBody($fileData);
    }

    /**
     * ROUTING SKENARIO 3: Secure Fetch Bank Soal JSON Berdasarkan Kode BMP & Nomor Modul
     * Mengadopsi struktur: writable/MK/{kode_bmp}/modul{X}.json
     * URL: GET /dokumen/soal/[:segment]/[:num] -> Contoh: /dokumen/soal/SKOM4316/1
     */
    public function soal($kodeBmp = null, $modulNum = null)
    {
        if (!$kodeBmp || $modulNum === null) {
            return $this->fail('Parameter Kode BMP atau Nomor Modul tidak lengkap.', 400);
        }

        // Sanitasi keamanan input parameter
        $kodeBmp = basename((string)$kodeBmp);
        $modulNum = (int)$modulNum; // Memastikan modul berupa angka murni

        // =========================================================
        // FITUR FREEMIUM DINAMIS
        // =========================================================
        $mkData = $this->mkModel->where('kode_bmp', $kodeBmp)->first();
        $premiumModules = ($mkData && !empty($mkData['modul_premium'])) ? explode(',', $mkData['modul_premium']) : [];
        
        // Cek apakah nomor modul yang diminta ada dalam daftar premium MK ini
        if (in_array((string)$modulNum, $premiumModules) && session()->get('is_premium') != 1 && session()->get('role') !== 'admin') {
            return $this->failForbidden("Akses Ditolak: Modul {$modulNum} adalah fitur Premium. Silakan tingkatkan keanggotaan Anda.");
        }

        // Merangkai target lokasi file JSON kuis formatif
        $pathFile = WRITEPATH . 'MK/' . $kodeBmp . '/modul' . $modulNum . '.json';

        if (!file_exists($pathFile)) {
            return $this->failNotFound("Berkas soal modul {$modulNum} untuk BMP {$kodeBmp} belum diunggah ke server.");
        }

        // Ekstraksi string isi file JSON
        $content = file_get_contents($pathFile);
        
        // Mengubah string JSON menjadi PHP Array lalu dikirim kembali via response RESTful (API) ke Vue.js
        return $this->respond(json_decode($content, true), 200);
    }

    /**
     * ROUTING SKENARIO 4: PROSES UNGGAH GAMBAR MODUL (.WEBP) UNTUK ADMIN
     * Menyimpan ke direktori: writable/MK/{kode_bmp}/{kode_bmp}.webp
     * URL: POST /dokumen/uploadBmp/(:num) -> Parameter adalah ID Mata Kuliah
     */
    public function uploadBmp($idMk = null)
    {
        if (!$idMk) {
            return $this->fail('ID Mata Kuliah wajib dilampirkan.', 400);
        }

        // Ambil metadata MK dari database untuk menentukan folder target (kode_bmp)
        $mkData = $this->mkModel->find($idMk);
        if (!$mkData) {
            return $this->failNotFound('Data Mata kuliah tidak terdaftar di sistem.');
        }

        $kodeBmp = $mkData['kode_bmp'];
        
        // Menangkap file multipart dari form submission
        $fileGambar = $this->request->getFile('image');

        // Validasi keberadaan dan integritas file yang diunggah
        if (!$fileGambar || !$fileGambar->isValid()) {
            return $this->fail('Berkas gambar tidak ditemukan atau file rusak (corrupted).', 400);
        }

        // Validasi ketat khusus format WebP demi efisiensi storage
        if ($fileGambar->getMimeType() !== 'image/webp' && $fileGambar->getExtension() !== 'webp') {
            return $this->fail('Format file ditolak! Sistem hanya menerima ekstensi .webp.', 400);
        }

        // Siapkan folder target di dalam lingkungan 'writable/MK/'
        $targetDir = WRITEPATH . 'MK/' . $kodeBmp . '/';
        
        // Jika folder BMP belum ada, ciptakan foldernya secara dinamis
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Pindahkan file gambar ke folder target dengan nama '{kode_bmp}.webp'
        // Parameter 'true' di akhir akan menimpa (overwrite) file lama jika sudah ada
        if ($fileGambar->move($targetDir, $kodeBmp . '.webp', true)) {
            
            // =========================================================================
            // AUTOMATIC COMPRESSION & RESIZE VIA CODEIGNITER IMAGE SERVICES
            // =========================================================================
            $pathFisikGambar = $targetDir . $kodeBmp . '.webp';
            $imageServices = \Config\Services::image();
            
            // Mengubah ukuran dan memotong proporsional (aspect ratio 3:4) max 450x600px
            // Disimpan ulang dalam format WebP dengan rasio kualitas 80% (Sangat hemat kuota!)
            $imageServices->withFile($pathFisikGambar)
                          ->fit(450, 600, 'center')
                          ->save($pathFisikGambar, 80);

            return $this->respond([
                'status'  => true,
                'message' => "Gambar sampul untuk Buku Materi Pokok {$kodeBmp} berhasil diunggah dan dikompresi."
            ], 200);
        }

        return $this->failServerError('Gagal menyimpan file gambar ke direktori fisik server STB.');
    }

    /**
     * ROUTING SKENARIO 5: Secure Stream Pas Foto Profil Mahasiswa (.webp)
     * Mengadopsi struktur: writable/PROFIL/{nama_file}.webp
     * URL: GET /api/dokumen/profil/(:segment)
     */
    public function profil($fileName = null)
    {
        if (!$fileName) {
            return $this->fail('Parameter Nama File tidak valid atau kosong.', 400);
        }

        // ANTI PATH-TRAVERSAL: Sanitasi agar nama file murni tanpa manipulasi ../
        $fileName = basename((string)$fileName);

        // Menyusun letak fisik berkas di dalam folder terproteksi
        $pathFile = WRITEPATH . 'PROFIL/' . $fileName;
        
        // Jaring pengaman (fallback) jika file tidak ditemukan atau user memakai foto bawaan
        $fallbackPath = WRITEPATH . 'PROFIL/mahasiswa.webp';

        if (!file_exists($pathFile)) {
            $pathFile = $fallbackPath;
        }

        // Jika file bawaan pun tidak ada di folder writable, berikan response 404
        if (!file_exists($pathFile)) {
            return $this->response->setStatusCode(404, 'Aset default profil tidak ditemukan di server.');
        }

        // Ambil binary data gambar
        $fileData = file_get_contents($pathFile);
        
        // Kirim response data gambar webp dengan optimasi cache browser 7 hari
        return $this->response
                    ->setHeader('Content-Type', 'image/webp')
                    ->setHeader('Cache-Control', 'max-age=604800, public')
                    ->setBody($fileData);
    }
}
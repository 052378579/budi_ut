<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Files\File;

/**
 * MkApi
 * RESTful API Controller untuk mengelola data Mata Kuliah.
 * Berkomunikasi langsung dengan frontend Vue.js menggunakan format JSON.
 */
class MkApi extends ResourceController
{
    // Secara otomatis menghubungkan controller ini dengan MkModel
    protected $modelName = 'App\Models\MkModel';
    protected $format    = 'json';

    // =========================================================================
    // 1. FUNGSI CRUD MATA KULIAH (UNTUK HALAMAN ADMIN)
    // =========================================================================

    /**
     * Menampilkan semua data Mata Kuliah
     * Method: GET /api/mk
     */
    public function index()
    {
        return $this->respond($this->model->orderBy('created_at', 'DESC')->findAll());
    }

    /**
     * Menyimpan data Mata Kuliah baru
     * Method: POST /api/mk/create
     */
    public function create()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        
        // Mencegah manipulasi id_mk dari payload eksternal
        if (array_key_exists('id_mk', $data)) {
            unset($data['id_mk']);
        }
        
        // Validasi Mencegah Tabrakan Rute (Route Collision)
        $reservedRoutes = ['admin', 'api', 'profil', 'saran', 'riwayat', 'favorit', 'unduh', 'privacy-policy', 'contact-us', 'prestasi', 'peringkat', 'peringkat-uas', 'dashboard', 'users', 'logs', 'pengumuman'];
        if (isset($data['kode_mk']) && in_array(strtolower($data['kode_mk']), $reservedRoutes)) {
            return $this->failValidationErrors(['kode_mk' => 'Kode MK tidak boleh menggunakan kata cadangan sistem (seperti: admin, profil, riwayat, dll).']);
        }
        
        $this->model->setValidationRule('kode_mk', 'required|is_unique[mata_kuliah.kode_mk]');
        if ($this->model->insert($data)) {
            return $this->respondCreated([
                'status'  => true, 
                'message' => 'Mata Kuliah berhasil ditambahkan ke dalam sistem.'
            ]);
        }
        
        return $this->failValidationErrors($this->model->errors());
    }

    /**
     * Memperbarui data Mata Kuliah
     * Method: PUT /api/mk/update/(:num)
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON(true) ?? $this->request->getRawInput();
        $data['id_mk'] = $id;
        
        // Validasi Mencegah Tabrakan Rute (Route Collision)
        $reservedRoutes = ['admin', 'api', 'profil', 'saran', 'riwayat', 'favorit', 'unduh', 'privacy-policy', 'contact-us', 'prestasi', 'peringkat', 'peringkat-uas', 'dashboard', 'users', 'logs', 'pengumuman'];
        if (isset($data['kode_mk']) && in_array(strtolower($data['kode_mk']), $reservedRoutes)) {
            return $this->failValidationErrors(['kode_mk' => 'Kode MK tidak boleh menggunakan kata cadangan sistem (seperti: admin, profil, riwayat, dll).']);
        }
        
        // Validasi unik dikecualikan untuk baris data yang sedang diperbarui (id_mk)
        $this->model->setValidationRule('kode_mk', "required|is_unique[mata_kuliah.kode_mk,id_mk,{$id}]");
        
        if ($this->model->update($id, $data)) {
            return $this->respond([
                'status'  => true, 
                'message' => 'Data Mata Kuliah berhasil diperbarui.'
            ]);
        }
        
        return $this->failValidationErrors($this->model->errors());
    }

    /**
     * Menghapus data Mata Kuliah
     * Method: DELETE /api/mk/delete/(:num)
     */
    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted([
                'status'  => true, 
                'message' => 'Data Mata Kuliah berhasil dihapus secara permanen.'
            ]);
        }
        
        return $this->failNotFound('Data tidak ditemukan atau sudah pernah dihapus sebelumnya.');
    }

    /**
     * Pencarian Mata Kuliah (Global Search)
     * Method: GET /api/mk/search?q=keyword
     */
    public function search()
    {
        $keyword = $this->request->getVar('q');
        
        if (empty($keyword)) {
            return $this->fail('Parameter pencarian (q) tidak boleh kosong.');
        }

        $results = $this->model->searchMK($keyword);
        return $this->respond($results);
    }

    // =========================================================================
    // 2. FUNGSI AKSES DATA FRONTEND (UNTUK VUE.JS MAHASISWA)
    // =========================================================================

    /**
     * Mengambil metadata Mata Kuliah berdasarkan Kode MK
     * Method: GET /api/mk/show/(:segment)
     */
    public function show($id = null)
    {
        $data = $this->model->getByKode($id);

        if (!$data) {
            return $this->failNotFound("Mata kuliah dengan kode '$id' tidak terdaftar.");
        }

        return $this->respond($data);
    }

    /**
     * Mengambil Berkas Soal JSON dari folder Writable
     * Method: GET /api/mk/soal/(:segment)/(:num)
     */
    public function getSoal($kodeMk, $modul)
    {
        // PERBAIKAN KEAMANAN: Mencegah Directory/Path Traversal
        if (!preg_match('/^[a-zA-Z0-9]+$/', $kodeMk)) {
            return $this->fail('Format Kode MK tidak valid atau terindikasi ilegal.', 400);
        }

        $path = WRITEPATH . $kodeMk . "/modul" . (int)$modul . ".json";

        if (file_exists($path)) {
            $content = file_get_contents($path);
            return $this->respond(json_decode($content));
        }

        return $this->failNotFound("Berkas soal modul $modul untuk $kodeMk belum tersedia di server.");
    }

    /**
     * Merender Gambar Sampul WebP dari folder Writable
     * Method: GET /api/mk/image/(:segment)
     */
    public function renderImage($kodeMk)
    {
        // PERBAIKAN KEAMANAN: Mencegah Directory/Path Traversal
        if (!preg_match('/^[a-zA-Z0-9]+$/', $kodeMk)) {
            return $this->fail('Format Kode MK tidak valid atau terindikasi ilegal.', 400);
        }

        $path = WRITEPATH . $kodeMk . "/" . $kodeMk . ".webp";

        if (file_exists($path)) {
            $file = new File($path);
            
            return $this->response
                        ->setHeader('Content-Type', $file->getMimeType())
                        ->setBody(file_get_contents($path));
        }

        // Fallback jika file gambar sampul buku materi (BMP) belum ada
        return redirect()->to('https://placehold.co/600x400/004da1/white?text=Gambar+Belum+Ada');
    }

    /**
     * Mengumpulkan soal untuk Simulasi UAS.
     * Logika: Memindai SELURUH file modulX.json di direktori,
     * lalu secara instan (menggunakan array_rand) mencabut 5 soal acak dari setiap modul.
     * Menyimpan informasi Modul Asal dan Nomor Soal Asli sebelum diacak total.
     * Method: GET /api/mk/uas-questions/(:segment)
     */
    public function getUasQuestions($kodeMk)
    {
        // 1. Validasi keamanan format Kode MK
        if (!preg_match('/^[a-zA-Z0-9]+$/', $kodeMk)) {
            return $this->fail('Format Kode MK tidak valid atau terindikasi ilegal.', 400);
        }

        // 2. Ambil metadata mata kuliah dari database
        $mkData = $this->model->where('kode_mk', $kodeMk)->first();
        if (!$mkData) {
            return $this->failNotFound("Mata kuliah dengan kode '$kodeMk' tidak ditemukan.");
        }

        $allQuestions = [];
        
        // 3. Persiapkan jalur ke folder penyimpanan BMP (Sinkron dengan direktori fisik)
        $path = WRITEPATH . 'MK/' . $mkData['kode_bmp'] . '/';

        // 4. Pindai (Scan) semua file di dalam folder secara dinamis
        if (is_dir($path)) {
            $files = scandir($path);
            
            foreach ($files as $file) {
                // Memfilter hanya file yang bernama 'modulX.json' (X = angka)
                if (preg_match('/^modul(\d+)\.json$/i', $file, $matches)) {
                    $modulNum = $matches[1]; // Ekstrak nomor modul
                    $filePath = $path . $file;
                    
                    $jsonContent = file_get_contents($filePath);
                    $questions = json_decode($jsonContent, true);
                    
                    if (is_array($questions) && !empty($questions)) {
                        
                        // Batasi pengambilan maksimal 5 soal (atau sejumlah total soal jika isinya kurang dari 5)
                        $limit = min(5, count($questions));
                        
                        // Mencabut Index secara acak tanpa harus mengacak (shuffle) seluruh array
                        // Dikonversi ke (array) untuk menanggulangi limit = 1 yang mengembalikan string/integer
                        $randomKeys = (array) array_rand($questions, $limit);
                        
                        // Masukkan ke keranjang soal utama dan beri identitas modul beserta nomor soal asli
                        foreach ($randomKeys as $key) {
                            $q = $questions[$key];
                            $q['source'] = "Modul {$modulNum}";
                            
                            // PERBAIKAN SINKRONISASI: Mengunci nomor indeks asli pada file JSON sebagai nomor soal asli (+1 karena indeks dimulai dari 0)
                            $q['original_no'] = $key + 1;
                            
                            $allQuestions[] = $q;
                        }
                    }
                }
            }
        }

        // Memastikan tidak menampilkan array kosong jika tidak ada soal yang berhasil dipindai
        if (empty($allQuestions)) {
            return $this->failNotFound("Bank soal ujian untuk $kodeMk belum tersedia secara lengkap di server.");
        }

        // 5. Acak KEMBALI keranjang soal utama
        // Agar soal dari Modul 1, 2, 3, dst posisinya tercampur rata saat dirender di antarmuka Vue
        shuffle($allQuestions);

        return $this->respond($allQuestions);
    }
}
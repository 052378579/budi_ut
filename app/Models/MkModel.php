<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * MkModel
 * * Mengelola metadata Mata Kuliah Universitas Terbuka, 
 * termasuk referensi ke file bank soal JSON, kontrol jumlah SKS (pembatas modul), 
 * dan elemen visual UI (tema warna Bootstrap).
 */
class MkModel extends Model
{
    // -------------------------------------------------------------------------
    // 1. KONFIGURASI TABEL UTAMA
    // -------------------------------------------------------------------------
    protected $table            = 'mata_kuliah';
    protected $primaryKey       = 'id_mk';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // -------------------------------------------------------------------------
    // 2. DAFTAR KOLOM YANG DIIZINKAN (ALLOWED FIELDS)
    // -------------------------------------------------------------------------
    // Disesuaikan dengan skema budi_ut_fixed.sql
    protected $allowedFields    = [
        'kode_bmp',       // Kode BMP untuk penamaan direktori bank soal JSON
        'img_bmp',        // Nama file gambar sampul buku materi (WebP)
        'kode_mk',        // Kode unik Mata Kuliah Resmi Kurikulum UT
        'nama_mk',        // Nama lengkap Mata Kuliah
        'program_studi',  // Fakultas/Program Studi penanggung jawab
        'jumlah_sks',     // Jumlah SKS (digunakan sebagai kontrol loop modul)
        'theme_color',    // Kelas warna background card di Bootstrap 5
        'modul_premium'   // Daftar modul premium yang dikunci
    ];

    // -------------------------------------------------------------------------
    // 3. PENGATURAN TIMESTAMP SERVER
    // -------------------------------------------------------------------------
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    // -------------------------------------------------------------------------
    // 4. ATURAN VALIDASI DATA (VALIDATION RULES)
    // -------------------------------------------------------------------------
    protected $validationRules = [
        'kode_bmp'      => 'required|min_length[3]|max_length[15]',
        'img_bmp'       => 'permit_empty|max_length[100]',
        // Exception validation untuk id_mk ketika melakukan proses UPDATE
        'kode_mk'       => 'required|max_length[20]|is_unique[mata_kuliah.kode_mk,id_mk,{id_mk}]',
        'nama_mk'       => 'required|min_length[3]|max_length[100]',
        // PERBAIKAN: Melonggarkan validasi agar menerima string gabungan untuk multiseleksi fakultas
        'program_studi' => 'required|string|max_length[100]',
        // SKS 2 = 6 modul, SKS 3 = 9 modul, SKS 4 = 12 modul
        'jumlah_sks'    => 'required|in_list[2,3,4]', 
        'theme_color'   => 'permit_empty|max_length[50]',
        'modul_premium' => 'permit_empty|string|max_length[255]'
    ];

    protected $validationMessages = [
        'kode_mk' => [
            'required'  => 'Kode Mata Kuliah resmi UT wajib diisi.',
            'is_unique' => 'Kode Mata Kuliah ini sudah terdaftar di pangkalan data.',
            'max_length'=> 'Kode Mata Kuliah tidak boleh lebih dari 20 karakter.'
        ],
        // PERBAIKAN: Pesan error disesuaikan dengan validasi string
        'program_studi' => [
            'required'   => 'Pilihan Program Studi (Fakultas) wajib diisi.',
            'string'     => 'Format Program Studi harus berupa teks.',
            'max_length' => 'Teks Program Studi tidak boleh melebihi 100 karakter.'
        ],
        'jumlah_sks' => [
            'required'  => 'Jumlah SKS wajib diisi sebagai penentu jumlah modul.',
            'in_list'   => 'Sistem hanya mendukung SKS 2 (6 modul), 3 (9 modul), atau 4 (12 modul).'
        ]
    ];

    // -------------------------------------------------------------------------
    // 5. METODE KUSTOM (CUSTOM METHODS)
    // -------------------------------------------------------------------------
    
    /**
     * Mengambil detail spesifik mata kuliah berdasarkan kode uniknya.
     * Digunakan oleh MkController untuk merender halaman kursus/uji kompetensi.
     * * @param string $kodeMk Kode Mata Kuliah (contoh: STSI4201)
     * @return array|object|null
     */
    public function getByKode($kodeMk)
    {
        return $this->where('kode_mk', $kodeMk)->first();
    }

    /**
     * Melakukan pencarian global pada katalog mata kuliah berdasarkan kata kunci.
     * Mencakup pencarian pada kode MK, nama MK, dan kode BMP.
     * * @param string $keyword Kata kunci pencarian dari sisi Frontend (Vue.js)
     * @return array Kumpulan hasil pencarian mata kuliah
     */
    public function searchMK($keyword)
    {
        return $this->groupStart()
                        ->like('kode_mk', $keyword)
                        ->orLike('nama_mk', $keyword)
                        ->orLike('kode_bmp', $keyword)
                    ->groupEnd()
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
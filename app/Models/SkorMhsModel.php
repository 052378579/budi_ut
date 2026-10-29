<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * SkorMhsModel
 * Mengelola data nilai transaksional kuis mahasiswa (Formatif & Simulasi UAS) 
 * serta menyajikan data agregasi untuk Papan Peringkat (Leaderboard) publik.
 */
class SkorMhsModel extends Model
{
    // -------------------------------------------------------------------------
    // 1. KONFIGURASI TABEL UTAMA
    // -------------------------------------------------------------------------
    protected $table            = 'skor_mhs';
    protected $primaryKey       = 'id_skor';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // -------------------------------------------------------------------------
    // 2. DAFTAR KOLOM YANG DIIZINKAN (ALLOWED FIELDS)
    // -------------------------------------------------------------------------
    // Kolom disesuaikan dengan struktur relasional budi_ut_fixed.sql
    protected $allowedFields    = [
        'id_user',       // Relasi ke pengguna (mahasiswa)
        'id_mk',         // Relasi ke mata kuliah
        'jenis_ujian',   // ENUM: 'formatif' atau 'uas'
        'modul_ke',      // Nomor modul (NULL jika jenis_ujian = 'uas')
        'skor'           // Nilai presisi (DECIMAL 5,2)
    ];

    // -------------------------------------------------------------------------
    // 3. PENGATURAN TIMESTAMP SERVER
    // -------------------------------------------------------------------------
    // Diset ke 'false' karena field waktu_selesai sudah ditangani secara otomatis 
    // oleh database melalui atribut TIMESTAMP DEFAULT CURRENT_TIMESTAMP.
    protected $useTimestamps    = false; 

    // -------------------------------------------------------------------------
    // 4. ATURAN VALIDASI DATA (VALIDATION RULES)
    // -------------------------------------------------------------------------
    protected $validationRules = [
        'id_user'     => 'required|numeric|is_not_unique[users_mhs.id_user]',
        'id_mk'       => 'required|numeric|is_not_unique[mata_kuliah.id_mk]',
        'jenis_ujian' => 'required|in_list[formatif,uas]',
        'modul_ke'    => 'permit_empty|numeric',
        // Memastikan skor berupa angka (termasuk desimal)
        'skor'        => 'required|numeric' 
    ];

    protected $validationMessages = [
        'id_user' => [
            'is_not_unique' => 'Identitas mahasiswa tidak ditemukan di pangkalan data.'
        ],
        'id_mk'   => [
            'is_not_unique' => 'Mata kuliah tidak terdaftar di sistem.'
        ],
        'jenis_ujian' => [
            'in_list' => 'Jenis ujian hanya menerima tipe formatif atau uas.'
        ]
    ];

    // -------------------------------------------------------------------------
    // 5. METODE KUSTOM (CUSTOM METHODS)
    // -------------------------------------------------------------------------
    
    /**
     * Mengambil data Papan Peringkat (Top 10) Berdasarkan Jenis Ujian.
     * Kueri ini dirancang untuk memanfaatkan INDEX idx_leaderboard_formatif 
     * dan idx_leaderboard_uas pada database demi performa tinggi.
     * * @param int $idMk ID Mata Kuliah
     * @param int|null $modul Nomor Modul (Gunakan NULL jika UAS)
     * @param string $jenisUjian 'formatif' atau 'uas'
     * @return array Daftar 10 peraih skor tertinggi
     */
    public function getRanking($idMk, $modul, $jenisUjian = 'formatif')
    {
        $builder = $this->select('skor_mhs.*, users_mhs.nama_user, users_mhs.no_hp')
                        ->join('users_mhs', 'users_mhs.id_user = skor_mhs.id_user')
                        ->where('skor_mhs.id_mk', $idMk)
                        ->where('skor_mhs.jenis_ujian', $jenisUjian);

        // Jika ujian formatif, filter spesifik berdasarkan modul
        if ($jenisUjian === 'formatif') {
            $builder->where('skor_mhs.modul_ke', $modul);
        } else {
            // Jika UAS, pastikan kueri mengabaikan nomor modul
            $builder->where('skor_mhs.modul_ke', null);
        }

        return $builder->orderBy('skor_mhs.skor', 'DESC')
                       // Tie-breaker: Jika skor sama, yang selesai duluan menang
                       ->orderBy('skor_mhs.waktu_selesai', 'ASC')
                       ->limit(10)
                       ->findAll();
    }

    /**
     * Mengambil Riwayat Kuis Komparatif milik Mahasiswa tertentu.
     * Digunakan pada halaman Dasbor Profil Mahasiswa (Riwayat Belajar).
     * * @param int $idUser ID Pengguna Mahasiswa
     * @return array Daftar riwayat pengerjaan tes
     */
    public function getUserHistory($idUser)
    {
        return $this->select('skor_mhs.*, mata_kuliah.nama_mk, mata_kuliah.kode_mk, mata_kuliah.kode_bmp, mata_kuliah.theme_color')
                    ->join('mata_kuliah', 'mata_kuliah.id_mk = skor_mhs.id_mk')
                    ->where('skor_mhs.id_user', $idUser)
                    ->orderBy('skor_mhs.waktu_selesai', 'DESC')
                    ->findAll();
    }
}
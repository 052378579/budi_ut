<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * FavoritMhsModel
 * Mengelola fitur bookmark atau semat kartu mata kuliah favorit mahasiswa.
 * Model ini menangani logika toggle (simpan/hapus) dan pengambilan data relasional.
 */
class FavoritMhsModel extends Model
{
    // -------------------------------------------------------------------------
    // 1. KONFIGURASI TABEL UTAMA
    // -------------------------------------------------------------------------
    protected $table            = 'favorit_mhs';
    protected $primaryKey       = 'id_favorit';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // -------------------------------------------------------------------------
    // 2. DAFTAR KOLOM YANG DIIZINKAN (ALLOWED FIELDS)
    // -------------------------------------------------------------------------
    // Disesuaikan dengan constraint foreign key di budi_ut_fixed.sql
    protected $allowedFields    = [
        'id_user', 
        'id_mk', 
        'created_at'
    ];
    
    // -------------------------------------------------------------------------
    // 3. PENGATURAN TIMESTAMP SERVER
    // -------------------------------------------------------------------------
    // Diset false karena field created_at akan disuntikkan secara manual
    // pada method toggleFavorit() agar lebih presisi sesuai waktu aksi user.
    protected $useTimestamps    = false; 

    // -------------------------------------------------------------------------
    // 4. METODE KUSTOM (CUSTOM METHODS)
    // -------------------------------------------------------------------------

    /**
     * Logika Cerdas Toggle Bookmark (Tambah/Hapus Sekaligus).
     * Jika relasi (id_user & id_mk) sudah ada, maka hapus dari favorit.
     * Jika belum ada, maka tambahkan ke favorit.
     * * @param int $idUser ID Pengguna Mahasiswa
     * @param int $idMk ID Mata Kuliah
     * @return array Status dan pesan balasan untuk API Vue.js
     */
    public function toggleFavorit($idUser, $idMk)
    {
        // Cek apakah data sudah disematkan sebelumnya
        $existing = $this->where('id_user', $idUser)
                         ->where('id_mk', $idMk)
                         ->first();

        if ($existing) {
            // Jika sudah ada, hapus dari database (Un-favorite)
            $this->delete($existing['id_favorit']);
            return [
                'status'  => 'deleted',
                'message' => 'Mata kuliah berhasil dihapus dari daftar favorit.'
            ];
        } else {
            // Jika belum ada, masukkan ke database (Favorite)
            $this->insert([
                'id_user'    => $idUser,
                'id_mk'      => $idMk,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            return [
                'status'  => 'inserted',
                'message' => 'Mata kuliah berhasil disematkan ke daftar favorit.'
            ];
        }
    }

    /**
     * Memuat daftar mata kuliah yang telah difavoritkan mahasiswa.
     * Melakukan JOIN ke tabel mata_kuliah untuk mengambil detail metadata 
     * dan kelas tema warna (Bootstrap 5) dari masing-masing MK.
     * * @param int $idUser ID Pengguna Mahasiswa
     * @return array Daftar lengkap metadata mata kuliah favorit
     */
    public function getFavoritByUser($idUser)
    {
        return $this->db->table($this->table)
            ->select('mata_kuliah.*, favorit_mhs.created_at as tanggal_favorit')
            ->join('mata_kuliah', 'mata_kuliah.id_mk = favorit_mhs.id_mk')
            ->where('favorit_mhs.id_user', $idUser)
            ->orderBy('favorit_mhs.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Mengambil koleksi ID Mata Kuliah dalam bentuk array 1-Dimensi.
     * Sangat berguna untuk kebutuhan reaktif (state management) di Vue.js 
     * agar icon 'Hati' dapat langsung dirender dengan warna solid (sudah difavoritkan).
     * * @param int $idUser ID Pengguna Mahasiswa
     * @return array Array berisi deretan id_mk (contoh: [1, 5, 8])
     */
    public function getArrayIdMkFavorit($idUser)
    {
        $data = $this->select('id_mk')
                     ->where('id_user', $idUser)
                     ->findAll();
                     
        return array_column($data, 'id_mk');
    }
}
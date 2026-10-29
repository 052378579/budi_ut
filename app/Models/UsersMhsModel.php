<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UsersMhsModel
 * * Mengelola data identitas mahasiswa, hak akses admin (role), 
 * serta relasi ke daerah layanan Universitas Terbuka (UPBJJ-UT).
 */
class UsersMhsModel extends Model
{
    // -------------------------------------------------------------------------
    // 1. KONFIGURASI TABEL UTAMA
    // -------------------------------------------------------------------------
    protected $table            = 'users_mhs';
    protected $primaryKey       = 'id_user';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // -------------------------------------------------------------------------
    // 2. DAFTAR KOLOM YANG DIIZINKAN (ALLOWED FIELDS)
    // -------------------------------------------------------------------------
    // Disesuaikan dengan skema budi_ut_fixed.sql (termasuk penambahan fitur profil)
    protected $allowedFields    = [
        'no_hp',      // Kredensial utama & password sederhana (mahasiswa)
        'nim_mhs',    // NIM resmi mahasiswa UT (9 digit)
        'kode_ut',    // Relasi ke tabel ut_daerah (2 digit kode wilayah)
        'img_mhs',    // Avatar identitas visual
        'password',   // Kata sandi terenkripsi (khusus akses Admin)
        'nama_user',  // Nama lengkap pengguna
        'role',       // Hak akses (ENUM: 'admin', 'mahasiswa')
        'is_premium'  // Status akun Premium (0 = Gratis, 1 = Premium)
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
        'nama_user' => 'required|min_length[3]|max_length[100]',
        'no_hp'     => 'required|numeric|min_length[9]|max_length[16]|is_unique[users_mhs.no_hp,id_user,{id_user}]',
        'nim_mhs'   => 'permit_empty|numeric|exact_length[9]',
        'kode_ut'   => 'permit_empty|is_not_unique[ut_daerah.kode_ut]',
        'role'      => 'permit_empty|in_list[admin,mahasiswa]',
        'password'  => 'permit_empty|min_length[6]|max_length[255]',
        'img_mhs'   => 'permit_empty|max_length[100]'
    ];

    protected $validationMessages = [
        'no_hp' => [
            'required'    => 'Nomor handphone wajib diisi.',
            'is_unique'   => 'Nomor handphone ini sudah terdaftar di sistem.',
            'numeric'     => 'Nomor handphone hanya boleh berisi angka.',
            'min_length'  => 'Nomor handphone minimal 9 digit.',
            'max_length'  => 'Nomor handphone maksimal 16 digit.'
        ],
        'nim_mhs' => [
            'exact_length' => 'NIM Resmi harus tepat 9 digit angka.',
            'numeric'      => 'NIM hanya boleh berisi angka.'
        ],
        'kode_ut' => [
            'is_not_unique' => 'Kode daerah UT tidak valid atau tidak ditemukan di database master.'
        ]
    ];

    // -------------------------------------------------------------------------
    // 5. METODE KUSTOM (CUSTOM METHODS)
    // -------------------------------------------------------------------------
    
    /**
     * Mengambil semua data pengguna beserta statistik aktivitas mereka.
     * Menggunakan LEFT JOIN untuk menarik nama UPBJJ-UT dan menghitung 
     * berapa banyak mata kuliah yang telah disematkan (favorit).
     * * @return array Kumpulan data pengguna lengkap dengan agregasi.
     */
    public function getAllUsersWithStats()
    {
        return $this->db->table($this->table)
            ->select('
                users_mhs.id_user, 
                users_mhs.nama_user, 
                users_mhs.no_hp, 
                users_mhs.nim_mhs, 
                users_mhs.role, 
                users_mhs.is_premium,
                users_mhs.created_at, 
                ut_daerah.nama_ut
            ')
            ->select('COUNT(DISTINCT favorit_mhs.id_favorit) as total_favorit')
            // Join ke Master Daerah UT
            ->join('ut_daerah', 'ut_daerah.kode_ut = users_mhs.kode_ut', 'left')
            // Join ke Transaksi Favorit untuk diagregasi
            ->join('favorit_mhs', 'favorit_mhs.id_user = users_mhs.id_user', 'left')
            // Pengelompokan data berdasarkan pengguna
            ->groupBy('users_mhs.id_user, ut_daerah.nama_ut')
            // Urutkan dari pengguna yang paling baru mendaftar
            ->orderBy('users_mhs.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Mencari dan memvalidasi kredensial pengguna (Proses Login Mahasiswa).
     * * @param string $no_hp Nomor handphone dari form login.
     * @return array|object|null Data user jika ditemukan.
     */
    public function authenticateStudent($no_hp)
    {
        return $this->where('no_hp', $no_hp)
                    ->where('role', 'mahasiswa')
                    ->first();
    }

    /**
     * Mengambil data statistik pendaftar berdasarkan rentang waktu
     */
    public function getStatistikPendaftar($filter = '7')
    {
        $builder = $this->builder();
        $builder->select('DATE(created_at) as tanggal, COUNT(id_user) as total');
        $builder->where('role', 'mahasiswa');

        // Filter berdasarkan jumlah hari, abaikan jika 'semua'
        if ($filter !== 'semua') {
            $days = (int)$filter;
            $builder->where("created_at >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY)");
        }

        $builder->groupBy('DATE(created_at)');
        $builder->orderBy('DATE(created_at)', 'ASC');

        return $builder->get()->getResultArray();
    }
}
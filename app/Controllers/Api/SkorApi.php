<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

/**
 * SkorApi
 * Mengelola antarmuka RESTful API untuk perekaman nilai ujian mahasiswa,
 * pengambilan data leaderboard (papan peringkat), serta riwayat kuis.
 */
class SkorApi extends ResourceController
{
    // Menghubungkan otomatis dengan model SkorMhsModel
    protected $modelName = 'App\Models\SkorMhsModel';
    protected $format    = 'json';

    /**
     * Skenario 1: Menyimpan Skor Baru (Dipanggil Vue.js setelah kuis/UAS selesai)
     * Method: POST /api/skor/create
     */
    public function create()
    {
        // 1. PENGAMANAN KRITIS: Ambil ID dari Sesi Server (Bukan dari payload JSON klien)
        $idUserSession = session()->get('id_user');
        if (!$idUserSession) {
            return $this->failUnauthorized('Akses ditolak. Sesi tidak valid atau telah berakhir.');
        }

        // 2. Tangkap request JSON dari Vue.js
        $reqData = $this->request->getJSON(true) ?? $this->request->getPost();
        
        // Logika Pintar: Jika parameter 'jenis_ujian' tidak dikirim, asumsikan kuis 'formatif' biasa
        $jenisUjian = $reqData['jenis_ujian'] ?? 'formatif';

        // 3. VALIDASI INTEGRITAS DATA (Cegah Injeksi Tipe Data Aneh)
        if (!in_array($jenisUjian, ['formatif', 'uas'])) {
            return $this->failValidationErrors('Jenis ujian tidak dikenali sistem.');
        }

        $modulKe = ($jenisUjian === 'uas') ? null : ($reqData['modul_ke'] ?? null);
        if ($jenisUjian === 'formatif' && !is_numeric($modulKe)) {
            return $this->failValidationErrors('ID Modul (modul_ke) harus berupa angka valid.');
        }

        // 4. Penyelarasan Payload dengan tabel skor_mhs terbaru
        $data = [
            'id_user'     => $idUserSession, // PENGAMANAN: Gunakan ID dari sesi
            'id_mk'       => $reqData['id_mk'] ?? null,
            'jenis_ujian' => $jenisUjian,
            'modul_ke'    => $modulKe,
            'skor'        => isset($reqData['skor']) ? floatval($reqData['skor']) : null 
        ];

        // Eksekusi insert melalui model
        if ($this->model->insert($data)) {
            return $this->respondCreated([
                'status'  => true,
                'message' => 'Skor evaluasi Anda berhasil dikirim ke server.'
            ]);
        }

        // Kembalikan pesan error jika validasi model (SkorMhsModel) gagal
        return $this->failValidationErrors($this->model->errors());
    }

    /**
     * Skenario 2: Mengambil Peringkat Mahasiswa dengan Sensor No HP (Privasi)
     * Method: GET /api/skor/ranking/(:num)/(:num)?tipe=formatif/uas
     */
    public function ranking($idMk, $modul)
    {
        // Deteksi jenis ujian via Query Parameter (contoh: /api/skor/ranking/1/0?tipe=uas)
        // Default (fallback) ke 'formatif' jika parameter tidak tersedia
        $jenisUjian = $this->request->getGet('tipe') ?? 'formatif';
        
        // Memanggil fungsi cerdas getRanking() di SkorMhsModel
        $results = $this->model->getRanking($idMk, $modul, $jenisUjian);

        // Keamanan Data Privasi: Sensor (Masking) nomor HP mahasiswa
        foreach ($results as &$row) {
            $hp = $row['no_hp'];
            // Pastikan string HP valid sebelum dipotong
            if (!empty($hp) && strlen($hp) >= 10) {
                // Mengubah format 081234567890 menjadi 08123456**90
                $row['no_hp'] = substr($hp, 0, 8) . '**' . substr($hp, -2);
            }
        }

        return $this->respond($results);
    }

    /**
     * Skenario 3: Mengambil Semua Riwayat Skor (Khusus Dashboard Admin)
     * Method: GET /api/skor
     */
    public function index()
    {
        // Mengambil seluruh data skor dan melakukan relasi JOIN ke users_mhs dan mata_kuliah
        // agar nama_user, no_hp, dan nama_mk dapat ditampilkan pada tabel admin Vue.js
        $data = $this->model->select('skor_mhs.*, users_mhs.nama_user, users_mhs.no_hp, mata_kuliah.nama_mk')
                            ->join('users_mhs', 'users_mhs.id_user = skor_mhs.id_user', 'left')
                            ->join('mata_kuliah', 'mata_kuliah.id_mk = skor_mhs.id_mk', 'left')
                            ->orderBy('skor_mhs.waktu_selesai', 'DESC')
                            ->findAll();
                            
        return $this->respond($data);
    }

    /**
     * Skenario 4: Menghapus Riwayat Skor (Fitur Admin)
     * Method: DELETE /api/skor/delete/(:num)
     */
    public function delete($id = null)
    {
        // PENGAMANAN IDOR: Ambil ID sesi dan temukan data skor
        $idUserSession = session()->get('id_user');
        $skor = $this->model->find($id);

        if (!$skor) {
            return $this->failNotFound('Data skor tidak ditemukan atau sudah dihapus.');
        }

        // Pastikan hanya pemilik nilai (atau admin) yang boleh menghapus
        if ($skor['id_user'] != $idUserSession && session()->get('role') !== 'admin') {
            return $this->failForbidden('Akses Ditolak. Anda tidak berhak menghapus riwayat nilai mahasiswa lain.');
        }

        // Proses hapus berdasarkan ID Skor
        if ($this->model->delete($id)) {
            return $this->respondDeleted([
                'status'  => true,
                'message' => 'Data riwayat skor berhasil dihapus dari sistem.'
            ]);
        }
        
        return $this->fail('Terjadi kesalahan saat menghapus data skor.');
    }

    /**
     * Skenario 5: Mengambil Riwayat Skor Milik Satu User Spesifik (Untuk Profil Mahasiswa)
     * PERBAIKAN: Mengubah nama method dari history() menjadi user() agar klop dengan pemanggilan fetch frontend Vue.js
     * Method: GET /api/skor/user/(:num)
     */
    public function user($idUser = null)
    {
        // Validasi jika ID Pengguna kosong atau tidak valid
        if (!$idUser) {
            return $this->failValidationErrors('ID Pengguna (id_user) tidak valid atau kosong.');
        }

        // Memanggil method kustom dari SkorMhsModel (Mengambil riwayat berdasar id_user)
        $data = $this->model->getUserHistory($idUser);
        
        return $this->respond($data);
    }
}
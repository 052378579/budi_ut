<?php

namespace App\Controllers\Api;

use App\Models\FavoritMhsModel;
use App\Models\MkModel;
use CodeIgniter\RESTful\ResourceController;
use Exception; // Tambahkan Use Exception untuk menangkap kesalahan

class FavoritApi extends ResourceController
{
    protected $favoritModel;
    protected $mkModel;
    protected $format = 'json'; // Memastikan respons default adalah JSON

    public function __construct()
    {
        $this->favoritModel = new FavoritMhsModel();
        $this->mkModel = new MkModel();
    }

    /**
     * Mengambil semua mata kuliah favorit untuk user tertentu.
     * GET /api/favorit/list/{idUser}
     */
    public function index($idUser = null)
    {
        // PERBAIKAN: Tangkap parameter langsung dari parameter fungsi (Sesuai Routes /(:num) )
        if (!$idUser) {
            return $this->fail('ID User tidak valid atau tidak ditemukan.', 400);
        }

        // PERBAIKAN: Meluruskan nama tabel menjadi 'favorit_mhs' dan 'mata_kuliah'
        $data = $this->favoritModel->where('favorit_mhs.id_user', $idUser)
            ->join('mata_kuliah', 'mata_kuliah.id_mk = favorit_mhs.id_mk')
            ->orderBy('favorit_mhs.created_at', 'DESC')
            ->findAll();

        return $this->respond([
            'status' => 'success',
            'data' => $data
        ], 200);
    }

    /**
     * Mengambil hanya daftar ID Mata Kuliah yang difavoritkan oleh user.
     * Digunakan oleh front-end fetchFavoritIds().
     * GET /api/favorit/ids/{idUser}
     */
    public function showIds($idUser = null)
    {
        // PERBAIKAN: Tangkap parameter langsung dari parameter fungsi (Sesuai Routes /(:num) )
        if (!$idUser) {
            return $this->fail('ID User tidak valid atau tidak ditemukan.', 400);
        }

        // Ambil hanya kolom id_mk
        $favoritIdsData = $this->favoritModel->where('id_user', $idUser)
            ->select('id_mk')
            ->findAll();

        // Flatten array untuk mendapatkan array linear ID
        $favoritIds = array_column($favoritIdsData, 'id_mk');

        return $this->respond([
            'status' => 'success',
            'data' => $favoritIds
        ], 200);
    }

    /**
     * Menambah atau menghapus mata kuliah dari favorit secara toggle.
     * POST /api/favorit/toggle
     */
    public function toggleFavorite()
    {
        // Mendapatkan data dari body request JSON
        $json = $this->request->getJSON();
        $idUser = $json->id_user ?? null;
        $idMk = $json->id_mk ?? null;

        if (!$idUser || !$idMk) {
            return $this->failValidationErrors('ID User dan ID Mata Kuliah wajib diisi.');
        }

        // 1. Dapatkan koneksi database eksplisit
        $db = \Config\Database::connect();

        try {
            // 2. Mulai transaksi eksplisit
            $db->transBegin();

            // 3. Cek keberadaan favorit
            $existing = $this->favoritModel->where([
                'id_user' => $idUser,
                'id_mk'   => $idMk
            ])->first();

            $isRemoving = false;

            if ($existing) {
                // Proses Hapus (jika sudah ada)
                $isRemoving = true;
                // Mencoba menghapus
                if (!$this->favoritModel->delete($existing['id_favorit'])) {
                    // Ambil kesalahan dari model jika ada
                    $errors = $this->favoritModel->errors();
                    $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Gagal menghapus dari favorit.';
                    throw new Exception($errorMsg); // Lemparkan exception
                }
            } else {
                // Proses Tambah (jika belum ada)
                
                // Validasi keberadaan Mata Kuliah terlebih dahulu
                if (!$this->mkModel->find($idMk)) {
                    throw new Exception('Mata kuliah tidak valid atau tidak ditemukan.');
                }

                $favoritData = [
                    'id_user' => $idUser,
                    'id_mk'   => $idMk
                ];

                // Mencoba menambah
                if (!$this->favoritModel->insert($favoritData)) {
                    // Ambil kesalahan dari model jika ada (misal validasi model gagal)
                    $errors = $this->favoritModel->errors();
                    $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Gagal menambahkan ke favorit.';
                    throw new Exception($errorMsg); // Lemparkan exception
                }
            }

            // 4. Periksa apakah operasi model menyebabkan kegagalan database internal
            if ($db->transStatus() === false) {
                throw new Exception('Terjadi kesalahan database internal saat memproses permintaan.');
            }

            // 5. Commit transaksi jika semua langkah di atas berhasil
            $db->transCommit();

            // 6. Respon Sukses dioptimalkan (Kirim pesan dan status baru)
            $actionWord = $isRemoving ? 'menghapus' : 'menambahkan';
            return $this->respond([
                'status' => 'success',
                'message' => "Berhasil {$actionWord} favorit.",
                'data' => [
                    'id_mk' => $idMk,
                    'is_favorite' => !$isRemoving // Mengembalikan status favorit terbaru (True jika ditambah, False jika dihapus)
                ]
            ], 200);

        } catch (Exception $e) {
            // 7. Rollback semua perubahan jika ada exception yang tertangkap
            $db->transRollback();

            // Log kesalahan rinci untuk debugging back-end
            log_message('error', '[FavoritApi::toggleFavorite] Exception tertangkap: ' . $e->getMessage());

            // Kembalikan respons error ke front-end (Status 500)
            return $this->respond([
                'status' => 'error',
                'message' => 'Gagal mengubah favorit: ' . $e->getMessage() // Sertakan pesan kesalahan spesifik
            ], 500);
        }
    }
}
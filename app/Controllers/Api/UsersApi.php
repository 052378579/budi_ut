<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

/**
 * UsersApi
 * Mengelola endpoint RESTful API untuk manajemen data pengguna (mahasiswa)
 * pada sisi Dasbor Administrator Proyek UT Tes Formatif.
 * Telah terintegrasi dengan Bot Telegram untuk pencatatan log aktivitas admin.
 */
class UsersApi extends ResourceController
{
    // Menghubungkan secara otomatis controller ini dengan UsersMhsModel
    protected $modelName = 'App\Models\UsersMhsModel';
    protected $format    = 'json';

    public function __construct()
    {
        helper('telegram_helper');
    }

    /**
     * Mengambil Semua Data Pengguna Beserta Statistik Agregat
     * Digunakan oleh Vue.js untuk merender tabel data mahasiswa terdaftar.
     * Method: GET /api/users
     * @return mixed JSON Response
     */
    public function index()
    {
        // Memanggil fungsi getAllUsersWithStats() dari UsersMhsModel 
        // yang sudah ter-join dengan tabel ut_daerah dan favorit_mhs
        $data = $this->model->getAllUsersWithStats();
        
        return $this->respond($data, 200);
    }

    /**
     * Endpoint Tambahan: POST /api/users/create
     * Digunakan oleh Admin untuk input data Mahasiswa Baru secara manual via Backoffice.
     */
    public function create()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        
        // Mencegah manipulasi id_user jika ada yang mencoba mengirimkannya
        if (array_key_exists('id_user', $data)) {
            unset($data['id_user']);
        }

        // Terapkan aturan penulisan nama KAPITAL murni untuk konsistensi database
        if (isset($data['nama_user'])) {
            $data['nama_user'] = strtoupper(trim($data['nama_user']));
        }

        // Simpan data ke database
        if ($this->model->insert($data)) {
            $newId = $this->model->getInsertID();

            // BOT TELEGRAM: Input Manual oleh Administrator
            $msgAdminAdd = "💼 <b>USER BARU DITAMBAHKAN (ADMIN)</b>\n"
                         . "--------------------------------\n"
                         . "👤 Nama Mhs: <b>" . esc($data['nama_user']) . "</b>\n"
                         . "📱 No. HP: <code>" . esc($data['no_hp'] ?? '-') . "</code>\n"
                         . "⚙️ Dibuat Oleh: Administrator\n"
                         . "🕒 Waktu: " . date('d-m-Y H:i:s') . " WIB\n"
                         . "--------------------------------";
            send_telegram_notif($msgAdminAdd);

            return $this->respondCreated([
                'status'  => true, 
                'message' => 'Data mahasiswa baru berhasil diinput oleh Admin.'
            ]);
        }
        
        // Kembalikan pesan error validasi jika gagal (misal: Nomor HP sudah terdaftar)
        return $this->failValidationErrors($this->model->errors());
    }

    /**
     * Menghapus Data Pengguna Secara Permanen dari Sistem
     * Method: DELETE /api/users/delete/(:num)
     * @param int|null $id ID Pengguna (id_user) yang akan dihapus
     * @return mixed JSON Response
     */
    public function delete($id = null)
    {
        // 1. Validasi awal: Pastikan parameter ID dikirim dan tidak kosong
        if (!$id || !is_numeric($id)) {
            return $this->fail('ID Pengguna (id_user) tidak valid atau kosong.', 400);
        }

        // 2. Periksa keberadaan data di database pangkalan data sebelum dihapus
        $user = $this->model->find($id);
        if (!$user) {
            return $this->failNotFound('Data pengguna tidak ditemukan di sistem.');
        }

        // 3. Eksekusi penghapusan data secara permanen
        // Catatan Arsitektur: Karena foreign key pada tabel skor_mhs dan favorit_mhs 
        // telah dikonfigurasi dengan properti ON DELETE CASCADE di database,
        // maka seluruh riwayat belajar dan bookmark kuis milik user ini otomatis ikut terhapus.
        if ($this->model->delete($id)) {
            
            // BOT TELEGRAM: Pencatatan Log Penghapusan Akun
            $msgDelete = "🗑️ <b>AKUN MAHASISWA DIHAPUS</b>\n"
                       . "--------------------------------\n"
                       . "👤 Nama: " . esc($user['nama_user']) . "\n"
                       . "📱 No. HP: <code>" . esc($user['no_hp']) . "</code>\n"
                       . "🕒 Waktu Eksekusi: " . date('d-m-Y H:i:s') . " WIB\n"
                       . "⚠️ <i>Seluruh riwayat nilai dan sesi ikut terhapus secara kaskade (cascade).</i>";
            send_telegram_notif($msgDelete);

            return $this->respondDeleted([
                'status'  => 'success',
                'message' => 'Akun mahasiswa bernama ' . $user['nama_user'] . ' beserta seluruh riwayat nilainya telah berhasil dihapus dari sistem secara permanen.'
            ]);
        }

        // Fallback jika terjadi kendala tak terduga pada server database
        return $this->failServerError('Gagal menghapus data pengguna. Sila coba beberapa saat lagi.');
    }

    /**
     * Memperbarui Status Premium Pengguna
     * Method: PUT /api/users/premium/(:num)
     */
    public function updatePremium($id = null)
    {
        if (!$id || !is_numeric($id)) {
            return $this->fail('ID Pengguna (id_user) tidak valid.', 400);
        }

        $user = $this->model->find($id);
        if (!$user) {
            return $this->failNotFound('Data pengguna tidak ditemukan.');
        }

        $data = $this->request->getJSON(true) ?? $this->request->getRawInput();
        if (!isset($data['is_premium'])) {
            return $this->fail('Status premium tidak disertakan dalam request.', 400);
        }

        $is_premium = $data['is_premium'] == 1 ? 1 : 0;

        if ($this->model->update($id, ['is_premium' => $is_premium])) {
            return $this->respond([
                'status' => true,
                'message' => 'Status premium berhasil diperbarui.'
            ]);
        }

        return $this->failServerError('Gagal memperbarui status premium.');
    }
}
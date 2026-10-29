<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UsersMhsModel;

/**
 * ProfilApi
 * Mengelola pembacaan dan pembaruan data profil mahasiswa, 
 * termasuk fitur unggah (upload) pas foto profil (Square 1:1) ke direktori writable.
 */
class ProfilApi extends ResourceController
{
    protected $format = 'json';

    public function __construct()
    {
        helper('telegram_helper');
    }

    /**
     * GET /api/profil/show/(:num)
     * Mengambil data profil berdasarkan ID User (Tabel users_mhs)
     */
    public function show($id = null)
    {
        if (!$id) {
            return $this->fail('ID Mahasiswa wajib dilampirkan.', 400);
        }

        $db = \Config\Database::connect();
        
        // Membidik tabel yang benar yaitu 'users_mhs'
        $builder = $db->table('users_mhs');
        
        // Menyesuaikan kolom dengan struktur SQL (id_user, nama_user)
        $builder->select('users_mhs.id_user, users_mhs.no_hp, users_mhs.nim_mhs, users_mhs.kode_ut, users_mhs.img_mhs, users_mhs.nama_user, users_mhs.role, ut_daerah.nama_ut');
        $builder->join('ut_daerah', 'ut_daerah.kode_ut = users_mhs.kode_ut', 'left');
        $builder->where('users_mhs.id_user', $id);
        
        $user = $builder->get()->getRowArray();

        if (!$user) {
            return $this->failNotFound('Data pengguna tidak ditemukan di dalam pangkalan data.');
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $user
        ], 200);
    }

    /**
     * POST /api/profil/update/(:num)
     * Memperbarui data profil mahasiswa (Nama User, NIM, UPBJJ-UT)
     */
    public function update($id = null)
    {
        $userModel = new UsersMhsModel();
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        // Mengunci baris data berdasarkan id_user
        $userOld = $userModel->where('id_user', $id)->first();
        if (!$userOld) {
            return $this->failNotFound('Akun tidak ditemukan.');
        }

        // Menggunakan 'nama_user' (HURUF KAPITAL)
        $updateData = [
            'nama_user' => strtoupper(trim($data['nama_user'] ?? $userOld['nama_user'])),
            'nim_mhs'   => !empty($data['nim_mhs']) ? trim($data['nim_mhs']) : null,
            'kode_ut'   => !empty($data['kode_ut']) ? trim($data['kode_ut']) : null
        ];

        if ($updateData['nim_mhs'] !== null && !preg_match('/^[0-9]{9}$/', $updateData['nim_mhs'])) {
            return $this->fail('Format NIM tidak valid! Harus berupa persis 9 digit angka.');
        }

        if ($userModel->where('id_user', $id)->set($updateData)->update()) {
            
            // BOT TELEGRAM LOG: Catat Aktivitas Perubahan Profil Mahasiswa
            $msgLog = "📝 <b>PROFIL MAHASISWA DIPERBARUI</b>\n"
                    . "--------------------------------\n"
                    . "👤 Nama Baru : <b>" . esc($updateData['nama_user']) . "</b>\n"
                    . "📱 No. HP : <code>" . esc($userOld['no_hp']) . "</code>\n"
                    . "🆔 NIM : <code>" . ($updateData['nim_mhs'] ?? 'Belum Diisi') . "</code>\n"
                    . "📍 UPBJJ-UT : <code>" . ($updateData['kode_ut'] ?? 'Belum Memilih') . "</code>\n"
                    . "🕒 Waktu : " . date('d-m-Y H:i:s') . " WIB\n"
                    . "--------------------------------";
            send_telegram_notif($msgLog);

            return $this->respond([
                'status'  => true,
                'message' => 'Profil Anda berhasil diperbarui'
            ], 200);
        }

        return $this->failServerError('Gagal memperbarui profil di server.');
    }

    /**
     * POST /api/profil/uploadFoto/(:num)
     * Unggah pas foto dengan penamaan format kustom: {no_hp}_{nama_user}.webp
     */
    public function uploadFoto($id = null)
    {
        $userModel = new UsersMhsModel();
        
        // Memastikan pencarian menggunakan id_user
        $user = $userModel->where('id_user', $id)->first();

        if (!$user) {
            return $this->failNotFound('Akun mahasiswa tidak ditemukan.');
        }

        $fileGambar = $this->request->getFile('foto_profil');
        
        if (!$fileGambar || !$fileGambar->isValid()) {
            return $this->fail('Berkas gambar tidak ditemukan atau rusak.', 400);
        }

        $supportedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!in_array($fileGambar->getMimeType(), $supportedTypes)) {
            return $this->fail('Sistem hanya menerima file gambar berformat JPG, PNG, atau WEBP.', 400);
        }

        $targetDir = WRITEPATH . 'PROFIL/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Format Nama File Kustom {no_hp}_{nama_user}.webp
        // Membersihkan spasi dan karakter khusus dari nama agar aman menjadi nama file / URL
        $cleanName = preg_replace('/[^A-Za-z0-9]/', '_', strtolower(trim($user['nama_user'])));
        $namaFileBaru = $user['no_hp'] . '_' . $cleanName . '.webp';

        // Pindahkan file ke writable folder (opsi "true" akan menimpa file lama otomatis jika nama sama)
        if ($fileGambar->move($targetDir, $namaFileBaru, true)) {
            
            $pathFisikGambar = $targetDir . $namaFileBaru;

            // =========================================================================
            // AUTOMATIC SQUARE CROP VIA CODEIGNITER IMAGE SERVICES
            // =========================================================================
            $imageServices = \Config\Services::image();
            
            list($width, $height) = getimagesize($pathFisikGambar);
            $squareSize = min($width, $height);
            
            // Memotong gambar menjadi Square (1:1) dan menyimpan ulang dengan kompresi WebP 85%
            $imageServices->withFile($pathFisikGambar)
                          ->fit($squareSize, $squareSize, 'center')
                          ->save($pathFisikGambar, 85); 

            // Update database dengan nama file baru (jika sebelumnya masih mahasiswa.webp)
            $userModel->where('id_user', $id)->set(['img_mhs' => $namaFileBaru])->update();

            // SINKRONISASI NOTIFIKASI BOT TELEGRAM
            $msgUpload = "📸 <b>FOTO PROFIL DIPERBARUI</b>\n"
                       . "--------------------------------\n"
                       . "👤 User: <b>" . esc($user['nama_user']) . "</b>\n"
                       . "🖼️ File: <code>" . $namaFileBaru . "</code>\n"
                       . "🕒 Waktu: " . date('d-m-Y H:i:s') . " WIB";
            send_telegram_notif($msgUpload);

            return $this->respond([
                'status'   => true,
                'message'  => 'Pas foto profil berhasil diunggah.',
                'img_mhs'  => $namaFileBaru
            ], 200);
        }

        return $this->failServerError('Gagal memproses unggahan foto ke direktori fisik server.');
    }
}
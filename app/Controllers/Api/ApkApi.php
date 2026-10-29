<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

/**
 * ApkApi
 * RESTful API Controller untuk melacak dan mengelola pengunduhan aplikasi Android (APK)
 */
class ApkApi extends ResourceController
{
    protected $format = 'json';

    /**
     * Mengambil total angka unduhan untuk ditampilkan di layar
     * Method: GET /api/apk/count
     */
    public function count()
    {
        $db = \Config\Database::connect();
        $total = $db->table('download_logs')->countAllResults();
        
        return $this->respond([
            'status' => 'success', 
            'total'  => $total
        ], 200);
    }

    /**
     * Mencatat log ke database dan memberikan URL file APK
     * Method: POST /api/apk/download
     * PERBAIKAN: Nama fungsi diubah dari logAndDownload() menjadi download() agar selaras dengan Routes.php
     */
    public function download()
    {
        $db = \Config\Database::connect();
        $json = $this->request->getJSON();

        $data = [
            // Jika belum login, id_user bernilai null
            'id_user'    => !empty($json->id_user) ? $json->id_user : null,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString()
        ];

        if ($db->table('download_logs')->insert($data)) {
            return $this->respond([
                'status'  => 'success',
                // Merujuk langsung ke folder public/apk/ sesuai direktori Anda
                'url'     => '/apk/TesFormatif.apk' 
            ], 200);
        }

        return $this->failServerError('Gagal mencatat log unduhan');
    }
}
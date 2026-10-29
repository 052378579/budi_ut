<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * AuthApiFilter
 * Filter khusus untuk melindungi rute API (RESTful JSON) bagi Mahasiswa.
 * Memastikan bahwa request hanya bisa diakses oleh pengguna yang memiliki sesi valid,
 * serta memvalidasi sinkronisasi identitas antara server dan klien (Vue.js).
 */
class AuthApiFilter implements FilterInterface
{
    /**
     * Fungsi ini dieksekusi SEBELUM request mencapai Controller API.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Inisialisasi layanan session CodeIgniter
        $session = session();
        
        // 1. Validasi Sesi Utama Server (Apakah pengguna sudah login?)
        if (!$session->has('id_user')) {
            // Mengembalikan format JSON baku agar mudah ditangkap oleh fetch API Vue.js
            return Services::response()
                ->setJSON([
                    'status'  => 'unauthorized',
                    'message' => 'Sesi belajar Anda telah berakhir. Silakan masuk kembali dari beranda.'
                ])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
        // 2. Cross-Validation (Sinkronisasi Keamanan dengan Vue.js localStorage)
        // Hanya ekstrak payload JSON jika tipe kontennya adalah application/json
        $jsonPayload = null;
        if (strpos($request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            $jsonPayload = $request->getJSON(true);
        }
        
        // Jika klien secara eksplisit mengirimkan parameter id_user (misal saat submit skor/favorit),
        // pastikan id_user yang dikirim COCOK dengan id_user yang tercatat di sesi server.
        if ($jsonPayload && isset($jsonPayload['id_user'])) {
            if ((int)$jsonPayload['id_user'] !== (int)$session->get('id_user')) {
                return Services::response()
                    ->setJSON([
                        'status'  => 'forbidden',
                        'message' => 'Anomali identitas terdeteksi. Akses diblokir demi keamanan.'
                    ])
                    ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
            }
        }
    }

    /**
     * Fungsi ini dieksekusi SESUDAH Controller mengembalikan response.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) 
    {
        // Dibiarkan kosong karena response JSON sudah dikelola sepenuhnya oleh Controller
    }
}
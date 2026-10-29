<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * AdminApiFilter
 * Filter khusus untuk melindungi rute API (RESTful JSON) khusus Administrator.
 */
class AdminApiFilter implements FilterInterface
{
    /**
     * Fungsi ini dieksekusi SEBELUM request mencapai Controller API.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Inisialisasi layanan session
        $session = session();
        
        // Proteksi berlapis: Validasi sesi server untuk peran Super Admin (id_user = 1)
        if (!$session->has('id_user') || (int)$session->get('id_user') !== 1) {
            
            // Mengembalikan format JSON baku agar mudah ditangkap oleh fetch API Vue.js
            return Services::response()
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Sesi tidak valid. Hak akses Administrator ditolak.'
                ])
                ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
        }
    }

    /**
     * Fungsi ini dieksekusi SESUDAH Controller mengembalikan response.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) 
    {
        // Dibiarkan kosong karena tidak ada manipulasi response lanjutan yang diperlukan
    }
}
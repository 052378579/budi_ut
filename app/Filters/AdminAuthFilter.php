<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AdminAuthFilter
 * Filter khusus untuk melindungi rute Web (halaman HTML) khusus Administrator.
 * Jika pengguna yang mengakses bukan Admin, mereka akan dialihkan (redirect) 
 * kembali ke halaman utama.
 */
class AdminAuthFilter implements FilterInterface
{
    /**
     * Fungsi ini dieksekusi SEBELUM request mencapai Controller.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Inisialisasi layanan session
        $session = session();
        
        // Cek apakah user belum login ATAU bukan Admin (id_user bukan 1)
        if (!$session->has('id_user') || (int)$session->get('id_user') !== 1) {
            
            // Hancurkan sesi yang tidak valid jika ada untuk mencegah kebocoran data
            if ($session->has('id_user') && (int)$session->get('id_user') !== 1) {
                $session->destroy();
            }
            
            // Arahkan kembali ke halaman Beranda (/) dengan membawa pesan error (Flashdata)
            return redirect()->to('/')->with('error', 'Akses ditolak! Anda tidak memiliki izin Administrator.');
        }
    }

    /**
     * Fungsi ini dieksekusi SESUDAH Controller mengembalikan response.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) 
    {
        // Dibiarkan kosong karena tidak ada manipulasi response (HTML) setelah halaman berhasil dimuat
    }
}
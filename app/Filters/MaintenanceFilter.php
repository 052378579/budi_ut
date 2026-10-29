<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MaintenanceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Jaminan Super Admin: Jika yang login adalah id_user = 1, jangan pernah blokir
        $session = session();
        if ($session->has('id_user') && (int)$session->get('id_user') === 1) {
            return;
        }

        $db = \Config\Database::connect();
        
        // 2. Prioritas Utama: Cek status maintenance dari database
        $mSetting = $db->table('pengaturan_sistem')->where('setting_key', 'maintenance_mode')->get()->getRow();
        if ($mSetting && $mSetting->setting_value === '1') {
            return \Config\Services::response()
                ->setStatusCode(503) // 503 Service Unavailable
                ->setBody(view('errors/html/production'));
        }

        // 3. Prioritas Kedua: Cek status coming soon dari database
        $csSetting = $db->table('pengaturan_sistem')->where('setting_key', 'coming_soon_mode')->get()->getRow();
        if ($csSetting && $csSetting->setting_value === '1') {
            return \Config\Services::response()
                ->setStatusCode(200)
                ->setBody(view('errors/html/coming_soon'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi khusus setelah response
    }
}

<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class SaranPublikController extends Controller
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        // Ambil data saran terbaru, urutkan dari yang terbaru
        $saranList = $db->table('saran_mhs')
            ->select('saran_mhs.*, users_mhs.nama_user')
            ->join('users_mhs', 'users_mhs.id_user = saran_mhs.id_user', 'left')
            ->orderBy('saran_mhs.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Sensor nama untuk privasi (Contoh: "Budi Santoso" -> "Budi ***")
        foreach ($saranList as &$s) {
            $namaAsli = trim((string)$s['nama_user']);
            if (empty($namaAsli)) {
                $s['nama_user'] = 'Anonim';
            } else {
                $parts = explode(' ', $namaAsli);
                if (count($parts) > 1) {
                    $s['nama_user'] = $parts[0] . ' ***'; // Hanya tampilkan kata pertama
                } else {
                    $s['nama_user'] = substr($namaAsli, 0, 2) . '***';
                }
            }
            // Sanitasi isi pesan agar aman dari XSS
            $s['isi_saran'] = esc($s['isi_saran']);
            
            // Waktu untuk rendering Vue (opsional, Vue bisa memformat sendiri)
            $s['waktu_format'] = date('d M Y', strtotime($s['created_at']));
        }

        $data = [
            'title'     => 'Papan Saran Publik',
            'saranList' => json_encode($saranList) // Dioper ke Vue.js
        ];

        return view('halaman/saran_publik', $data);
    }
}

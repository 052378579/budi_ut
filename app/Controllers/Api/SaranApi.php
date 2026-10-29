<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class SaranApi extends ResourceController
{
    public function create()
    {
        $db = \Config\Database::connect();
        $json = $this->request->getJSON();

        // Validasi kelengkapan data (menggunakan id_user sesuai skema relasi tabel yang baru)
        if (!$json || empty($json->id_user) || empty($json->isi_saran)) {
            return $this->fail('Data masukan tidak lengkap atau kosong.', 400);
        }

        // Menangkap informasi OS/Perangkat yang digunakan Mahasiswa
        $agent = $this->request->getUserAgent();
        $perangkat = $agent->getPlatform() . ' - ' . $agent->getBrowser() . ' ' . $agent->getVersion();
        if ($agent->isMobile()) {
            $perangkat .= ' (' . $agent->getMobile() . ')';
        }

        // Persiapan paket data untuk disimpan ke dalam database
        $data = [
            'id_user'        => $json->id_user,
            'kategori'       => $json->kategori ?? 'lainnya',
            'isi_saran'      => esc($json->isi_saran), // Mencegah serangan XSS
            'perangkat_info' => $perangkat
        ];

        // Eksekusi insert langsung menggunakan Query Builder CI4 ke tabel saran_mhs
        if ($db->table('saran_mhs')->insert($data)) {
            return $this->respondCreated([
                'status'  => 'success',
                'message' => 'Saran masukan berhasil disinkronisasikan ke database STB.'
            ]);
        }

        return $this->failServerError('Gagal menyimpan ke pangkalan data.');
    }
}
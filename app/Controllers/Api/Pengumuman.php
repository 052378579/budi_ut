<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PengumumanModel;

class Pengumuman extends ResourceController
{
    protected $format    = 'json';

    public function getAktif()
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        
        $builder = $db->table('pengumuman');
        $builder->where('tgl_mulai <=', $now);
        $builder->where('tgl_selesai >=', $now);
        $builder->orderBy('tgl_mulai', 'ASC');
        
        $data = $builder->get()->getResultArray();
        
        return $this->respond([
            'status' => 200,
            'message' => 'Success',
            'data' => $data
        ]);
    }
}

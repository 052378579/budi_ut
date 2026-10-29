<?php

namespace App\Models;

use CodeIgniter\Model;

class IpLokasi extends Model
{
    protected $table            = 'ip_lokasi';
    protected $primaryKey       = 'ip_address';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['ip_address', 'kota', 'provinsi', 'negara', 'created_at'];

    // Dates
    protected $useTimestamps = false;
}

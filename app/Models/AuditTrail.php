<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditTrail extends Model
{
    protected $table            = 'audit_trails';
    protected $primaryKey       = 'id_audit';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_user', 'aksi', 'detail_aksi', 'ip_address', 'created_at'];

    // Dates
    protected $useTimestamps = false;
    
    /**
     * Mendapatkan statistik Top 10 Kota Terbanyak
     */
    public function getTopCities($limit = 10)
    {
        return $this->db->table('audit_trails a')
            ->select('loc.kota, COUNT(a.id_audit) as total')
            ->join('ip_lokasi loc', 'a.ip_address = loc.ip_address')
            ->whereIn('a.aksi', ['LOGIN', 'REGISTER'])
            ->where('loc.kota IS NOT NULL')
            ->groupBy('loc.kota')
            ->orderBy('total', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}

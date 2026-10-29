<?php
namespace App\Models;
use CodeIgniter\Model;

class PaketPremiumModel extends Model
{
    protected $table = 'paket_premium';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama_paket', 'nominal', 'deskripsi', 'is_active'];
    protected $useTimestamps = true;
}

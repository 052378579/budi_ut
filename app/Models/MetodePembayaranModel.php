<?php
namespace App\Models;
use CodeIgniter\Model;

class MetodePembayaranModel extends Model
{
    protected $table = 'metode_pembayaran';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama_bank', 'nomor_rekening', 'atas_nama', 'logo_icon', 'is_active'];
    protected $useTimestamps = true;
}

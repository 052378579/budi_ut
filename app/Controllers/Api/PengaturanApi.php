<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class PengaturanApi extends ResourceController
{
    use ResponseTrait;

    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Mengambil status maintenance mode & coming soon mode
     */
    public function getMaintenanceStatus()
    {
        // 1. Status Maintenance
        $mSetting = $this->db->table('pengaturan_sistem')->where('setting_key', 'maintenance_mode')->get()->getRow();
        $mStatus = $mSetting ? ($mSetting->setting_value === '1') : false;

        // 2. Status Coming Soon (dengan auto-seed jika belum ada di tabel)
        $csSetting = $this->db->table('pengaturan_sistem')->where('setting_key', 'coming_soon_mode')->get()->getRow();
        if (!$csSetting) {
            $this->db->table('pengaturan_sistem')->insert([
                'setting_key'   => 'coming_soon_mode',
                'setting_value' => '0'
            ]);
            $csStatus = false;
        } else {
            $csStatus = ($csSetting->setting_value === '1');
        }

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'maintenance_mode' => $mStatus,
                'coming_soon_mode' => $csStatus
            ]
        ]);
    }

    /**
     * Memperbarui status maintenance mode
     */
    public function toggleMaintenance()
    {
        $json = $this->request->getJSON();
        
        if (!isset($json->status)) {
            return $this->fail('Status tidak disertakan', 400);
        }

        $val = $json->status ? '1' : '0';

        $this->db->table('pengaturan_sistem')
            ->where('setting_key', 'maintenance_mode')
            ->update(['setting_value' => $val]);

        // Jika maintenance diaktifkan, otomatis nonaktifkan coming_soon_mode
        if ($val === '1') {
            $this->db->table('pengaturan_sistem')
                ->where('setting_key', 'coming_soon_mode')
                ->update(['setting_value' => '0']);
        }

        return $this->respond([
            'status'  => 'success',
            'message' => 'Status Maintenance berhasil diperbarui menjadi ' . ($val === '1' ? 'AKTIF' : 'NON-AKTIF')
        ]);
    }

    /**
     * Memperbarui status coming soon mode
     */
    public function toggleComingSoon()
    {
        $json = $this->request->getJSON();
        
        if (!isset($json->status)) {
            return $this->fail('Status tidak disertakan', 400);
        }

        $val = $json->status ? '1' : '0';

        // Update atau Insert jika belum ada
        $exists = $this->db->table('pengaturan_sistem')->where('setting_key', 'coming_soon_mode')->countAllResults();
        if ($exists > 0) {
            $this->db->table('pengaturan_sistem')
                ->where('setting_key', 'coming_soon_mode')
                ->update(['setting_value' => $val]);
        } else {
            $this->db->table('pengaturan_sistem')->insert([
                'setting_key'   => 'coming_soon_mode',
                'setting_value' => $val
            ]);
        }

        // Jika coming soon diaktifkan, otomatis nonaktifkan maintenance mode
        if ($val === '1') {
            $this->db->table('pengaturan_sistem')
                ->where('setting_key', 'maintenance_mode')
                ->update(['setting_value' => '0']);
        }

        return $this->respond([
            'status'  => 'success',
            'message' => 'Status Coming Soon berhasil diperbarui menjadi ' . ($val === '1' ? 'AKTIF' : 'NON-AKTIF')
        ]);
    }
}

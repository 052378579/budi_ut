<?php
namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\MetodePembayaranModel;
use App\Models\PaketPremiumModel;

class DonasiApi extends ResourceController
{
    protected $format = 'json';

    // GET /api/donasi/data
    public function getPublicData()
    {
        $metodeModel = new MetodePembayaranModel();
        $paketModel = new PaketPremiumModel();
        $db = \Config\Database::connect();
        
        $waSetting = $db->table('pengaturan_sistem')->where('setting_key', 'whatsapp_admin')->get()->getRowArray();
        
        return $this->respond([
            'metode' => $metodeModel->where('is_active', 1)->findAll(),
            'paket' => $paketModel->where('is_active', 1)->findAll(),
            'whatsapp' => $waSetting ? $waSetting['setting_value'] : '081313428000'
        ]);
    }

    // CRUD METODE PEMBAYARAN
    public function getMetode()
    {
        $model = new MetodePembayaranModel();
        return $this->respond($model->findAll());
    }

    public function createMetode()
    {
        $model = new MetodePembayaranModel();
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        
        if ($model->insert($data)) {
            return $this->respondCreated(['status' => true, 'message' => 'Metode berhasil ditambahkan.']);
        }
        return $this->failValidationErrors($model->errors());
    }

    public function updateMetode($id = null)
    {
        $model = new MetodePembayaranModel();
        $data = $this->request->getJSON(true) ?? $this->request->getRawInput();
        
        if ($model->update($id, $data)) {
            return $this->respond(['status' => true, 'message' => 'Metode berhasil diperbarui.']);
        }
        return $this->failValidationErrors($model->errors());
    }

    public function deleteMetode($id = null)
    {
        $model = new MetodePembayaranModel();
        if ($model->delete($id)) {
            return $this->respondDeleted(['status' => true, 'message' => 'Metode berhasil dihapus.']);
        }
        return $this->failNotFound('Metode tidak ditemukan.');
    }

    // CRUD PAKET PREMIUM
    public function getPaket()
    {
        $model = new PaketPremiumModel();
        return $this->respond($model->findAll());
    }

    public function createPaket()
    {
        $model = new PaketPremiumModel();
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        
        if ($model->insert($data)) {
            return $this->respondCreated(['status' => true, 'message' => 'Paket berhasil ditambahkan.']);
        }
        return $this->failValidationErrors($model->errors());
    }

    public function updatePaket($id = null)
    {
        $model = new PaketPremiumModel();
        $data = $this->request->getJSON(true) ?? $this->request->getRawInput();
        
        if ($model->update($id, $data)) {
            return $this->respond(['status' => true, 'message' => 'Paket berhasil diperbarui.']);
        }
        return $this->failValidationErrors($model->errors());
    }

    public function deletePaket($id = null)
    {
        $model = new PaketPremiumModel();
        if ($model->delete($id)) {
            return $this->respondDeleted(['status' => true, 'message' => 'Paket berhasil dihapus.']);
        }
        return $this->failNotFound('Paket tidak ditemukan.');
    }
}

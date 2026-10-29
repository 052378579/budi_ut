<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\AuditTrail;

class StatistikController extends BaseController
{
    use ResponseTrait;

    public function topCities()
    {
        $auditModel = new AuditTrail();
        $data = $auditModel->getTopCities(10);
        
        return $this->respond([
            'status' => 'success',
            'data' => $data
        ]);
    }
}

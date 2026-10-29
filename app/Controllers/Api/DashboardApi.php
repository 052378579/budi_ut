<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UsersMhsModel;

class DashboardApi extends ResourceController
{
    /**
     * Endpoint API untuk mengambil statistik pendaftar baru
     * URL: GET /api/dashboard/chart-pendaftar
     */
    public function chartPendaftar()
    {
        $filter = $this->request->getGet('filter') ?? '7';
        $usersModel = new UsersMhsModel();
        
        $data = $usersModel->getStatistikPendaftar($filter);
        
        // Memformat data untuk keperluan Chart.js
        $labels = [];
        $totals = [];
        
        foreach ($data as $row) {
            $labels[] = date('d M Y', strtotime($row['tanggal']));
            $totals[] = (int) $row['total'];
        }
        
        return $this->respond([
            'status' => 200,
            'message' => 'Data statistik berhasil diambil',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Pendaftar Baru',
                        'data' => $totals,
                        'backgroundColor' => 'rgba(13, 110, 253, 0.2)', // Primary Bootstrap
                        'borderColor' => 'rgba(13, 110, 253, 1)',
                        'borderWidth' => 2,
                        'fill' => true,
                        'tension' => 0.4
                    ]
                ]
            ]
        ]);
    }
}

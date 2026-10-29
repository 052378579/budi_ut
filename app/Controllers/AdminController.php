<?php

namespace App\Controllers;

use App\Controllers\BaseController;

/**
 * AdminController
 * * Bertugas khusus untuk merender (menampilkan) file View HTML antarmuka Admin.
 * Karena aplikasi ini mengimplementasikan Vue.js (CDN) dan Axios pada sisi Frontend, 
 * controller ini tidak melakukan manipulasi data atau kueri database secara langsung, 
 * melainkan hanya menyajikan struktur halaman (View) ke browser pengguna.
 * * Catatan Keamanan: Proteksi hak akses tingkat tinggi untuk grup 'admin' dijalankan 
 * secara otomatis oleh komponen 'adminAuth' Filter melalui konfigurasi di Routes.php.
 */
class AdminController extends BaseController
{
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // KUNCI KEAMANAN MUTLAK: Hanya id_user = 1 yang berhak mengakses seluruh antarmuka Admin
        $session = session();
        if (!$session->has('id_user') || (int)$session->get('id_user') !== 1) {
            header('Location: /');
            exit;
        }
    }
    private function getOnlineUsersCount()
    {
        $db = \Config\Database::connect();
        return $db->table('user_sessions')
            ->where('last_activity >=', date('Y-m-d H:i:s', strtotime('-60 minutes')))
            ->where('last_activity <=', date('Y-m-d H:i:s', strtotime('+5 minutes')))
            ->countAllResults();
    }

    /**
     * Halaman Dasbor Utama Administrator
     * Menyajikan ringkasan statistik (metrik) dan saran terbaru dari mahasiswa.
     * URL: localhost/admin atau localhost/admin/dashboard
     * * @return string Rendered HTML View
     */
    public function dashboard()
    {
        $db = \Config\Database::connect();

        // 1. Menghitung Metrik Utama
        $totalMahasiswa = $db->table('users_mhs')->where('role', 'mahasiswa')->countAllResults();
        $totalMk        = $db->table('mata_kuliah')->countAllResults();
        $totalUjian     = $db->table('skor_mhs')->countAllResults();
        $totalUnduhan   = $db->table('download_logs')->countAllResults();

        $onlineUsersCount = $this->getOnlineUsersCount();

        // 2. Mengambil 5 Saran/Masukan terbaru yang belum dibaca
        $saranTerbaru = $db->table('saran_mhs')
            ->select('saran_mhs.*, users_mhs.nama_user, users_mhs.no_hp')
            ->join('users_mhs', 'users_mhs.id_user = saran_mhs.id_user', 'left')
            ->where('status_baca', 0)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        // Chart 1: Tren Aktivitas Harian (7 Hari Terakhir)
        $trenAktivitas = $db->query("
            SELECT DATE(waktu_selesai) as tgl, COUNT(id_skor) as total 
            FROM skor_mhs 
            WHERE waktu_selesai >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(waktu_selesai)
            ORDER BY tgl ASC
        ")->getResultArray();
        $chartTrenLabels = []; $chartTrenData = [];
        foreach ($trenAktivitas as $row) {
            $chartTrenLabels[] = $row['tgl'];
            $chartTrenData[] = (int)$row['total'];
        }

        // Chart 2: Rata-Rata Skor per MK (Limit 10)
        $avgSkor = $db->query("
            SELECT mata_kuliah.kode_mk, AVG(skor_mhs.skor) as rata_rata
            FROM skor_mhs
            JOIN mata_kuliah ON mata_kuliah.id_mk = skor_mhs.id_mk
            GROUP BY mata_kuliah.id_mk
            ORDER BY rata_rata DESC
            LIMIT 10
        ")->getResultArray();
        $chartAvgLabels = []; $chartAvgData = [];
        foreach ($avgSkor as $row) {
            $chartAvgLabels[] = $row['kode_mk'];
            $chartAvgData[] = round($row['rata_rata'], 2);
        }

        // Chart 3: Proporsi Jenis Ujian
        $proporsiUjian = $db->query("
            SELECT jenis_ujian, COUNT(id_skor) as total 
            FROM skor_mhs 
            GROUP BY jenis_ujian
        ")->getResultArray();
        $chartPropLabels = []; $chartPropData = [];
        foreach ($proporsiUjian as $row) {
            $chartPropLabels[] = strtoupper($row['jenis_ujian']);
            $chartPropData[] = (int)$row['total'];
        }

        // Chart 4: Top 5 MK Terfavorit
        $topFavorit = $db->table('favorit_mhs')
            ->select('mata_kuliah.kode_mk, COUNT(favorit_mhs.id_favorit) as total')
            ->join('mata_kuliah', 'mata_kuliah.id_mk = favorit_mhs.id_mk')
            ->groupBy('mata_kuliah.id_mk')
            ->orderBy('total', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
        $chartFavLabels = []; $chartFavData = [];
        foreach ($topFavorit as $fav) {
            $chartFavLabels[] = $fav['kode_mk'];
            $chartFavData[]   = (int)$fav['total'];
        }

        // Chart 5: Sebaran Rentang Nilai
        $sebaranNilai = $db->query("
            SELECT 
                SUM(CASE WHEN skor BETWEEN 0 AND 20 THEN 1 ELSE 0 END) as 'rng1',
                SUM(CASE WHEN skor BETWEEN 21 AND 40 THEN 1 ELSE 0 END) as 'rng2',
                SUM(CASE WHEN skor BETWEEN 41 AND 60 THEN 1 ELSE 0 END) as 'rng3',
                SUM(CASE WHEN skor BETWEEN 61 AND 80 THEN 1 ELSE 0 END) as 'rng4',
                SUM(CASE WHEN skor BETWEEN 81 AND 100 THEN 1 ELSE 0 END) as 'rng5'
            FROM skor_mhs
        ")->getRowArray();
        $chartSebaranLabels = ['0-20', '21-40', '41-60', '61-80', '81-100'];
        $chartSebaranData = [
            (int)$sebaranNilai['rng1'],
            (int)$sebaranNilai['rng2'],
            (int)$sebaranNilai['rng3'],
            (int)$sebaranNilai['rng4'],
            (int)$sebaranNilai['rng5']
        ];

        // Siapkan paket data untuk dikirim ke View
        $data = [
            'title'          => 'Dashboard Admin',
            'onlineUsersCount'=> $onlineUsersCount,
            'totalMahasiswa' => $totalMahasiswa,
            'totalMk'        => $totalMk,
            'totalUjian'     => $totalUjian,
            'totalUnduhan'   => $totalUnduhan,
            'saranTerbaru'   => $saranTerbaru,
            // Data Grafik
            'chartTrenLabels'    => json_encode($chartTrenLabels),
            'chartTrenData'      => json_encode($chartTrenData),
            'chartPropLabels'    => json_encode($chartPropLabels),
            'chartPropData'      => json_encode($chartPropData),
            'chartAvgLabels'     => json_encode($chartAvgLabels),
            'chartAvgData'       => json_encode($chartAvgData),
            'chartFavLabels'     => json_encode($chartFavLabels),
            'chartFavData'       => json_encode($chartFavData),
            'chartSebaranLabels' => json_encode($chartSebaranLabels),
            'chartSebaranData'   => json_encode($chartSebaranData),
            'navThemeColor'  => 'bg-dark' 
        ];

        return view('admin/dashboard', $data); // Pastikan path view sesuai (admin/dashboard atau admin_dashboard)
    }

    /**
     * Halaman Kelola Mata Kuliah
     * Menyajikan antarmuka bagi Admin untuk menambah, memperbarui, dan menghapus metadata mata kuliah.
     * URL: localhost/admin/matakuliah
     * * @return string Rendered HTML View
     */
    public function matakuliah()
    {
        $data = [
            'title' => 'Mata Kuliah',
            'onlineUsersCount' => $this->getOnlineUsersCount()
        ];
        
        return view('admin/mata_kuliah', $data);
    }

    /**
     * Halaman Dasbor Riwayat Skor
     * Menyajikan visualisasi rekap transaksional nilai kuis formatif dan simulasi UAS mahasiswa.
     * URL: localhost/admin/skor
     * * @return string Rendered HTML View
     */
    public function skor()
    {
        $data = [
            'title' => 'Riwayat',
            'onlineUsersCount' => $this->getOnlineUsersCount()
        ];
        
        return view('admin/skor_view', $data);
    }

    /**
     * Halaman Kelola Data Mahasiswa (Users)
     * Menyajikan antarmuka manajemen akun pengguna beserta statistik agregat total favorit mata kuliah.
     * URL: localhost/admin/users
     * * @return string Rendered HTML View
     */
    public function users()
    {
        $data = [
            'title' => 'Users',
            'onlineUsersCount' => $this->getOnlineUsersCount()
        ];
        
        return view('admin/users_view', $data);
    }

    /**
     * Halaman Kelola Pengumuman (BARU)
     * Menyajikan antarmuka manajemen CRUD untuk maklumat/pengumuman akademik.
     * URL: localhost/admin/pengumuman
     * * @return string Rendered HTML View
     */
    public function pengumumanView()
    {
        $data = [
            'title' => 'Pengumuman',
            'onlineUsersCount' => $this->getOnlineUsersCount()
        ];
        
        return view('admin/pengumuman', $data);
    }

    /**
     * Halaman Log Sistem Keamanan
     * Menyajikan tabel Audit Trails (Aktivitas) dan User Sessions (Sesi Aktif).
     * URL: localhost/admin/logs
     * * @return string Rendered HTML View
     */
    public function logs()
    {
        $db = \Config\Database::connect();

        // 1. Mengambil data Audit Trails (Dibatasi 200 aktivitas terakhir agar query ringan)
        $auditTrails = $db->table('audit_trails')
            ->select('audit_trails.*, users_mhs.nama_user, users_mhs.no_hp, users_mhs.role')
            ->join('users_mhs', 'users_mhs.id_user = audit_trails.id_user', 'left')
            ->orderBy('created_at', 'DESC')
            ->limit(200)
            ->get()
            ->getResultArray();

        // 2. Mengambil data Sesi Pengguna Aktif (Login)
        $userSessions = $db->table('user_sessions')
            ->select('user_sessions.*, users_mhs.nama_user, users_mhs.no_hp, users_mhs.role')
            ->join('users_mhs', 'users_mhs.id_user = user_sessions.id_user', 'left')
            ->orderBy('last_activity', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title'        => 'Log Sistem Keamanan',
            'auditTrails'  => json_encode($auditTrails),
            'userSessions' => json_encode($userSessions),
            'navThemeColor'=> 'bg-dark',
            'onlineUsersCount' => $this->getOnlineUsersCount()
        ];
        
        return view('admin/logs_view', $data);
    }

    /**
     * Halaman Statistik Top 10 Kota Terbanyak
     * Menyajikan grafik Chart.js persebaran kota mahasiswa
     * URL: localhost/admin/top-cities
     * * @return string Rendered HTML View
     */
    public function topCities()
    {
        $data = [
            'title' => 'Statistik Top 10 Kota',
            'onlineUsersCount' => $this->getOnlineUsersCount()
        ];
        
        return view('statistics/top_cities', $data);
    }

    /**
     * Halaman Kelola Saran & Masukan Mahasiswa (BARU)
     * Menyajikan tabel saran, fitur, bug, dan masukan dari pengguna aplikasi.
     * URL: localhost/admin/saran
     * * @return string Rendered HTML View
     */
    public function saran()
    {
        $db = \Config\Database::connect();

        $saranList = $db->table('saran_mhs')
            ->select('saran_mhs.*, users_mhs.nama_user, users_mhs.no_hp')
            ->join('users_mhs', 'users_mhs.id_user = saran_mhs.id_user', 'left')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title'     => 'Saran & Masukan',
            'saranList' => json_encode($saranList),
            'onlineUsersCount' => $this->getOnlineUsersCount()
        ];
        
        return view('admin/saran_view', $data);
    }
}
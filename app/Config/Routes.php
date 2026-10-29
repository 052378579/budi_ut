<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 * Konfigurasi Perutean (Routing) Utama Proyek UT Tes Formatif
 */

// =============================================================================================
// BAGIAN A: RUTE WEB / NON-RESTFUL API
// Bertugas merender antarmuka pengguna (Views HTML + Vue.js).
// Berkomunikasi langsung dengan browser pengguna.
// =============================================================================================

// 1. Halaman Utama / Landing Page
$routes->get('/', 'Home::index');

// Rute Sementara untuk Menguji Halaman Maintenance (Error 500)
$routes->get('test-error', static function() {
    // Melemparkan kesalahan sengaja untuk memicu production.php
    throw new \Exception('Ini adalah simulasi error untuk memicu halaman Maintenance.');
});

// 2. Halaman Dasbor Mahasiswa, Peringkat, dan Profil
$routes->get('riwayat', 'SkorController::riwayatView');
$routes->get('favorit', 'FavoritController::index');

// Rute Web untuk merender halaman antarmuka Profil Saya
$routes->get('profil', 'ProfilController::index');

// PERBAIKAN: Mendaftarkan Rute Halaman Unduh APK
$routes->get('unduh', static function() {
    return view('unduh_apk', ['title' => 'Unduh Aplikasi Tes Formatif']);
});

// HALAMAN PENGUMUMAN FITUR BARU (COMING SOON)
$routes->get('coming-soon', static function() {
    return view('errors/html/coming_soon');
});

// HALAMAN LEGALITAS & SEO (Diperlukan oleh Google AdSense)
$routes->get('privacy-policy', static function() {
    return view('halaman/privacy', ['title' => 'Privacy Policy']);
});
$routes->get('donasi', static function() {
    return view('halaman/donasi', ['title' => 'Donasi']);
});
$routes->get('contact-us', static function() {
    return view('halaman/contact', ['title' => 'Contact Us']);
});
$routes->get('saran', 'SaranPublikController::index');
$routes->get('prestasi', 'SkorController::globalLeaderboardView');

$routes->get('peringkat/(:segment)/MODUL-(:num)', 'SkorController::leaderboardView/$1/$2');
$routes->get('peringkat-uas/(:segment)', 'SkorController::leaderboardUasView/$1');

// 3. Halaman Evaluasi Ujian Akhir Semester (UAS)
$routes->get('(:segment)/uas-simulasi', 'MkController::uasSimulasi/$1');
$routes->get('(:segment)/uas-hasil', 'MkController::uasHasil/$1');

// 4. Halaman Dasbor Administrator (Terproteksi Filter 'adminAuth')
$routes->group('admin', ['filter' => 'adminAuth'], static function($routes) {
    $routes->get('/', 'AdminController::dashboard');
    $routes->get('dashboard', 'AdminController::dashboard');
    $routes->get('matakuliah', 'AdminController::matakuliah');
    $routes->get('skor', 'AdminController::skor');
    $routes->get('users', 'AdminController::users'); 
    
    $routes->get('pengumuman', 'AdminController::pengumumanView'); 
    $routes->get('saran', 'AdminController::saran');
    $routes->get('logs', 'AdminController::logs');
    $routes->get('top-cities', 'AdminController::topCities');
    $routes->get('donasi', static function() {
        return view('admin/donasi', ['title' => 'Kelola Metode Pembayaran & Donasi']);
    });
});


// =============================================================================================
// BAGIAN B: RUTE RESTFUL API & STORAGE STREAMING
// Bertugas menangani transaksi data (JSON) dan manajemen aset fisik (Dokumen.php).
// Dikonsumsi oleh Axios/Fetch pada Vue.js di sisi klien. Semua URL diawali dengan "/api/"
// =============================================================================================

$routes->group('api', static function($routes) {
    
    // --- 1. OTENTIKASI ---
    $routes->post('auth/masuk', 'Api\AuthApi::masuk');
    $routes->post('auth/keluar', 'Api\AuthApi::keluar'); // Memicu Notifikasi Bot Telegram LOGOUT
    $routes->get('auth/validate', 'Api\AuthApi::validateSession');
    
    // --- 2. MANAJEMEN BERKAS STORAGE PENGGUNA (Dokumen.php) ---
    // Diposisikan di atas API MK agar segmentasi tidak bertabrakan
    $routes->get('dokumen/view/(:segment)', 'Dokumen::view/$1');
    $routes->get('dokumen/cover/(:segment)', 'Dokumen::cover/$1');
    // Secure Stream Pas Foto Profil Mahasiswa dipindahkan ke filter authApi untuk privasi
    // Berkas bank soal dipindahkan ke filter authApi untuk keamanan

    // --- 3. MASTER DATA MATA KULIAH (Publik) ---
    $routes->get('mk', 'Api\MkApi::index');
    $routes->get('mk/search', 'Api\MkApi::search');
    $routes->get('mk/show/(:segment)', 'Api\MkApi::show/$1');
    $routes->get('mk/image/(:segment)', 'Api\MkApi::renderImage/$1');
    $routes->get('mk/uas-questions/(:segment)', 'Api\MkApi::getUasQuestions/$1');

    // --- 4. MASTER DATA WILAYAH UPBJJ-UT ---
    // Menyediakan daftar 40 wilayah UT daerah dari database untuk drop-down select di views
    $routes->get('daerah/list', static function() {
        $db = \Config\Database::connect();
        $data = $db->table('ut_daerah')->orderBy('kode_ut', 'ASC')->get()->getResultArray();
        return response()->setJSON(['status' => 'success', 'data' => $data]);
    });

    // --- 5. PENGUMUMAN & APK (Publik) ---
    $routes->get('pengumuman/active', 'Api\PengumumanApi::getActive');
    $routes->get('apk/count', 'Api\ApkApi::count');
    $routes->post('apk/download', 'Api\ApkApi::download');

    // --- 6. API DONASI & PREMIUM (Publik) ---
    $routes->get('donasi/data', 'Api\DonasiApi::getPublicData');

    // ========================================================================
    // ENDPOINT TERPROTEKSI MAHASISWA (Membutuhkan Login / authApi Filter)
    // ========================================================================
    $routes->group('', ['filter' => 'authApi'], static function($routes) {
        
        // --- SECURE: Akses Bank Soal dan Profil Mahasiswa ---
        $routes->get('dokumen/soal/(:segment)/(:num)', 'Dokumen::soal/$1/$2');
        $routes->get('dokumen/profil/(:segment)', 'Dokumen::profil/$1');
        
        $routes->get('skor', 'Api\SkorApi::index');
        $routes->post('skor/create', 'Api\SkorApi::create');
        $routes->delete('skor/delete/(:num)', 'Api\SkorApi::delete/$1');
        $routes->get('skor/user/(:num)', 'Api\SkorApi::user/$1'); 
        $routes->get('skor/ranking/(:num)/(:num)', 'Api\SkorApi::ranking/$1/$2');

        // --- 7. MANAJEMEN MATA KULIAH FAVORIT ---
        $routes->post('favorit/toggle', 'Api\FavoritApi::toggleFavorite');
        $routes->get('favorit/ids/(:num)', 'Api\FavoritApi::showIds/$1');
        $routes->get('favorit/list/(:num)', 'Api\FavoritApi::index/$1');

        // --- 8. MANAJEMEN PROFIL MAHASISWA ---
        $routes->get('profil/show/(:num)', 'Api\ProfilApi::show/$1');
        $routes->post('profil/update/(:num)', 'Api\ProfilApi::update/$1'); 
        $routes->post('profil/uploadFoto/(:num)', 'Api\ProfilApi::uploadFoto/$1'); 
    });

    // ========================================================================
    // ENDPOINT TERPROTEKSI ADMINISTRATOR (Membutuhkan Login Admin / adminAuth Filter)
    // ========================================================================
    $routes->group('', ['filter' => 'adminAuth'], static function($routes) {
        
        // --- 9. CRUD MASTER DATA MATA KULIAH & UPLOAD BMP ---
        $routes->post('mk/create', 'Api\MkApi::create');
        $routes->put('mk/update/(:num)', 'Api\MkApi::update/$1');
        $routes->delete('mk/delete/(:num)', 'Api\MkApi::delete/$1');
        $routes->post('dokumen/uploadBmp/(:num)', 'Dokumen::uploadBmp/$1');

        // --- 10. MANAJEMEN PENGGUNA (Admin) ---
        $routes->get('users', 'Api\UsersApi::index');
        $routes->post('users/create', 'Api\UsersApi::create');
        $routes->delete('users/delete/(:num)', 'Api\UsersApi::delete/$1');
        $routes->put('users/premium/(:num)', 'Api\UsersApi::updatePremium/$1');

        // --- 11. MANAJEMEN PENGUMUMAN ---
        $routes->get('pengumuman', 'Api\PengumumanApi::index');
        $routes->post('pengumuman/create', 'Api\PengumumanApi::create');
        $routes->put('pengumuman/update/(:num)', 'Api\PengumumanApi::update/$1'); 
        $routes->delete('pengumuman/delete/(:num)', 'Api\PengumumanApi::delete/$1');

        // --- 12. DASHBOARD STATISTIK ---
        $routes->get('dashboard/chart-pendaftar', 'Api\DashboardApi::chartPendaftar');
        $routes->get('statistik/topCities', 'Api\StatistikController::topCities');

        // --- 13. MANAJEMEN DONASI / PREMIUM ---
        $routes->get('donasi/metode', 'Api\DonasiApi::getMetode');
        $routes->post('donasi/metode/create', 'Api\DonasiApi::createMetode');
        $routes->put('donasi/metode/update/(:num)', 'Api\DonasiApi::updateMetode/$1');
        $routes->delete('donasi/metode/delete/(:num)', 'Api\DonasiApi::deleteMetode/$1');

        $routes->get('donasi/paket', 'Api\DonasiApi::getPaket');
        $routes->post('donasi/paket/create', 'Api\DonasiApi::createPaket');
        $routes->put('donasi/paket/update/(:num)', 'Api\DonasiApi::updatePaket/$1');
        $routes->delete('donasi/paket/delete/(:num)', 'Api\DonasiApi::deletePaket/$1');

        // --- 14. PENGATURAN SISTEM ---
        $routes->get('pengaturan/maintenance', 'Api\PengaturanApi::getMaintenanceStatus');
        $routes->put('pengaturan/maintenance', 'Api\PengaturanApi::toggleMaintenance');
        $routes->put('pengaturan/coming-soon', 'Api\PengaturanApi::toggleComingSoon');
    });
});


// =============================================================================================
// BAGIAN C: RUTE DINAMIS (CATCH-ALL)
// PERINGATAN: Blok ini WAJIB berada di baris paling bawah aplikasi!
// Berfungsi menangkap URL dinamis untuk halaman daftar kuis dan lembar pengerjaan kuis.
// =============================================================================================

// 1. Halaman Beranda Internal (Daftar Modul)
// Contoh URL: localhost/STSI4201
$routes->get('(:segment)', 'MkController::courseIndex/$1');

// 2. Halaman Lembar Pelaksanaan Kuis Tes Formatif
// Contoh URL: localhost/STSI4201/MODUL-1
$routes->get('(:segment)/MODUL-(:num)', 'MkController::index/$1/$2');

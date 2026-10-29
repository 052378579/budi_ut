<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UsersMhsModel;

/**
 * AuthApi
 * * Menangani proses Otentikasi (Masuk) dan Pendaftaran Otomatis (Registrasi) 
 * untuk Mahasiswa serta akses login khusus Administrator.
 * Telah terintegrasi dengan Bot Telegram untuk pencatatan log aktivitas.
 */
class AuthApi extends ResourceController
{
    protected $format = 'json';

    public function __construct()
    {
        helper('telegram_helper');
    }

    /**
     * Endpoint: POST /api/auth/masuk
     * * Logika pintar yang membedakan antara login Administrator (dengan sandi)
     * dan login/daftar otomatis Mahasiswa (hanya No HP dan Nama).
     */
    public function masuk()
    {
        // 1. Inisialisasi Model Pengguna
        $userModel = new UsersMhsModel();
        
        $ipAddress = $this->request->getIPAddress();
        if ($ipAddress === '::1') $ipAddress = '127.0.0.1';

        // BACKGROUND PROCESS: GeoJS Fetch untuk menyimpan lokasi IP (berjalan setelah response dikirim ke client)
        register_shutdown_function(function() use ($ipAddress) {
            $db = \Config\Database::connect();
            if ($db->table('ip_lokasi')->where('ip_address', $ipAddress)->countAllResults() === 0) {
                if ($ipAddress === '127.0.0.1' || strpos($ipAddress, '192.168.') === 0 || strpos($ipAddress, '10.') === 0) {
                    $kota = 'Localhost / Jaringan Internal';
                    $provinsi = 'Local';
                    $negara = 'Local';
                } else {
                    $ch = curl_init("https://get.geojs.io/v1/ip/geo/{$ipAddress}.json");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                    $response = curl_exec($ch);
                    curl_close($ch);
                    
                    $kota = 'Lokasi tidak diketahui';
                    $provinsi = null;
                    $negara = null;

                    if ($response) {
                        $data = json_decode($response, true);
                        if ($data) {
                            $kotaTemp = (!empty($data['city']) && $data['city'] !== '-') ? $data['city'] : 
                                        ((!empty($data['region']) && $data['region'] !== '-') ? $data['region'] : null);
                            
                            if (!$kotaTemp && !empty($data['timezone'])) {
                                $tzParts = explode('/', $data['timezone']);
                                if (count($tzParts) > 1) {
                                    $kotaTemp = str_replace('_', ' ', end($tzParts));
                                }
                            }
                            
                            $kodeNegara = (!empty($data['country_code']) && $data['country_code'] !== '-') ? $data['country_code'] : 
                                          ((!empty($data['country']) && $data['country'] !== '-') ? $data['country'] : null);
                            
                            if ($kotaTemp && $kodeNegara) {
                                $kota = $kotaTemp . ', ' . $kodeNegara;
                            } elseif ($kodeNegara) {
                                $kota = $kodeNegara;
                            } elseif ($kotaTemp) {
                                $kota = $kotaTemp;
                            }
                            
                            $provinsi = $data['region'] ?? null;
                            $negara = $data['country'] ?? null;
                        }
                    }
                }
                
                $db->table('ip_lokasi')->ignore(true)->insert([
                    'ip_address' => $ipAddress,
                    'kota' => $kota,
                    'provinsi' => $provinsi,
                    'negara' => $negara,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        });

        
        // 2. Tangkap input JSON dari frontend Vue.js atau Fallback ke form POST biasa
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        // 3. Bersihkan karakter non-angka pada nomor Handphone sejak awal
        $noHp     = preg_replace('/[^0-9]/', '', $data['no_hp'] ?? '');
        // Nama user diubah menjadi HURUF KAPITAL (hanya dipakai jika mendaftar baru)
        $namaUser = strtoupper(trim($data['nama_user'] ?? '')); 
        $password = $data['password'] ?? null;

        // 4. Validasi Dasar Utama
        if (empty($noHp)) {
            return $this->fail('Nomor Handphone wajib diisi', 400);
        }

        // =========================================================================
        // SINKRONISASI REGEX VALIDASI SESUAI TRIGGER DATABASE (no_hp.sql)
        // Berlaku mutlak untuk semua skenario akses (Admin maupun Mahasiswa)
        // =========================================================================
        
        // Kriteria A: Panjang nomor 10 - 13 digit
        if (strlen($noHp) < 10 || strlen($noHp) > 13) {
            return $this->fail('Gagal: Nomor HP tidak Valid', 400);
        }

        // Kriteria B: Format awalan 08 (Format standar lokal Indonesia)
        if (!preg_match('/^08/', $noHp)) {
            return $this->fail('Gagal: Prefix nomor HP tidak dikenali atau tidak menggunakan format 08', 400);
        }

        // Kriteria C: Spam Repetitif (1 angka diulang 6x)
        if (preg_match('/([0-9])\1{5,}/', $noHp)) {
            return $this->fail('Gagal: Nomor HP Terdeteksi Spam', 400);
        }

        // Kriteria D: Spam Sekuensial (Angka berurutan 7 digit)
        if (preg_match('/(1234567|2345678|3456789|4567890)/', $noHp)) {
            return $this->fail('Gagal: Nomor HP Terdeteksi Spam', 400);
        }

        // =========================================================================
        // SKENARIO A: LOGIN ADMINISTRATOR (Super User)
        // =========================================================================
        // Jika payload JSON menyertakan 'password', maka asumsikan ini percobaan masuk Admin
        if (!empty($password)) {
             // Cari akun admin berdasarkan Nomor HP
             $adminUser = $userModel->where('no_hp', $noHp)
                                    ->where('role', 'admin')
                                    ->first();
             
             // Verifikasi keberadaan akun dan kecocokan hash password
             if ($adminUser && password_verify($password, $adminUser['password'])) {
                 
                 // Daftarkan Sesi (Session) ke server PHP
                 session()->set([
                    'id_user'   => $adminUser['id_user'],
                    'nama_user' => $adminUser['nama_user'],
                    'role'      => $adminUser['role'],
                    'no_hp'     => $adminUser['no_hp']
                 ]);
                 
                 // AKTIVASI: Rekam Jejak dan Sesi Aktif Admin
                 $db = \Config\Database::connect();
                 $db->table('user_sessions')->where('id_user', $adminUser['id_user'])->delete();
                 $db->table('user_sessions')->insert([
                     'session_token' => bin2hex(random_bytes(16)),
                     'id_user'       => $adminUser['id_user'],
                     'ip_address'    => $ipAddress,
                     'user_agent'    => (string)$this->request->getUserAgent()->getAgentString(),
                     'last_activity' => date('Y-m-d H:i:s')
                 ]);
                 $db->table('audit_trails')->insert([
                     'id_user'     => $adminUser['id_user'],
                     'aksi'        => 'LOGIN',
                     'detail_aksi' => 'Administrator berhasil login',
                     'ip_address'  => $ipAddress,
                     'created_at'  => date('Y-m-d H:i:s')
                 ]);
                 
                 // BOT TELEGRAM: Notifikasi Admin Login
                 $msgAdmin = "⚡ <b>ADMIN MASUK DASBOR</b>\n"
                           . "--------------------------------\n"
                           . "👤 Admin : " . $adminUser['nama_user'] . "\n"
                           . "🕒 Waktu : " . date('d-m-Y H:i:s') . " WIB\n"
                           . "🌐 IP : " . $ipAddress;
                 send_telegram_notif($msgAdmin);

                 return $this->respond([
                     'status'  => true, 
                     'message' => 'Otentikasi berhasil. Selamat datang di Dasbor Admin, ' . $adminUser['nama_user'] . '!'
                 ]);
             }
             
             return $this->failUnauthorized('Kredensial Administrator tidak valid. Nomor HP atau Password salah.');
        }

        // =========================================================================
        // SKENARIO B: LOGIN / DAFTAR MAHASISWA (Reguler)
        // =========================================================================
        
        // Cari Mahasiswa berdasarkan No HP
        // (Mendukung method kustom authenticateStudent jika Anda membuatnya di Model, atau fallback bawaan)
        if (method_exists($userModel, 'authenticateStudent')) {
            $userByHp = $userModel->authenticateStudent($noHp);
        } else {
            $userByHp = $userModel->where('no_hp', $noHp)->first();
        }

        if ($userByHp) {
            
            // --- B.1: Nomor HP Ditemukan (Proses Login Mahasiswa Lama) ---
            
            session()->set([
                'id_user'   => $userByHp['id_user'],
                'nama_user' => $userByHp['nama_user'],
                'role'      => $userByHp['role'],
                'no_hp'     => $userByHp['no_hp'],
                'is_premium'=> $userByHp['is_premium']
            ]);

            // AKTIVASI: Rekam Jejak dan Sesi Aktif Mahasiswa Lama
            $db = \Config\Database::connect();
            $db->table('user_sessions')->where('id_user', $userByHp['id_user'])->delete();
            $db->table('user_sessions')->insert([
                'session_token' => bin2hex(random_bytes(16)),
                'id_user'       => $userByHp['id_user'],
                'ip_address'    => $ipAddress,
                'user_agent'    => (string)$this->request->getUserAgent()->getAgentString(),
                'last_activity' => date('Y-m-d H:i:s')
            ]);
            $db->table('audit_trails')->insert([
                'id_user'     => $userByHp['id_user'],
                'aksi'        => 'LOGIN',
                'detail_aksi' => 'Mahasiswa berhasil login',
                'ip_address'  => $ipAddress,
                'created_at'  => date('Y-m-d H:i:s')
            ]);

            // BOT TELEGRAM: Aktivitas Login Mahasiswa
            $msgLogin = "🚀 <b>" . esc($userByHp['nama_user']) . " Berhasil Login!</b>\n"
                      . "--------------------------------\n"
                      . "📱 No. HP : <code>" . esc($userByHp['no_hp']) . "</code>\n"
                      . "🕒 Waktu : " . date('d-m-Y H:i') . " WIB\n"
                      . "--------------------------------";
            send_telegram_notif($msgLogin);

            return $this->respond([
                'status'  => true,
                'message' => 'Berhasil masuk. Selamat datang kembali, ' . $userByHp['nama_user'] . '!',
                'data'    => [
                    'id_user'   => $userByHp['id_user'],
                    'nama_user' => $userByHp['nama_user'],
                    'role'      => $userByHp['role'],
                    'no_hp'     => $userByHp['no_hp'],
                    'is_premium'=> $userByHp['is_premium'],
                    'img_mhs'   => $userByHp['img_mhs'] ?? 'mahasiswa.webp'
                ]
            ]);

        } else {
            
            // --- B.2: Nomor HP Belum Terdaftar (Proses Registrasi Otomatis) ---
            
            if (empty($namaUser)) {
                return $this->fail('Akun belum terdaftar. Silakan masukkan Nama Lengkap Anda terlebih dahulu untuk memulai pendaftaran otomatis.', 400);
            }

            $newData = [
                'nama_user' => $namaUser,
                'no_hp'     => $noHp,
                'role'      => 'mahasiswa' // Set role secara eksplisit
            ];

            // Karena data sudah disaring dengan regex di atas, namun Trigger DB juga melakukan validasi ketat,
            // kita bungkus dengan try-catch agar error trigger (SQLSTATE 45000) tidak menyebabkan crash Fatal Error 500.
            try {
                if ($userModel->insert($newData)) {
                    
                    $newId = $userModel->getInsertID();

                    // Daftarkan sesi agar pengguna langsung siap mengakses soal Tes Formatif
                    session()->set([
                        'id_user'   => $newId,
                        'nama_user' => $namaUser,
                        'role'      => 'mahasiswa',
                        'no_hp'     => $noHp,
                        'is_premium'=> 0
                    ]);

                    // AKTIVASI: Rekam Jejak dan Sesi Aktif Mahasiswa Baru
                    $db = \Config\Database::connect();
                    $db->table('user_sessions')->where('id_user', $newId)->delete();
                    $db->table('user_sessions')->insert([
                        'session_token' => bin2hex(random_bytes(16)),
                        'id_user'       => $newId,
                        'ip_address'    => $ipAddress,
                        'user_agent'    => (string)$this->request->getUserAgent()->getAgentString(),
                        'last_activity' => date('Y-m-d H:i:s')
                    ]);
                    $db->table('audit_trails')->insert([
                        'id_user'     => $newId,
                        'aksi'        => 'REGISTER',
                        'detail_aksi' => 'Pendaftaran akun otomatis',
                        'ip_address'  => $ipAddress,
                        'created_at'  => date('Y-m-d H:i:s')
                    ]);

                    // BOT TELEGRAM: Aktivitas Registrasi / Tambah Mahasiswa Baru
                    $msgRegister = "👤 <b>SELAMAT DATANG " . esc($namaUser) . "</b>\n"
                                 . "📱 No.HP : <code>" . esc($noHp) . "</code>\n"
                                 . "🆔 ID    : <code>" . $newId . "</code>\n"
                                 . "🕒 Waktu : " . date('d-m-Y H:i:s') . " WIB\n"
                                 . "🚀 <i>Selamat Datang di Tes Formatif!</i>";
                    send_telegram_notif($msgRegister);

                    return $this->respondCreated([
                        'status'  => true,
                        'message' => 'Akun berhasil didaftarkan. Selamat mengerjakan!',
                        'data'    => [
                            'id_user'   => $newId,
                            'nama_user' => $namaUser,
                            'role'      => 'mahasiswa',
                            'no_hp'     => $noHp,
                            'is_premium'=> 0,
                            'img_mhs'   => 'mahasiswa.webp'
                        ]
                    ]);
                    
                } else {
                    // Jika gagal simpan (Misal: nama_user ditolak aturan Model)
                    return $this->failValidationErrors($userModel->errors());
                }
            } catch (\Exception $e) {
                // Tangkap error dari Trigger DB (SQLSTATE 45000)
                $pesanError = $e->getMessage();
                if (strpos($pesanError, 'Gagal INSERT:') !== false) {
                    // Ambil teks setelah "Gagal INSERT:"
                    preg_match('/Gagal INSERT: (.*)/', $pesanError, $matches);
                    $pesanBersih = isset($matches[0]) ? $matches[0] : 'Gagal didaftarkan karena melanggar aturan database.';
                    return $this->fail($pesanBersih, 400);
                }
                return $this->fail('Terjadi kesalahan database: ' . $pesanError, 500);
            }
        }
    }

    /**
     * Endpoint Baru: POST /api/auth/keluar
     * Menangani proses pembersihan sesi (Logout) dan mencatat lognya ke Bot Telegram.
     * Harus dipanggil (Fetch API) oleh frontend sebelum menghapus localStorage.
     */
    public function keluar()
    {

        // PENGAMANAN: Jangan terima nama_user dari Payload, gunakan Sesi Server
        $namaUser = session()->get('nama_user') ?? 'Mahasiswa (Sesi Berakhir)';
        $idUser = session()->get('id_user') ?? null;
        
        $ipAddress = $this->request->getIPAddress();
        if ($ipAddress === '::1') $ipAddress = '127.0.0.1';
        
        if ($idUser) {
            $db = \Config\Database::connect();
            $db->table('user_sessions')->where('id_user', $idUser)->delete();
            $db->table('audit_trails')->insert([
                'id_user'     => $idUser,
                'aksi'        => 'LOGOUT',
                'detail_aksi' => 'Pengguna keluar dari sistem',
                'ip_address'  => $ipAddress,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }

        // BOT TELEGRAM: Aktivitas Logout Mahasiswa
        $msgLogout = "📴 <b>" . esc($namaUser) . " Keluar (Logout)</b>\n"
                   . "--------------------------------\n"
                   . "🔒 Status : Tes Formatif Ditutup\n"
                   . "🕒 Waktu : " . date('d-m-Y H:i:s') . " WIB\n"
                   . "--------------------------------\n";
        send_telegram_notif($msgLogout);

        // Hancurkan Sesi Server PHP
        session()->destroy();

        return $this->respond([
            'status'  => true,
            'message' => 'Sesi berhasil dihapus secara aman dari server.'
        ]);
    }

    /**
     * Endpoint Baru: GET /api/auth/validate
     * Digunakan oleh Vue.js Frontend untuk memvalidasi apakah cookie sesi di sisi server
     * masih hidup atau sudah kadaluwarsa/hilang.
     */
    public function validateSession()
    {
        $idUser = session()->get('id_user');
        if ($idUser) {
            $userModel = new \App\Models\UsersMhsModel();
            $user = $userModel->find($idUser);
            
            if ($user) {
                // Perbarui sesi server juga agar sinkron dengan database
                session()->set([
                    'id_user'   => $user['id_user'],
                    'nama_user' => $user['nama_user'],
                    'role'      => $user['role'],
                    'no_hp'     => $user['no_hp'],
                    'is_premium'=> $user['is_premium']
                ]);

                return $this->respond([
                    'status' => true,
                    'message' => 'Sesi aktif.',
                    'data' => [
                        'id_user'   => $user['id_user'],
                        'nama_user' => $user['nama_user'],
                        'role'      => $user['role'],
                        'no_hp'     => $user['no_hp'],
                        'is_premium'=> $user['is_premium'],
                        'img_mhs'   => $user['img_mhs'] ?? 'mahasiswa.webp'
                    ]
                ]);
            }
        }
        return $this->failUnauthorized('Sesi tidak valid atau telah berakhir.');
    }
}
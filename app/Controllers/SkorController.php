<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MkModel;

/**
 * SkorController
 * * Mengelola halaman tampilan (View) riwayat nilai mahasiswa, 
 * serta visualisasi Papan Peringkat (Leaderboard) publik kuis UT.
 */
class SkorController extends BaseController
{

    /**
     * Menampilkan Halaman Papan Peringkat (Leaderboard) Publik
     * URL: localhost/peringkat/(:segment)/MODUL-(:num) -> Contoh: localhost/peringkat/STSI4201/MODUL-1
     * * @param string $kodeMk Kode unik Mata Kuliah
     * @param int $modul Nomor Modul
     * @return string Rendered HTML View
     */
    public function leaderboardView($kodeMk, $modul)
    {
        // 1. Inisialisasi Model Mata Kuliah
        $mkModel = new MkModel();
        
        // 2. Ambil metadata berdasarkan kode segmen URL
        $mkData = $mkModel->getByKode($kodeMk);

        if (!$mkData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Mata kuliah dengan kode $kodeMk tidak ditemukan.");
        }

        // 3. Render halaman peringkat dengan menyuntikkan ID MK baru (id_mk) untuk dikonsumsi oleh Axios Vue.js
        return view('peringkat_view', [
            'title'  => "Peringkat Puncak - $kodeMk Modul $modul",
            'idMk'   => $mkData['id_mk'], // Menggunakan id_mk hasil penyelarasan skema baru
            'kodeMk' => $kodeMk,
            'modul'  => (int)$modul
        ]);
    }

    /**
     * Menampilkan Halaman Papan Peringkat Khusus Simulasi UAS
     * URL: localhost/peringkat-uas/(:segment) -> Contoh: localhost/peringkat-uas/STSI4201
     * @param string $kodeMk Kode unik Mata Kuliah
     * @return string Rendered HTML View
     */
    public function leaderboardUasView($kodeMk)
    {
        // 1. Inisialisasi Model Mata Kuliah
        $mkModel = new MkModel();
        
        // 2. Ambil metadata berdasarkan kode segmen URL
        $mkData = $mkModel->getByKode($kodeMk);

        if (!$mkData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Mata kuliah dengan kode $kodeMk tidak ditemukan.");
        }

        // 3. Render halaman peringkat dengan status UAS
        return view('peringkat_view', [
            'title'  => "Peringkat Puncak - $kodeMk Simulasi UAS",
            'idMk'   => $mkData['id_mk'],
            'kodeMk' => $kodeMk,
            'modul'  => 'Simulasi UAS'
        ]);
    }

    /**
     * Menampilkan Halaman Riwayat Belajar Profil Mahasiswa
     * URL: localhost/riwayat
     * * @return mixed Redirect atau Rendered HTML View
     */
    public function riwayatView()
    {
        // Proteksi: Sesi wajib aktif agar komponen Vue.js dapat menarik data riwayat miliknya sendiri
        if (!session()->get('id_user')) {
            return redirect()->to('/')->with('error', 'Silakan masuk (login) terlebih dahulu untuk melihat riwayat belajar Anda.');
        }

        return view('riwayat_saya', [
            'title' => 'Riwayat'
        ]);
    }

    /**
     * Menampilkan Papan Peringkat Global (Hall of Fame)
     * URL: localhost/prestasi
     * Mengambil 100 skor tertinggi dan terbaru dari seluruh mata kuliah
     */
    public function globalLeaderboardView()
    {
        $db = \Config\Database::connect();
        
        // 1. Ambil 5 Aktivitas Terbaru
        $skorTerbaruRaw = $db->table('skor_mhs')
            ->select('skor_mhs.*, users_mhs.nama_user, users_mhs.no_hp, mata_kuliah.nama_mk')
            ->join('users_mhs', 'users_mhs.id_user = skor_mhs.id_user', 'left')
            ->join('mata_kuliah', 'mata_kuliah.id_mk = skor_mhs.id_mk', 'left')
            ->orderBy('skor_mhs.waktu_selesai', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        // 2. Ambil Papan Peringkat 3 Hari Terakhir
        $allSkor = $db->table('skor_mhs')
            ->select('skor_mhs.*, users_mhs.nama_user, users_mhs.no_hp, mata_kuliah.nama_mk')
            ->join('users_mhs', 'users_mhs.id_user = skor_mhs.id_user', 'left')
            ->join('mata_kuliah', 'mata_kuliah.id_mk = skor_mhs.id_mk', 'left')
            ->where('DATE(skor_mhs.waktu_selesai) >=', date('Y-m-d', strtotime('-3 days')))
            ->orderBy('skor_mhs.skor', 'DESC')
            ->orderBy('skor_mhs.waktu_selesai', 'ASC')
            ->get()
            ->getResultArray();

        $topSkorPerModul = [];
        $seen = [];
        foreach ($allSkor as $s) {
            $key = $s['id_mk'] . '_' . $s['jenis_ujian'] . '_' . ($s['modul_ke'] ?? 'UAS');
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $topSkorPerModul[] = $s;
            }
        }

        // Fungsi Penyamaran Nomor HP (Misal: 081234567890 -> 08123456***0)
        $maskPhone = function($phone) {
            $phone = trim((string)$phone);
            if (empty($phone) || strlen($phone) < 6) return 'Tidak Diketahui';
            // Menampilkan bagian depan, menyembunyikan 3 angka sebelum angka terakhir
            return substr($phone, 0, -4) . '***' . substr($phone, -1);
        };

        // Format data Terbaru
        foreach ($skorTerbaruRaw as &$s) {
            $s['no_hp_masked'] = $maskPhone($s['no_hp']);
            $s['waktu_format'] = date('d M Y, H:i', strtotime($s['waktu_selesai']));
        }
        
        // Format data Top Skor
        foreach ($topSkorPerModul as &$s) {
            $s['no_hp_masked'] = $maskPhone($s['no_hp']);
            $s['waktu_format'] = date('d M Y, H:i', strtotime($s['waktu_selesai']));
        }

        return view('halaman/peringkat_global', [
            'title'       => 'Papan Prestasi Global - UT Tes Formatif',
            'skorTerbaru' => json_encode($skorTerbaruRaw),
            'skorList'    => json_encode($topSkorPerModul)
        ]);
    }
}
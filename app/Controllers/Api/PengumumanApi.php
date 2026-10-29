<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

/**
 * PengumumanApi
 * Mengelola distribusi maklumat atau pengumuman akademik aktif 
 * yang disaring otomatis berdasarkan rentang waktu server, 
 * serta menyediakan antarmuka CRUD untuk dasbor Administrator.
 */
class PengumumanApi extends ResourceController
{
    protected $format = 'json';

    /**
     * GET /api/pengumuman/active
     * Mengambil satu pengumuman terbaru yang masih berlaku (aktif) pada hari ini.
     * Digunakan oleh halaman Beranda Mahasiswa.
     */
    public function getActive()
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('pengumuman');
            
            // Saring data: Waktu sekarang (NOW) harus berada di antara tgl_mulai dan tgl_selesai
            $builder->select('id_pengumuman, judul, isi_pengumuman, tipe_pengumuman, gambar')
                    ->where('tgl_mulai <=', date('Y-m-d H:i:s'))
                    ->where('tgl_selesai >=', date('Y-m-d H:i:s'))
                    ->orderBy('tgl_mulai', 'ASC');

            $pengumuman = $builder->get()->getResultArray();

            // Jika tidak ada pengumuman yang aktif pada rentang tanggal saat ini
            if (empty($pengumuman)) {
                return $this->respond([
                    'status' => 'empty',
                    'data'   => []
                ], 200);
            }

            // Jika terdapat pengumuman aktif, kirimkan data JSON ke klien
            return $this->respond([
                'status' => 'success',
                'data'   => $pengumuman
            ], 200);
            
        } catch (\Exception $e) {
            // Mengatasi fatal error menjadi JSON respons agar tidak merusak parser Frontend
            return $this->failServerError('Terjadi gangguan jaringan atau basis data.');
        }
    }

    /**
     * GET /api/pengumuman
     * Menampilkan seluruh daftar riwayat pengumuman tanpa filter waktu.
     * Digunakan oleh tabel Halaman Administrator.
     */
    public function index()
    {
        try {
            $db = \Config\Database::connect();
            
            // Mengambil semua baris data pengumuman diurutkan berdasarkan pembuatan terbaru
            $data = $db->table('pengumuman')
                       ->orderBy('created_at', 'DESC')
                       ->get()
                       ->getResultArray();
            
            return $this->respond([
                'status' => 'success',
                'data'   => $data
            ], 200);
        } catch (\Exception $e) {
            return $this->failServerError('Gagal memuat daftar riwayat pengumuman.');
        }
    }

    /**
     * POST /api/pengumuman/create
     * PERBAIKAN PRODUCTION: Validasi ketat integritas formulir, Anti-XSS, dan foreign key created_by
     */
    public function create()
    {
        $db = \Config\Database::connect();
        
        // Mencegah error HTTPException (Failed to parse JSON) saat menerima FormData
        if (strpos($this->request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            $data = $this->request->getJSON(true);
        } else {
            $data = $this->request->getPost();
        }

        // Mencegah insersi data kosong (null) yang memicu fatal error SQL
        if (empty($data['judul']) || empty($data['isi_pengumuman']) || empty($data['tgl_mulai']) || empty($data['tgl_selesai']) || empty($data['created_by'])) {
            return $this->fail('Gagal memproses! Sesi Admin tidak valid atau formulir tidak diisi dengan lengkap.', 400);
        }

        // Susun payload untuk disisipkan ke basis data (Dilengkapi proteksi strip_tags Anti-XSS)
        $insertData = [
            'judul'           => strip_tags(trim($data['judul'])),
            'isi_pengumuman'  => strip_tags(trim($data['isi_pengumuman']), '<p><br><strong><b><i><em><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><span>'), // Mengizinkan tag format WYSIWYG
            'tipe_pengumuman' => $data['tipe_pengumuman'] ?? 'info',
            'tgl_mulai'       => $data['tgl_mulai'],
            'tgl_selesai'     => $data['tgl_selesai'],
            'created_by'      => (int)$data['created_by'] // Wajib dikunci ke tipe data integer
        ];

        // Eksekusi penyisipan data
        if ($db->table('pengumuman')->insert($insertData)) {
            $insertId = $db->insertID();
            
            // Proses Upload Gambar Banner
            $file = $this->request->getFile('gambar');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $imageName = $insertId . '_' . date('YmdHis') . '.webp';
                
                try {
                    \Config\Services::image()
                        ->withFile($file->getTempName())
                        ->convert(IMAGETYPE_WEBP)
                        ->save(FCPATH . 'pengumuman/' . $imageName);
                    
                    $db->table('pengumuman')->where('id_pengumuman', $insertId)->update(['gambar' => $imageName]);
                } catch (\Exception $e) {
                    log_message('error', '[Upload Image Error]: ' . $e->getMessage());
                    // Jika gagal memproses gambar, tidak menghentikan respon sukses
                }
            }

            return $this->respond([
                'status'  => true,
                'message' => 'Pengumuman baru berhasil dipublikasikan ke sistem.'
            ], 200);
        }

        return $this->failServerError('Gagal menyimpan pengumuman ke dalam database.');
    }

    /**
     * PUT (atau POST via Spoofing) /api/pengumuman/update/(:num)
     * Mengubah data pengumuman yang sudah ada berdasarkan id_pengumuman.
     */
    public function update($id = null)
    {
        $db = \Config\Database::connect();
        
        // Mencegah error HTTPException (Failed to parse JSON) pada method spoofing PUT
        if (strpos($this->request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            $data = $this->request->getJSON(true);
        } else {
            $data = $this->request->getPost();
        }

        $builder = $db->table('pengumuman');
        
        // Pastikan baris data yang ingin diubah benar-benar eksis di database
        $exists = $builder->where('id_pengumuman', $id)->get()->getRowArray();
        if (!$exists) {
            return $this->failNotFound('Data pengumuman tidak ditemukan di sistem.');
        }

        // Tumpuk data lama dengan data baru yang direvisi dari form (Dilengkapi proteksi Anti-XSS)
        $updateData = [
            'judul'           => isset($data['judul']) ? strip_tags(trim($data['judul'])) : $exists['judul'],
            'isi_pengumuman'  => isset($data['isi_pengumuman']) ? strip_tags(trim($data['isi_pengumuman']), '<p><br><strong><b><i><em><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><span>') : $exists['isi_pengumuman'],
            'tipe_pengumuman' => $data['tipe_pengumuman'] ?? $exists['tipe_pengumuman'],
            'tgl_mulai'       => $data['tgl_mulai'] ?? $exists['tgl_mulai'],
            'tgl_selesai'     => $data['tgl_selesai'] ?? $exists['tgl_selesai']
        ];

        // Eksekusi pembaruan data ke basis data
        if ($builder->where('id_pengumuman', $id)->update($updateData)) {
            
            // Proses Upload Gambar Banner
            $file = $this->request->getFile('gambar');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $imageName = $id . '_' . date('YmdHis') . '.webp';
                
                try {
                    \Config\Services::image()
                        ->withFile($file->getTempName())
                        ->convert(IMAGETYPE_WEBP)
                        ->save(FCPATH . 'pengumuman/' . $imageName);
                    
                    $db->table('pengumuman')->where('id_pengumuman', $id)->update(['gambar' => $imageName]);
                } catch (\Exception $e) {
                    log_message('error', '[Upload Image Error]: ' . $e->getMessage());
                    // Jika gagal memproses gambar
                }
            }

            return $this->respond([
                'status'  => true,
                'message' => 'Data pengumuman berhasil diperbarui.'
            ], 200);
        }

        return $this->failServerError('Gagal memperbarui data pengumuman di server.');
    }

    /**
     * DELETE /api/pengumuman/delete/(:num)
     * Menghapus secara permanen baris data pengumuman berdasarkan id_pengumuman.
     */
    public function delete($id = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('pengumuman');
        
        // Verifikasi eksistensi data sebelum melakukan penghapusan
        if (!$builder->where('id_pengumuman', $id)->get()->getRowArray()) {
            return $this->failNotFound('Data Pengumuman yang ingin dihapus tidak ditemukan.');
        }

        // Eksekusi penghapusan baris data
        if ($builder->where('id_pengumuman', $id)->delete()) {
            return $this->respond([
                'status'  => true,
                'message' => 'Pengumuman berhasil dihapus permanen dari sistem.'
            ], 200);
        }

        return $this->failServerError('Gagal menghapus data dari pangkalan data.');
    }
}

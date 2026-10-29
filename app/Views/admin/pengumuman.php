<?= $this->extend('layout/admin_layout') ?>

<?= $this->section('extra_styles') ?>
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" href="/css/admin/pengumuman.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<main class="container pb-5 pengumuman-container">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark fw-bold"><i class="bi bi-megaphone-fill me-2 text-primary"></i>Manajemen Pengumuman</h2>
        <button class="btn btn-primary shadow-sm rounded-pill px-4 fw-bold" @click="bukaModalTambah()">
            <i class="bi bi-plus-lg"></i> Tambah Pengumuman
        </button>
    </div>

    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3 mt-5 toast-container-custom">
        <div id="adminToast" class="toast align-items-center bg-white border border-2 shadow-lg px-2" :class="toastType === 'success' ? 'border-success text-success' : 'border-danger text-danger'" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
            <div class="d-flex">
                <div class="toast-body fs-6 fw-bold py-2">
                    <i class="bi me-2" :class="toastType === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'"></i>
                    {{ toastMessage }}
                </div>
                <button type="button" class="btn-close m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <div class="card admin-card border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th class="ps-4 col-no">No</th>
                            <th>Judul Pengumuman</th>
                            <th>Tipe Box</th>
                            <th>Mulai Tayang</th>
                            <th>Selesai Tayang</th>
                            <th class="text-center pe-4 col-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="listPengumuman.length === 0">
                            <td colspan="6" class="text-center text-muted py-5 fs-6">
                                Belum ada riwayat pengumuman akademik yang dibuat.
                            </td>
                        </tr>
                        <tr v-for="(item, index) in listPengumuman" :key="item.id_pengumuman">
                            <td class="ps-4 font-monospace">{{ index + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ item.judul }}</div>
                                <small class="text-muted text-truncate d-block text-truncate-custom">{{ item.isi_pengumuman }}</small>
                            </td>
                            <td>
                                <span :class="['badge badge-pill shadow-sm', 
                                              item.tipe_pengumuman === 'danger' ? 'badge-soft-danger' : 
                                              item.tipe_pengumuman === 'warning' ? 'badge-soft-warning' : 
                                              item.tipe_pengumuman === 'success' ? 'badge-soft-success' : 'badge-soft-info']">
                                    {{ String(item.tipe_pengumuman).toUpperCase() }}
                                </span>
                            </td>
                            <td class="small font-monospace text-muted">{{ item.tgl_mulai }}</td>
                            <td class="small font-monospace text-muted">{{ item.tgl_selesai }}</td>
                            <td class="text-center pe-4">
                                <div class="btn-group gap-1">
                                    <button class="btn btn-action text-warning shadow-sm" @click="bukaModalEdit(item)" title="Ubah Pengumuman">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-action text-danger shadow-sm" @click="hapusPengumuman(item.id_pengumuman)" title="Hapus Permanen">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="crudAnnouncementModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg modal-content-custom">
                <div class="modal-header bg-light border-0 modal-header-custom">
                    <h5 class="modal-title fw-bold">
                        {{ isEditMode ? 'Ubah Maklumat Pengumuman' : 'Buat Maklumat Baru' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form @submit.prevent="simpanDataPengumuman">
                    <div class="modal-body p-4">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small">Judul Maklumat / Pengumuman</label>
                            <input type="text" v-model="form.judul" class="form-control" placeholder="Contoh: SOSIALISASI UJIAN AKHIR SEMESTER (UAS) 2026.1" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small">Tipe Box Visual (Warna Alert)</label>
                            <select v-model="form.tipe_pengumuman" class="form-select">
                                <option value="info">Info (Biru Default - Informasi Umum)</option>
                                <option value="warning">Warning (Kuning - Perhatian/Peringatan)</option>
                                <option value="danger">Danger (Merah - Sangat Penting/Kritis)</option>
                                <option value="success">Success (Hijau - Berita Baik/Sosialisasi)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small">Isi Pengumuman Lengkap</label>
                            <div id="quillEditor" class="bg-white"></div>
                            <div class="form-text text-muted small mt-2">Isi pengumuman menggunakan format WYSIWYG secara langsung ke mahasiswa.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small">Banner Gambar Pengumuman (Opsional)</label>
                            <input type="file" ref="gambarInput" class="form-control" accept="image/*" @change="handleFileChange">
                            <div class="form-text text-muted small mt-1">Format didukung: JPG, PNG, WEBP. Gambar akan otomatis dikonversi ke format WebP untuk optimalisasi kecepatan situs.</div>
                            <div v-if="form.gambar" class="mt-2 bg-light p-2 rounded">
                                <span class="small fw-bold text-success"><i class="bi bi-image"></i> Banner saat ini:</span> 
                                <span class="small text-muted font-monospace">{{ form.gambar }}</span>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">Mulai Tayang (Jam/Tgl)</label>
                                <input type="datetime-local" v-model="form.tgl_mulai" class="form-control font-monospace" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">Selesai Tayang (Jam/Tgl)</label>
                                <input type="datetime-local" v-model="form.tgl_selesai" class="form-control font-monospace" required>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-0 bg-light py-3 px-4 modal-footer-custom">
                        <button type="button" class="btn btn-secondary rounded-pill px-4 shadow-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" :disabled="isSaving">
                            <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> 
                            {{ isSaving ? 'Memproses...' : 'Simpan Data' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>
<script src="/js/admin/pengumuman.js"></script>
<?= $this->endSection() ?>
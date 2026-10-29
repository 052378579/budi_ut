<?= $this->extend('layout/personal_template') ?>

<?= $this->section('extra_styles') ?>
<link rel="stylesheet" href="/css/unduh.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="overlay-bg"></div>

<main class="container content-wrapper" id="ut">
    
    <div class="card download-card">
        
        <button @click="tutupHalaman" class="btn-close-custom" title="Tutup dan Kembali">
            <i class="bi bi-x-lg"></i>
        </button>

        <div class="mb-3">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-4 shadow-sm" style="width: 70px; height: 70px;">
                <i class="bi bi-android2" style="font-size: 2.5rem;"></i>
            </div>
        </div>
        
        <h3 class="fw-bold text-dark mb-1">APK Tes Formatif</h3>
        <p class="text-secondary small mb-4">Lebih dekat, hadir di Perangkat Anda</p>
        
        <div class="file-info-box">
            File : <b class="text-danger">TesFormatif.apk</b>
        </div>
        
        <button @click="unduhApk" class="btn btn-download mb-4" :disabled="isDownloading">
            <span v-if="isDownloading"><span class="spinner-border spinner-border-sm me-2"></span>MENYIAPKAN...</span>
            <span v-else><i class="bi bi-cloud-arrow-down-fill me-2"></i> UNDUH APK</span>
        </button>
        
        <div class="mt-2 pt-3 border-top">
            <div class="counter-text">{{ totalDownloads }}</div>
            <small class="text-secondary fw-bold" style="letter-spacing: 1.5px; font-size: 0.75rem;">TOTAL UNDUHAN</small>
        </div>
    </div>

    <div class="modal fade" id="saranModal" tabindex="-1" aria-hidden="true" ref="saranModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 bg-light pb-2">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-envelope-paper-heart text-primary fs-4"></i> Kotak Saran
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-3">
                    <p class="text-secondary small mb-4">Masukan Anda sangat berarti bagi pengembangan aplikasi <b>Tes Formatif</b>. Jangan ragu untuk melaporkan <i>bug</i> atau mengusulkan fitur baru!</p>
                    
                    <form @submit.prevent="kirimSaran">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-dark">Kategori Laporan</label>
                            <select v-model="saranForm.kategori" class="form-select rounded-3 shadow-none border-secondary-subtle" required>
                                <option value="bug">🐛 Laporan Eror / Bug</option>
                                <option value="fitur">💡 Usulan Fitur Baru</option>
                                <option value="desain">🎨 Kritik Desain / Tampilan</option>
                                <option value="konten">📝 Kesalahan Konten / Soal</option>
                                <option value="lainnya">📌 Lainnya</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-dark">Detail Masukan</label>
                            <textarea v-model="saranForm.isi_saran" class="form-control rounded-3 shadow-none border-secondary-subtle" rows="4" placeholder="Ceritakan secara rinci masukan atau kendala yang Anda alami..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm" :disabled="isSubmittingSaran">
                            <span v-if="isSubmittingSaran"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Mengirim...</span>
                            <span v-else><i class="bi bi-send-fill me-1"></i> Kirim Saran</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/js/unduh.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
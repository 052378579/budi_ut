<?= $this->extend('layout/personal_template') ?>

<?= $this->section('extra_styles') ?>
<link rel="stylesheet" href="/css/riwayat.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<main class="container content-wrapper mb-5 flex-grow-1">
    
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <h2 class="fw-bold text-dark">
            <i class="bi bi-clock-history text-primary me-2"></i>Riwayat Saya
        </h2>
        <button class="btn btn-primary bg-primary border rounded-pill px-4 shadow-sm small fw-bold" @click="fetchRiwayat" :disabled="isLoading">
            {{ isLoading ? 'Memuat...' : 'Segarkan' }}
        </button>
    </div>

    <div v-if="isLoading" class="text-center py-5">
        <div class="spinner-border text-primary border-4" style="width: 3rem; height: 3rem;" role="status"></div>
        <p class="mt-3 text-muted fw-medium">Menarik rekap pangkalan data nilai Anda...</p>
    </div>

    <div v-else-if="riwayatList.length === 0" class="alert alert-info text-center rounded-4 shadow-sm py-5 border-0 bg-white">
        <div class="display-1 opacity-25 mb-3">📝</div>
        <h4 class="alert-heading fw-bold mb-2">Belum Ada Riwayat Ujian</h4>
        <p class="text-muted mb-4">Anda belum pernah mengambil Tes Formatif Modul maupun Simulasi UAS</p>
        <a href="/" class="btn btn-primary rounded-pill px-5 shadow-sm fw-bold">Coba Tes Formatif</a>
    </div>

    <div v-else class="card riwayat-card shadow-sm bg-white">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-dark">
                        <tr>
                            <th width="8%">No</th>
                            <th width="12%">Sampul</th>
                            <th width="35%" class="text-start">Mata Kuliah</th>
                            <th width="20%">Jenis Evaluasi</th>
                            <th width="15%">Tanggal</th>
                            <th width="10%">Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in riwayatList" :key="item.id_skor">
                            <td class="fw-bold text-secondary">{{ index + 1 }}</td>
                            <td>
                                <img :src="'/api/dokumen/cover/' + (item.kode_bmp || item.kode_mk)" 
                                     @error="handleImageError"
                                     class="img-cover-thumb border shadow-sm" 
                                     loading="lazy"
                                     :alt="item.nama_mk">
                            </td>
                            <td class="text-start">
                                <div class="fw-bold text-dark mb-0 lh-sm">{{ item.nama_mk }}</div>
                                <small class="text-muted font-monospace" style="font-size: 0.8rem;">
                                    {{ item.kode_mk }} &bull; BMP {{ item.kode_bmp || item.kode_mk }}
                                </small>
                            </td>
                            <td>
                                <span v-if="item.jenis_ujian === 'uas' || item.modul_ke == 0" class="badge bg-danger px-3 py-2 rounded-pill shadow-sm">
                                    <i class="bi bi-file-earmark-lock-fill me-1"></i> SIMULASI UAS
                                </span>
                                <span v-else class="badge bg-light text-primary border border-primary px-3 py-2 rounded-pill fw-bold">
                                    <i class="bi bi-book me-1"></i> Modul Ke-{{ item.modul_ke }}
                                </span>
                            </td>
                            <td class="small text-secondary">
                                {{ formatWaktu(item.waktu_selesai) }}
                            </td>
                            <td>
                                <span class="score-badge text-success">
                                    {{ parseFloat(item.skor).toFixed(2) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/js/riwayat.js?v=<?= time() ?>"></script>

<?= $this->endSection() ?>
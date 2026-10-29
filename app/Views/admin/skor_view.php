<?= $this->extend('layout/admin_layout') ?>

<?= $this->section('extra_styles') ?>
<link rel="stylesheet" href="/css/admin/skor.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<main class="container pb-5 skor-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark fw-bold"><i class="bi bi-bar-chart-line-fill me-2 text-primary"></i>Riwayat Tes Formatif</h2>
        <button class="btn btn-secondary shadow-sm rounded-pill px-4 fw-bold" @click="fetchSemuaSkor" :disabled="isLoading">
            {{ isLoading ? 'Memuat...' : '🔄 Segarkan Data' }}
        </button>
    </div>

    <div class="row mb-3 align-items-center">
        <div class="col-md-6 d-flex align-items-center">
            <label class="me-2 small fw-bold text-muted">Tampilkan:</label>
            <select class="form-select form-select-sm w-auto rounded-pill shadow-sm" v-model="itemsPerPage" @change="currentPage = 1">
                <option v-for="opt in perPageOptions" :key="opt" :value="opt">{{ opt }}</option>
            </select>
            <span class="ms-2 small text-muted">baris per halaman</span>
        </div>
        <div class="col-md-6 text-md-end">
            <span class="small text-muted bg-white px-3 py-2 rounded-pill shadow-sm">Total Transaksi: <strong>{{ skorList.length }}</strong> Lembar Ujian</span>
        </div>
    </div>

    <div v-if="errorMessage" class="alert alert-danger shadow-sm border-0 alert-custom-radius">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ errorMessage }}
    </div>

    <div class="card admin-card border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th class="ps-4">Waktu Penyelesaian</th>
                            <th>Nama Mata Kuliah</th>
                            <th>Nama Mahasiswa</th>
                            <th>Skor Capaian</th>
                            <th class="text-center col-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="isLoading">
                            <td colspan="5" class="text-center py-5 text-primary">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                Mengambil data transaksi skor desimal...
                            </td>
                        </tr>
                        
                        <tr v-else-if="skorList.length === 0">
                            <td colspan="5" class="text-center py-5 text-muted">
                                Belum ada riwayat pengerjaan ujian kuis di pangkalan data.
                            </td>
                        </tr>
                        
                        <tr v-for="item in paginatedList" :key="item.id_skor">
                            <td class="ps-4 align-middle">{{ formatWaktu(item.waktu_selesai) }}</td>
                            <td class="align-middle">
                                <div class="fw-bold text-dark">{{ item.nama_mk || 'MK-' + item.id_mk }}</div>
                                <div class="mt-1">
                                    <span v-if="item.jenis_ujian === 'uas'" class="badge badge-soft-danger badge-pill"><i class="bi bi-file-earmark-lock-fill"></i> SIMULASI UAS</span>
                                    <span v-else class="badge badge-soft-info badge-pill"><i class="bi bi-book"></i> Modul Ke-{{ item.modul_ke }}</span>
                                </div>
                            </td>
                            <td class="align-middle">
                                <div class="fw-semibold text-dark">{{ item.nama_user || 'Mahasiswa Terhapus' }}</div>
                                <div class="mt-1">
                                    <code class="text-muted" style="background: none; padding: 0;"><i class="bi bi-telephone-fill small"></i> {{ item.no_hp || '-' }}</code>
                                </div>
                            </td> 
                            <td class="fw-bold text-success fs-5 align-middle">
                                {{ parseFloat(item.skor).toFixed(2) }}
                            </td>
                            <td class="text-center">
                                <a v-if="item.no_hp" 
                                   :href="formatWhatsAppLink(item)" 
                                   target="_blank" 
                                   class="btn btn-action text-success shadow-sm me-2" 
                                   title="Kirim Pesan WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                <button class="btn btn-action text-danger shadow-sm" 
                                        @click="hapusSkor(item.id_skor, item.nama_user || 'mahasiswa ini')" title="Hapus Skor">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white border-0 py-3" v-if="skorList.length > 0 && itemsPerPage !== 'Semua'">
            <nav>
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                        <button class="page-link shadow-sm px-3 pagination-prev" @click="currentPage--">Sebelumnya</button>
                    </li>
                    <li v-for="(page, index) in visiblePages" :key="index" class="page-item" :class="{ active: currentPage === page, disabled: page === '...' }">
                        <button v-if="page !== '...'" class="page-link shadow-sm" @click="currentPage = page">{{ page }}</button>
                        <span v-else class="page-link shadow-sm text-muted bg-light">...</span>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                        <button class="page-link shadow-sm px-3 pagination-next" @click="currentPage++">Berikutnya</button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/js/admin/skor.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
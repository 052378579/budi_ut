<?= $this->extend('layout/admin_layout') ?>

<?= $this->section('extra_styles') ?>
<link rel="stylesheet" href="/css/admin/saran.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<main class="container content-wrapper mb-5 flex-grow-1">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0"><i class="bi bi-chat-heart text-danger me-2"></i>Saran & Masukan</h2>
            <p class="text-secondary small mt-1">Daftar laporan bug, fitur, dan umpan balik dari pengguna.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center">
                <label class="me-2 small fw-bold text-muted">Tampilkan:</label>
                <select class="form-select form-select-sm w-auto rounded-pill shadow-sm" v-model="itemsPerPage" @change="resetPages">
                    <option v-for="opt in perPageOptions" :key="opt" :value="opt">{{ opt }}</option>
                </select>
            </div>
            <input type="text" v-model="searchQuery" @input="resetPages" class="form-control search-input" placeholder="Pencarian Cepat...">
        </div>
    </div>

    <div class="card admin-card border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th width="15%">Waktu</th>
                            <th width="20%">Pengirim</th>
                            <th width="15%">Kategori</th>
                            <th width="35%">Isi Saran</th>
                            <th width="15%">Perangkat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="saran in paginatedSaran" :key="saran.id_saran">
                            <td class="text-secondary small fw-medium">{{ formatDateTime(saran.created_at) }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ saran.nama_user || 'Anonim' }}</div>
                                <div class="small text-muted">{{ saran.no_hp || '-' }}</div>
                            </td>
                            <td><span class="badge badge-pill text-uppercase shadow-sm" :class="getBadgeClass(saran.kategori)">{{ saran.kategori }}</span></td>
                            <td class="small text-dark pre-line-text">{{ saran.isi_saran }}</td>
                            <td class="font-monospace small text-muted"><code class="device-code">{{ saran.perangkat_info || 'Unknown' }}</code></td>
                        </tr>
                        <tr v-if="filteredSaran.length === 0">
                            <td colspan="5" class="text-center py-5 text-muted">Data saran dan masukan belum tersedia.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center" v-if="itemsPerPage !== 'Semua'">
            <span class="small text-muted">Total Data: <strong>{{ filteredSaran.length }}</strong></span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                        <button class="page-link shadow-sm px-3 pagination-prev" @click="currentPage--">Sebelumnya</button>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link text-dark shadow-sm">Hal {{ currentPage }} dari {{ totalPages }}</span>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage === totalPages || totalPages === 0 }">
                        <button class="page-link shadow-sm px-3 pagination-next" @click="currentPage++">Berikutnya</button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.saranData = {
        saranList: <?= $saranList ?? '[]' ?>
    };
</script>
<script src="/js/admin/saran.js"></script>
<?= $this->endSection() ?>

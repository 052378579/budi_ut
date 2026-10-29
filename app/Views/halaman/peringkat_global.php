<?= $this->extend('layout/personal_template') ?>

<?= $this->section('content') ?>

<header class="hero-bg text-center py-5 position-relative overflow-hidden">
    <div class="position-absolute top-50 start-0 translate-middle-y opacity-25 d-none d-md-block" style="font-size: 10rem; pointer-events: none;"><i class="bi bi-trophy-fill text-warning"></i></div>
    <div class="position-absolute top-50 end-0 translate-middle-y opacity-25 d-none d-md-block" style="font-size: 10rem; pointer-events: none;"><i class="bi bi-star-fill text-warning"></i></div>
    
    <div class="container position-relative z-1">
        <div class="d-inline-block bg-warning text-dark fw-bold rounded-pill px-3 py-1 mb-3 small shadow-sm">
            <i class="bi bi-stars me-1"></i> Hall of Fame
        </div>
        <h2 class="fw-bold text-dark mb-2">Selamat Atas Prestasi Anda</h2>
        <p class="text-secondary mx-auto" style="max-width: 600px; line-height: 1.6;">
            Peringkat dengan Skor Tertinggi pada Tes Formatif dan Simulasi UAS
        </p>
    </div>
</header>

<main class="container my-5 flex-grow-1">

    <!-- SEKSI 1: 5 Skor Terbaru (Ticker/Cards) -->
    <div class="mb-5" v-if="listTerbaru.length > 0">
        <h6 class="fw-bold text-secondary mb-3 text-uppercase" style="letter-spacing: 1px; font-size: 0.85rem;"><i class="bi bi-activity text-primary me-2"></i>Terkini</h6>
        <div class="row flex-nowrap overflow-auto pb-3 hide-scrollbar g-3">
            <div class="col-11 col-sm-6 col-md-4 col-lg-3" v-for="baru in listTerbaru" :key="baru.id_skor + '_baru'">
                <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift" style="background: linear-gradient(145deg, #ffffff, #f8f9fa); transition: transform 0.2s;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar-placeholder rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; font-size: 0.85rem;">
                                {{ baru.nama_user.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <div class="fw-bold text-dark text-truncate" style="font-size: 0.85rem; max-width: 120px;" :title="baru.nama_user">{{ baru.nama_user }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">{{ baru.no_hp_masked }}</div>
                            </div>
                            <div class="ms-auto">
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold p-2" style="font-size: 0.85rem;">{{ Math.floor(baru.skor) == baru.skor ? Math.floor(baru.skor) : baru.skor }}</span>
                            </div>
                        </div>
                        <div class="text-primary fw-bold text-truncate" style="font-size: 0.8rem;" :title="baru.nama_mk">{{ baru.nama_mk || 'Tidak Diketahui' }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            <span v-if="baru.jenis_ujian === 'formatif'">Modul {{ baru.modul_ke }}</span>
                            <span v-else>UAS Akhir</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- SEKSI 2: Papan Peringkat Top Skor -->
    <div class="row align-items-center mb-4 gy-3">
        <div class="col-12 col-md-auto me-auto">
            <h5 class="fw-bold text-dark mb-0 text-nowrap">Skor Tertinggi</h5>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <select v-model="filterMk" class="form-select shadow-sm border-0 bg-white text-truncate w-100" style="border-radius: 12px; cursor: pointer;">
                <option value="">Semua Mata Kuliah</option>
                <option v-for="mk in uniqueMkList" :key="mk" :value="mk">{{ mk }}</option>
            </select>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase" style="letter-spacing: 0.5px;">
                    <tr>
                        <th class="py-3 px-4 text-center" style="width: 80px;">Rank</th>
                        <th class="py-3">Mahasiswa</th>
                        <th class="py-3">Mata Kuliah</th>
                        <th class="py-3 text-center d-none d-md-table-cell">Ujian</th>
                        <th class="py-3 text-center">Skor</th>
                        <th class="py-3 px-4 text-end d-none d-md-table-cell">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(skor, index) in paginatedList" :key="skor.id_skor + '_top'" class="border-bottom border-light" style="transition: background 0.2s;">
                        <td class="px-4 text-center">
                            <span v-if="getAbsoluteIndex(index) === 0" class="badge bg-warning text-dark fs-5 shadow-sm rounded-circle p-2" title="Juara 1"><i class="bi bi-trophy-fill"></i></span>
                            <span v-else-if="getAbsoluteIndex(index) === 1" class="badge bg-secondary fs-5 shadow-sm rounded-circle p-2" style="background-color: #c0c0c0 !important;" title="Juara 2"><i class="bi bi-trophy-fill"></i></span>
                            <span v-else-if="getAbsoluteIndex(index) === 2" class="badge fs-5 shadow-sm rounded-circle p-2" style="background-color: #cd7f32 !important;" title="Juara 3"><i class="bi bi-trophy-fill"></i></span>
                            <span v-else class="fw-bold text-muted fs-5">#{{ getAbsoluteIndex(index) + 1 }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-placeholder rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-none d-md-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 40px; height: 40px;">
                                    {{ skor.nama_user.charAt(0).toUpperCase() }}
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">{{ skor.nama_user }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ skor.no_hp_masked }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-primary">{{ skor.nama_mk || 'Tidak Diketahui' }}</div>
                        </td>
                        <td class="text-center d-none d-md-table-cell">
                            <span v-if="skor.jenis_ujian === 'formatif'" class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill fw-medium">Formatif M-{{ skor.modul_ke }}</span>
                            <span v-else class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-medium">UAS Akhir</span>
                        </td>
                        <td class="text-center">
                            <span class="fs-4 fw-bold" :class="skor.skor >= 90 ? 'text-success' : 'text-dark'">{{ Math.floor(skor.skor) == skor.skor ? Math.floor(skor.skor) : skor.skor }}</span>
                        </td>
                        <td class="px-4 text-end text-muted small d-none d-md-table-cell">
                            {{ skor.waktu_format }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div v-if="paginatedList.length === 0" class="text-center py-5 bg-white">
            <i class="bi bi-search text-muted opacity-25" style="font-size: 5rem;"></i>
            <h5 class="text-secondary mt-3 fw-bold">Tidak Ada Data</h5>
            <p class="text-muted">Mata kuliah yang Anda cari tidak memiliki riwayat prestasi yang cocok.</p>
        </div>
    </div>
    
    <!-- PAGINATION -->
    <nav v-if="totalPages > 1" aria-label="Navigasi Peringkat">
        <ul class="pagination justify-content-center border-0">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <a class="page-link shadow-sm rounded-start-pill border-0 px-4 text-dark fw-bold bg-white" href="#" @click.prevent="changePage(currentPage - 1)">Sebelumnya</a>
            </li>
            <li class="page-item disabled d-none d-md-block">
                <span class="page-link border-0 text-muted bg-transparent">Halaman {{ currentPage }} dari {{ totalPages }}</span>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <a class="page-link shadow-sm rounded-end-pill border-0 px-4 text-dark fw-bold bg-white" href="#" @click.prevent="changePage(currentPage + 1)">Selanjutnya</a>
            </li>
        </ul>
    </nav>

</main>

<?= $this->endSection() ?>

<?= $this->section('extra_styles') ?>
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const prestasiData = <?= $skorList ?>;
    const terbaruData  = <?= $skorTerbaru ?>;

    const { createApp } = Vue;
    const app = createApp({
        mixins: [globalMixin], 
        data() {
            return {
                listSkor: prestasiData || [],
                listTerbaru: terbaruData || [],
                filterMk: '',
                perPage: 10,
                currentPage: 1
            }
        },
        watch: {
            // Reset page ke 1 kalau filter/jumlah baris diubah
            filterMk() { this.currentPage = 1; },
            perPage() { this.currentPage = 1; }
        },
        computed: {
            uniqueMkList() {
                const uniqueMks = new Set();
                this.listSkor.forEach(s => {
                    if (s.nama_mk) uniqueMks.add(s.nama_mk);
                });
                return Array.from(uniqueMks).sort();
            },
            filteredList() {
                let result = this.listSkor;
                if (this.filterMk) {
                    result = result.filter(s => s.nama_mk === this.filterMk);
                }
                return result.slice(0, 10); // Batasi keras hanya 10 peringkat tertinggi
            },
            totalPages() {
                if (this.perPage >= 999999) return 1;
                return Math.ceil(this.filteredList.length / this.perPage) || 1;
            },
            paginatedList() {
                if (this.perPage >= 999999) return this.filteredList;
                const start = (this.currentPage - 1) * this.perPage;
                const end = start + this.perPage;
                return this.filteredList.slice(start, end);
            }
        },
        methods: {
            changePage(page) {
                if (page >= 1 && page <= this.totalPages) {
                    this.currentPage = page;
                }
            },
            getAbsoluteIndex(relativeIndex) {
                if (this.perPage >= 999999) return relativeIndex;
                return ((this.currentPage - 1) * this.perPage) + relativeIndex;
            }
        }
    });
    
    app.config.compilerOptions.isCustomElement = (tag) => tag === 'g-emoji';
    app.mount('#ut');
</script>
<?= $this->endSection() ?>

<?= $this->extend('layout/personal_template') ?>

<?= $this->section('content') ?>

<header class="hero-bg text-center py-5">
    <div class="container">
        <h2 class="fw-bold text-white mb-2">Papan Saran & Masukan Publik</h2>
        <p class="text-white-50 mx-auto" style="max-width: 600px;">
            Transparansi penuh atas laporan <i>bug</i>, keluhan, dan usulan fitur dari pengguna aplikasi UT Tes Formatif.
        </p>
    </div>
</header>

<main class="container my-5 flex-grow-1">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h5 class="fw-bold text-dark mb-0">Semua Masukan ({{ filteredList.length }})</h5>
        
        <select v-model="filterKategori" class="form-select w-auto shadow-sm border-0 bg-white" style="border-radius: 12px; cursor: pointer;">
            <option value="semua">Semua Kategori</option>
            <option value="bug">🐛 Bug / Eror</option>
            <option value="fitur">💡 Fitur Baru</option>
            <option value="desain">🎨 Kritik Desain</option>
            <option value="konten">📝 Koreksi Konten</option>
            <option value="lainnya">📌 Lainnya</option>
        </select>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-lg-4" v-for="saran in filteredList" :key="saran.id_saran">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift" style="transition: transform 0.2s;">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span :class="getBadgeClass(saran.kategori)" class="badge rounded-pill px-3 py-2 fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">
                            <i :class="getIconClass(saran.kategori)" class="me-1"></i> {{ saran.kategori }}
                        </span>
                        <span class="text-muted small">{{ saran.waktu_format }}</span>
                    </div>
                    
                    <div class="mb-4 text-dark" style="line-height: 1.6; white-space: pre-wrap; word-break: break-word;">{{ saran.isi_saran }}</div>
                    
                    <div class="mt-auto d-flex align-items-center pt-3 border-top border-light">
                        <div class="avatar-placeholder rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center me-3" style="width: 38px; height: 38px;">
                            {{ saran.nama_user.charAt(0).toUpperCase() }}
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark" style="font-size: 0.85rem;">{{ saran.nama_user }}</span>
                            <span class="text-muted" style="font-size: 0.7rem;">Mahasiswa</span>
                        </div>
                        
                        <!-- Indikator sudah dibaca Admin -->
                        <div class="ms-auto" v-if="saran.status_baca == 1" data-bs-toggle="tooltip" title="Telah ditinjau Admin">
                            <i class="bi bi-check2-all text-success fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-if="filteredList.length === 0" class="text-center py-5">
        <i class="bi bi-inbox text-muted opacity-25" style="font-size: 5rem;"></i>
        <h5 class="text-secondary mt-3 fw-bold">Tidak Ada Masukan</h5>
        <p class="text-muted">Belum ada saran untuk kategori ini.</p>
    </div>

</main>

<?= $this->endSection() ?>

<?= $this->section('extra_styles') ?>
<style>
    .hover-lift:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important; 
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const saranData = <?= $saranList ?>;

    const { createApp } = Vue;
    const app = createApp({
        mixins: [globalMixin], // Bawaan dari personal_template
        data() {
            return {
                listSaran: saranData || [],
                filterKategori: 'semua'
            }
        },
        computed: {
            filteredList() {
                if (this.filterKategori === 'semua') return this.listSaran;
                return this.listSaran.filter(s => s.kategori === this.filterKategori);
            }
        },
        methods: {
            getBadgeClass(kategori) {
                switch(kategori) {
                    case 'bug': return 'bg-danger bg-opacity-10 text-danger';
                    case 'fitur': return 'bg-success bg-opacity-10 text-success';
                    case 'desain': return 'bg-warning bg-opacity-10 text-warning text-dark';
                    case 'konten': return 'bg-info bg-opacity-10 text-info text-dark';
                    default: return 'bg-secondary bg-opacity-10 text-secondary';
                }
            },
            getIconClass(kategori) {
                switch(kategori) {
                    case 'bug': return 'bi-bug-fill';
                    case 'fitur': return 'bi-lightbulb-fill';
                    case 'desain': return 'bi-palette-fill';
                    case 'konten': return 'bi-file-text-fill';
                    default: return 'bi-pin-angle-fill';
                }
            }
        },
        mounted() {
            // Inisialisasi tooltip bootstrap jika diperlukan
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
        }
    });
    
    app.config.compilerOptions.isCustomElement = (tag) => tag === 'g-emoji';
    app.mount('#ut');
</script>
<?= $this->endSection() ?>

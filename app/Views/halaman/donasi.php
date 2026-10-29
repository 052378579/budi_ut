<?= $this->extend('layout/personal_template') ?>

<?= $this->section('extra_styles') ?>
<style>
    .premium-hero {
        background: linear-gradient(135deg, #0a192f 0%, #112240 100%);
        color: white;
        position: relative;
        overflow: hidden;
    }
    .premium-hero::before {
        content: '';
        position: absolute;
        top: -50%; left: -50%; width: 200%; height: 200%;
        background: radial-gradient(circle, rgba(255,215,0,0.15) 0%, rgba(255,215,0,0) 50%);
        animation: rotate 20s linear infinite;
        pointer-events: none;
    }
    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,215,0,0.3);
        border-radius: 1.5rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }
    .gold-text {
        background: linear-gradient(to right, #bf953f, #fcf6ba, #b38728, #fbf5b7);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 800;
    }
    .btn-gold {
        background: linear-gradient(135deg, #FFD700 0%, #FDB931 100%);
        color: #000;
        border: none;
        font-weight: 700;
        transition: all 0.3s ease;
    }
    .btn-gold:hover {
        background: linear-gradient(135deg, #FDB931 0%, #FFD700 100%);
        transform: scale(1.05);
        color: #000;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<main class="flex-grow-1 bg-light pb-5">
    <!-- Hero Section -->
    <header class="premium-hero text-center py-5 shadow-sm">
        <div class="container position-relative z-1 py-4">
            <h1 class="display-4 fw-bold gold-text mb-3">Tingkatkan Pengalaman Belajar Anda</h1>
            <p class="lead text-white-50 mx-auto" style="max-width: 600px;">
                Dapatkan akses penuh tanpa batas ke seluruh Soal Modul dan Simulasi UAS dengan Berdonasi
            </p>
        </div>
    </header>

    <div class="container" style="margin-top: -30px; position: relative; z-1;">
        <div class="row justify-content-center">
            
            <!-- Donasi -->
            <div class="col-md-5 col-lg-4 mb-4" v-for="paket in pakets" :key="paket.id">
                <div class="card glass-card text-center p-4 h-100">
                    <div class="badge bg-warning text-dark position-absolute top-0 start-50 translate-middle rounded-pill px-3 py-2 shadow-sm">
                        <!--<i class="bi bi-star-fill me-1"></i> Rekomendasi-->
                    </div>
                    <h3 class="fw-bold text-dark mt-3">{{ paket.nama_paket }}</h3>
                    <div class="my-4">
                        <h2 class="display-5 fw-bold text-primary mb-0">Rp {{ formatRupiah(paket.nominal) }}</h2>
                        <small class="text-muted">Sekali Bayar</small>
                    </div>
                    <p class="text-secondary flex-grow-1">{{ paket.deskripsi }}</p>
                    <hr class="opacity-10 my-4">
                    <ul class="list-unstyled text-start mb-4 text-secondary">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Akses Semua Modul Premium</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Simulasi UAS Tanpa Batas</li>
                    </ul>
                </div>
            </div>

        </div>

        <!-- Pembayaran -->
        <div class="row justify-content-center mt-5">
            <div class="col-md-8 col-lg-7">
                <div class="text-center mb-4">
                    <h3 class="fw-bold">Metode Pembayaran</h3>
                    <!--<p class="text-muted">Donasi ke</p>-->
                </div>
                
                <div class="row">
                    <div class="col-sm-6 mb-3" v-for="m in metodes" :key="m.id">
                        <div class="card border-0 shadow-sm rounded-4 hover-shadow h-100">
                            <div class="card-body d-flex align-items-center p-3">
                                <div class="bg-light rounded-circle p-3 me-3 text-primary">
                                    <i :class="m.logo_icon || 'bi bi-bank'" class="fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">{{ m.nama_bank }}</h6>
                                    <h5 class="fw-bold text-dark mb-0 user-select-all">{{ m.nomor_rekening }}</h5>
                                    <small class="text-muted">a.n {{ m.atas_nama }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Konfirmasi WA -->
                <div class="card border-0 shadow rounded-4 mt-4 bg-white overflow-hidden">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold mb-3">Sudah Melakukan Transfer?</h5>
                        <p class="text-muted mb-4">Silakan konfirmasi dengan mengirimkan bukti transfer ke WhatsApp. Akun Anda akan segera diaktifkan.</p>
                        <a :href="waLink" target="_blank" class="btn btn-gold btn-lg rounded-pill px-5 shadow">
                            <i class="bi bi-whatsapp me-2"></i> Konfirmasi via WhatsApp
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const { createApp } = Vue;
    const app = createApp({
        mixins: [globalMixin],
        data() {
            return {
                metodes: [],
                pakets: [],
                waNumber: '081313428000',
            }
        },
        computed: {
            waLink() {
                const num = this.waNumber.replace(/^0/, '62');
                const text = encodeURIComponent('Halo Admin, saya ingin konfirmasi pembayaran untuk Upgrade Premium. Berikut bukti transfernya:');
                return `https://wa.me/${num}?text=${text}`;
            }
        },
        methods: {
            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            },
            async loadData() {
                try {
                    const res = await fetch('/api/donasi/data').then(r => r.json());
                    this.metodes = res.metode;
                    this.pakets = res.paket;
                    if(res.whatsapp) this.waNumber = res.whatsapp;
                } catch(e) { console.error('Failed to load data', e); }
            }
        },
        mounted() {
            this.loadData();
        }
    });
    app.config.compilerOptions.isCustomElement = (tag) => tag === 'g-emoji';
    app.mount('#ut');
</script>
<?= $this->endSection() ?>

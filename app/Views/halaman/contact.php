<?= $this->extend('layout/personal_template') ?>

<?= $this->section('content') ?>

<header class="hero-bg text-center py-4">
    <div class="container">
        <h2 class="fw-bold text-dark mb-0">Contact Us</h2>
        <p class="text-secondary mt-2">Hubungi Pengelola UT Tes Formatif</p>
    </div>
</header>

<main class="container my-5 flex-grow-1">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4 text-center">
                <div class="card-body p-5">
                    
                    <div class="mb-4">
                        <i class="bi bi-envelope-paper-heart text-primary" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h4 class="fw-bold text-dark mb-3">Punya Pertanyaan atau Masukan?</h4>
                    <p class="text-secondary mb-5" style="line-height: 1.6;">
                        UT Tes Formatif adalah platform belajar independen. Jika Anda memiliki pertanyaan seputar aplikasi, melaporkan dugaan pelanggaran hak cipta, kemitraan bisnis, atau kendala teknis yang tidak dapat diselesaikan melalui menu "Saran Masukan", silakan hubungi tim administrasi kami.
                    </p>

                    <div class="bg-light rounded-4 p-4 mb-4 d-inline-block shadow-sm">
                        <div class="small text-muted mb-2 text-uppercase fw-bold">Chat Bantuan Langsung</div>
                        <a href="https://wa.me/6281313428000?text=Nama:%0ASaran:%0A" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-lg rounded-pill fw-bold shadow-sm px-4">
                            <i class="bi bi-whatsapp me-2"></i> Hubungi via WhatsApp
                        </a>
                    </div>
                    
                    <p class="text-muted small mt-4">
                        Kami berusaha membalas seluruh pertanyaan dalam waktu 1-2 hari kerja.
                    </p>

                </div>
            </div>
        </div>
    </div>
</main>

<?= $this->section('scripts') ?>
<script>
    const { createApp } = Vue;
    const app = createApp({
        mixins: [globalMixin],
        data() { return {}; }
    });
    app.config.compilerOptions.isCustomElement = (tag) => tag === 'g-emoji';
    app.mount('#ut');
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>

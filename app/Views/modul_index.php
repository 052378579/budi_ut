<?= $this->extend('layout/modul_layout') ?>

<?= $this->section('extra_styles') ?>
<link rel="stylesheet" href="/css/modul.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<main class="container content-wrapper mb-5 flex-grow-1">
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8 text-center">
            
            <div class="bg-white mx-auto mb-4 border rounded-3 shadow-sm d-flex align-items-center justify-content-center" style="height: 220px; width: 160px; overflow: hidden;">
                <img :src="'/api/dokumen/cover/' + mkData.kode_bmp" 
                     @error="handleImageError"
                     class="img-fluid rounded shadow" 
                     style="max-width: 140px; aspect-ratio: 3/4; object-fit: cover;" 
                     loading="lazy"
                     :alt="mkData.nama_mk">
            </div>
            
            <div class="d-flex justify-content-center align-items-center gap-3 mb-2">
                <h1 class="fw-bolder text-dark mb-0">{{ mkData.nama_mk }}</h1>
                
                <button @click="toggleFavorite(mkData.id_mk)" 
                        class="btn btn-light rounded-circle shadow-sm btn-header-fav d-flex align-items-center justify-content-center" 
                        style="width: 45px; height: 45px;" 
                        title="Tandai sebagai Favorit">
                    <i v-if="isFavorite(mkData.id_mk)" class="bi bi-heart-fill text-danger fs-4"></i>
                    <i v-else class="bi bi-heart text-secondary fs-4"></i>
                </button>
            </div>
            
            <div class="d-flex justify-content-center gap-3 fs-5 text-secondary mt-3">
                <span>Kode MK: <strong class="text-primary">{{ kodeMk }}</strong></span>
                <span class="opacity-25">|</span>
                <span>Kode BMP: <strong class="text-dark">{{ mkData.kode_bmp }}</strong></span>
            </div>
            
            <hr class="w-25 mx-auto mt-4 mb-0 opacity-25">
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div v-if="modules.length === 0" class="alert alert-warning text-center rounded-4 shadow-sm py-5 border-0">
                <div class="display-4 mb-3">📂</div>
                <h4 class="alert-heading fw-bold mb-2">Skema Ujian ESSAY (E)</h4>
                <p class="mb-0 text-muted"></p>
            </div>

            <div v-else class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <div class="col" v-for="singleModul in modules" :key="singleModul">
                    <!-- Normal Link for Free Modules or Premium Users -->
                    <a v-if="!isModulPremium(singleModul) || (currentUser && currentUser.is_premium == 1) || (currentUser && currentUser.role === 'admin')" 
                       :href="'/' + kodeMk + '/MODUL-' + singleModul" class="module-link text-decoration-none">
                        <div :class="['card module-card shadow-sm text-center h-100', mkData.theme_color || 'bg-primary']">
                            <div class="card-body d-flex flex-column justify-content-center py-4" :class="isLightTheme ? 'text-dark' : 'text-white'">
                                <h4 class="card-title fw-bold mb-0">
                                    <i v-if="isModulPremium(singleModul)" class="bi bi-unlock-fill text-warning me-2"></i>Modul {{ singleModul }}
                                </h4>
                            </div>
                        </div>
                    </a>
                    <!-- Locked Card for Premium Modules -->
                    <a v-else href="javascript:void(0)" @click.prevent="showPremiumModal()" class="module-link text-decoration-none">
                        <div class="card module-card shadow-sm text-center h-100 bg-secondary" style="opacity: 0.85;">
                            <div class="card-body d-flex flex-column justify-content-center py-4 text-white">
                                <h4 class="card-title fw-bold mb-0">
                                    <i class="bi bi-lock-fill text-warning me-2"></i>Modul {{ singleModul }}
                                </h4>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-center mt-5">
        <a href="/" class="btn btn-outline-secondary rounded-pill px-4 py-2 shadow-sm">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Beranda
        </a>
    </div>
    
    <!-- Modal Penawaran Premium (Upsell) -->
    <div class="modal fade" id="premiumModal" tabindex="-1" aria-labelledby="premiumModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-warning border-0 p-4">
                    <h5 class="modal-title fw-bold text-dark" id="premiumModalLabel">
                        <i class="bi bi-star-fill text-white me-2"></i>Fitur Premium Terkunci
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-4">
                        <i class="bi bi-shield-lock-fill text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Tingkatkan ke Premium</h4>
                    <p class="text-muted mb-4">
                        Modul ini adalah konten eksklusif. Dapatkan akses penuh ke seluruh bank soal, simulasi UAS, dan fitur unggulan lainnya dengan mendaftar akun Premium.
                    </p>
                    <div class="d-grid gap-2">
                        <a href="/donasi" class="btn fw-bold rounded-pill shadow-sm" style="background-color: #ffc107; color: #000;">
                            Tingkatkan Sekarang
                        </a>
                        <button type="button" class="btn btn-link text-secondary text-decoration-none rounded-pill fw-medium" data-bs-dismiss="modal">
                            Nanti Saja
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.utKodeMk = <?= json_encode($kodeMk ?? '') ?>;
    window.utMkData = <?= json_encode($mkData ?? []) ?>;
    window.utModules = <?= json_encode($modules ?? []) ?>;
</script>
<script src="/js/modul.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
<?= $this->extend('layout/personal_template') ?>

<?= $this->section('extra_styles') ?>
<link rel="stylesheet" href="/css/favorit.css?v=<?= time() ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<main class="container content-wrapper mb-5 flex-grow-1" style="padding-top: calc(env(safe-area-inset-top) + 120px);">

    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <h2 class="fw-bold text-dark"><i class="bi bi-heart-fill text-danger me-2"></i>Mata Kuliah Favorit</h2>
        <span v-if="favoritList.length > 0" class="badge bg-primary rounded-pill px-3 py-2 fs-6 shadow-sm">
            {{ favoritList.length }} MK Disimpan
        </span>
    </div>

    <div v-if="isLoading" class="text-center py-5">
        <div class="spinner-border text-primary border-4" style="width: 3rem; height: 3rem;" role="status"></div>
        <p class="mt-3 text-muted fw-medium">Memuat daftar mata kuliah favorit Anda...</p>
    </div>

    <div v-else-if="favoritList.length === 0" class="alert alert-info text-center rounded-4 shadow-sm py-5 border-0 bg-white">
        <div class="display-1 opacity-25 mb-3">📁</div>
        <h4 class="alert-heading fw-bold mb-2">Belum Ada Favorit</h4>
        <p class="text-muted mb-4">Simpan mata kuliah favorit Anda untuk mempermudah akses belajar di lain waktu.</p>
        <a href="/" class="btn btn-primary rounded-pill px-5 shadow-sm fw-bold">Jelajahi Mata Kuliah</a>
    </div>

    <div v-else class="row g-3 g-md-4">
        <div class="col-6 col-md-4 col-lg-3" v-for="mk in favoritList" :key="mk.id_mk">
            <div class="card mk-card shadow-sm h-100 position-relative" style="border-radius: 16px; overflow: hidden; cursor: pointer;" @mouseover="hoverCard = mk.id_mk" @mouseleave="hoverCard = null">
                
                <button @click.stop="hapusFavorit(mk.id_mk)" 
                        class="btn btn-light position-absolute top-0 end-0 m-2 shadow-sm rounded-circle d-flex align-items-center justify-content-center transition-all" 
                        style="width: 35px; height: 35px; z-index: 10;"
                        title="Hapus dari Favorit">
                    <i class="bi bi-heart-fill text-danger"></i>
                </button>
                
                <div @click="openCourse(mk.kode_mk)" class="h-100 d-flex flex-column text-decoration-none">
                    
                    <div :class="['card-header-custom border-0 text-center py-2', mk.theme_color || 'bg-primary']">
                        <span class="small fw-bold text-white">{{ mk.kode_bmp }}</span>
                    </div>
                    
                    <div class="text-center bg-light position-relative" style="aspect-ratio: 3/4; padding: 0px;">
                        <img :src="'/api/dokumen/cover/' + mk.kode_bmp" 
                             @error="handleImageError"
                             class="img-fluid w-100 h-100" 
                             :alt="mk.nama_mk" 
                             loading="lazy"
                             style="object-fit: cover;">
                    </div>
                    
                    <div class="card-body p-3 d-flex flex-column bg-white">
                        <h6 class="card-title fw-bold text-dark mb-3 lh-base">{{ mk.nama_mk }}</h6>             
                        <div class="mt-auto">
                            <hr class="opacity-10 my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock me-1"></i> {{ formatDate(mk.created_at) }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    
</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/js/favorit.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
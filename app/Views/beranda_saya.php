<?= $this->extend('layout/personal_template') ?>

<?= $this->section('extra_styles') ?>
<link rel="stylesheet" href="/css/beranda.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<header class="hero-bg text-center">
    <div class="container">            
        <p class="lead text-secondary d-none d-md-block">Pilih Mata Kuliah untuk memulai Tes Formatif</p>            
        <p class="lead text-secondary d-block d-md-none"></p>
        
        <div class="row justify-content-center">
            <div class="col-md-7 px-4 px-md-3">
                <input type="text" v-model="searchQuery" class="form-control form-control-lg search-box shadow-sm d-none d-md-block" 
                    placeholder="Cari Mata Kuliah (Nama, Kode MK, atau Kode BMP)">
                <input type="text" v-model="searchQuery" class="form-control form-control-lg search-box shadow-sm d-md-none" 
                    placeholder="Cari Nama MK, Kode MK, atau BMP">
            </div>
        </div>
    </div>
</header>

<main class="container mb-5 flex-grow-1">
    <div class="d-flex flex-wrap justify-content-center mb-4 gap-0">
        <button v-for="cat in categories" 
                :key="cat"
                @click="activeFilter = cat"
                :class="['btn btn-filter shadow-sm', activeFilter === cat ? 'btn-primary' : 'btn-white bg-white border']">
            {{ cat }}
        </button>
    </div>

    <div class="row g-3 g-md-4 justify-content-center">
        
        <!-- Skeleton Loading -->
        <template v-if="isLoadingData">
            <div class="col-6 col-md-4 col-lg-3" v-for="n in 8" :key="'skel-'+n">
                <div class="card mk-card shadow-sm h-100 placeholder-glow border-0">
                    <div class="card-header-custom border-0 text-center py-2 bg-secondary bg-opacity-25 placeholder">
                        <span class="small fw-bold text-transparent placeholder">SKOM0000</span>
                    </div>
                    <div class="position-relative w-100 bg-secondary bg-opacity-10 placeholder" style="aspect-ratio: 3/4;"></div>
                    <div class="card-body p-3 d-flex flex-column bg-white">
                        <h6 class="card-title fw-bold text-transparent placeholder rounded col-10 mb-2"></h6>
                        <h6 class="card-title fw-bold text-transparent placeholder rounded col-6 mb-3"></h6>
                        <div class="mt-auto">
                            <hr class="opacity-10 my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-secondary placeholder rounded-pill col-4"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template v-else>
            <div class="col-6 col-md-4 col-lg-3" v-for="mk in filteredList" :key="mk.id_mk">
            <div class="card mk-card shadow-sm h-100 position-relative" @click="openCourse(mk.kode_mk)">
                <button @click.stop="toggleFavorite(mk.id_mk)" 
                        class="btn btn-light btn-sm btn-fav position-absolute top-0 end-0 m-2 rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                        style="width: 35px; height: 35px;"
                        title="Tandai sebagai Favorit">
                    <i v-if="isFavorite(mk.id_mk)" class="bi bi-heart-fill text-danger fs-6"></i>
                    <i v-else class="bi bi-heart text-secondary fs-6"></i>
                </button>
                <div :class="['card-header-custom border-0 text-center py-2', mk.theme_color || 'bg-primary']">
                    <span class="small fw-bold" :class="isCardLightTheme(mk.theme_color) ? 'text-dark' : 'text-white'">{{ mk.kode_bmp }}</span>
                </div>
                
                <div class="position-relative w-100" style="aspect-ratio: 3/4; overflow: hidden;">
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
                            <div class="prodi-badges">
                                <span v-for="p in splitProdi(mk.program_studi)" 
                                      :key="p"
                                      class="badge rounded-pill bg-light text-primary border me-1" 
                                      style="font-size: 0.75rem;">
                                    {{ p }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div v-if="filteredList.length === 0" class="text-center py-5">
        <div class="display-1 text-muted opacity-25 mb-3">:(</div>
        <h4 class="text-secondary">Mata kuliah tidak ditemukan</h4>
        <p class="text-muted">Coba gunakan kata kunci pencarian yang lain</p>
    </div>
</main>

<!-- Login Auth -->
<div class="modal fade" id="authModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" ref="authModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-modal border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 text-dark justify-content-center" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold w-100 text-center">Selamat Datang</h5>
            </div>
            <div class="modal-body p-4 text-start">
                <form @submit.prevent="submitAuth">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" v-model="authForm.nama_user" class="form-control form-control-lg shadow-none" placeholder="" required style="text-transform: uppercase;">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">No. Handphone</label>
                        <input type="text" 
                               inputmode="numeric" 
                               maxlength="16" 
                               v-model="authForm.no_hp" 
                               class="form-control form-control-lg shadow-none" 
                               placeholder="Contoh: 081234567890" 
                               required
                               @input="sanitizePhoneInput">
                        <div class="form-text small text-dark">Nomor akan disembunyikan (Contoh: 08123456**90)</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Verifikasi Keamanan</label>
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-light fw-bold text-primary justify-content-center" style="min-width: 140px;">
                                {{ captcha.num1 }} {{ captcha.operator }} {{ captcha.num2 }} = 
                            </span>
                            <input type="number" 
                                   v-model="authForm.captchaAnswer" 
                                   class="form-control shadow-none" 
                                   placeholder="Ketik Jawaban" 
                                   required>
                            <button type="button" class="btn btn-outline-secondary" @click="generateCaptcha" title="Ganti Angka">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-outline-primary btn-lg w-100 rounded-pill fw-bold shadow-sm" :disabled="isAuthenticating">
                        {{ isAuthenticating ? 'Memproses...' : 'Masuk' }}
                    </button>

                    <!-- Prestasi -->
                    <div class="text-center mt-4">
                        <a href="/prestasi" class="text-decoration-none text-primary fw-bold">
                            <i class="bi bi-trophy-fill text-warning me-1"></i>Lihat Prestasi
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Progressbar Login -->
            <div class="progress" style="height: 6px; border-radius: 0 0 20px 20px;" v-if="isAuthenticating">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%"></div>
            </div>
        </div>
    </div>
</div>

<!-- Pengumuman -->
<div class="modal fade" id="pengumumanModal" tabindex="-1" aria-labelledby="pengumumanModalLabel" aria-hidden="true" style="z-index: 1060;" ref="pengumumanModal">
    <div class="modal-dialog modal-dialog-centered modal-lg" v-if="currentPengumuman">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header border-0" :class="{
                'bg-success': currentPengumuman.tipe_pengumuman === 'success',
                'bg-warning': currentPengumuman.tipe_pengumuman === 'warning',
                'bg-danger': currentPengumuman.tipe_pengumuman === 'danger',
                'bg-info': currentPengumuman.tipe_pengumuman === 'info' || !currentPengumuman.tipe_pengumuman
            }">
                <h5 class="modal-title fw-bold" :class="currentPengumuman.tipe_pengumuman === 'warning' ? 'text-dark' : 'text-white'" id="pengumumanModalLabel">
                    <i class="bi bi-info-circle-fill me-2"></i> PENGUMUMAN
                </h5>
                <button type="button" class="btn-close" :class="currentPengumuman.tipe_pengumuman === 'warning' ? '' : 'btn-close-white'" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 text-start">
                <h5 class="fw-bold text-uppercase mb-3" style="line-height: 1.5;">{{ currentPengumuman.judul }}</h5>
                
                <div class="isi-pengumuman" style="white-space: pre-wrap; font-size: 1rem; color: #4a4a4a; line-height: 1.6;" v-html="currentPengumuman.isi_pengumuman"></div>

                <!-- Kontainer Gambar Dinamis -->
                <div class="text-center mt-4" v-if="currentPengumuman.gambar && currentPengumuman.gambar.trim() !== ''">
                    <hr class="mb-3 border-secondary opacity-25">
                    <img :src="'/pengumuman/' + currentPengumuman.gambar" alt="Banner Pengumuman" class="img-fluid rounded shadow-sm" style="max-width: 100%;">
                </div>
            </div>
        </div>
    </div>
</div>


<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.listMKData = [];
    <?php if (session()->getFlashdata('error')) : ?>
        window.serverFlashError = <?= json_encode(session()->getFlashdata('error')) ?>;
    <?php endif; ?>
</script>

<script src="/js/beranda.js?v=<?= filemtime(FCPATH . 'js/beranda.js') ?>"></script>

<?= $this->endSection() ?>
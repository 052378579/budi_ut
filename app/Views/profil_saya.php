<?= $this->extend('layout/personal_template') ?>

<?= $this->section('extra_styles') ?>
<link rel="stylesheet" href="/css/profil.css?v=<?= time() ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<main class="container profile-container mb-5 flex-grow-1">
    
    <div class="card card-profile p-4 p-md-5">
        
        <div class="text-center mb-5">
            <div class="avatar-wrapper mb-3">
                <img :src="'/api/dokumen/profil/' + (form.img_mhs || 'mahasiswa.webp') + '?v=' + imageVersion" 
                     @error="(e) => { e.target.src = '/api/dokumen/profil/mahasiswa.webp'; }"
                     class="rounded-circle border border-4 border-white shadow bg-light" 
                     style="width: 120px; height: 120px; object-fit: cover;" 
                     loading="lazy"
                     alt="Avatar">
                
                <input type="file" ref="fotoInput" @change="prosesUnggahFoto" accept="image/jpeg,image/png,image/webp" class="d-none">
                
                <div class="btn-upload-overlay" @click="$refs.fotoInput.click()" title="Unggah Pas Foto Kotak (Square)">
                    <i class="bi bi-camera-fill" v-if="!isUploadingFoto"></i>
                    <span class="spinner-border spinner-border-sm" role="status" v-else></span>
                </div>
            </div>
            
            <h3 class="fw-bold text-dark mb-1">{{ form.nama_user || 'NAMA MAHASISWA' }}</h3>
            <span class="badge bg-light text-secondary border rounded-pill px-3 py-2 font-monospace mt-1">
            Status: {{ String(form.role).toUpperCase() }}
            </span>
        </div>

        <div v-if="isPageLoading" class="text-center py-5">
            <div class="spinner-border text-primary border-4" style="width: 3rem; height: 3rem;" role="status"></div>
            <p class="text-muted mt-3 fw-medium">Menyelaraskan biodata Anda dengan Server...</p>
        </div>

        <form v-else @submit.prevent="simpanPerubahanProfil">
            <div class="row g-4">
                
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">
                        <i class="bi bi-phone me-1"></i> Nomor Handphone
                    </label>
                    <input type="text" :value="form.no_hp" class="form-control form-control-lg rounded-3 shadow-none form-control-readonly" readonly disabled>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark">
                        <i class="bi bi-person-badge me-1"></i> Nama Lengkap
                    </label>
                    <input type="text" v-model="form.nama_user" class="form-control form-control-lg rounded-3 shadow-none" placeholder="Masukkan Nama Lengkap" style="text-transform: uppercase;" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark">
                        <i class="bi bi-card-text me-1"></i> Nomor Induk Mahasiswa / NIM
                    </label>
                    <input type="text" v-model="form.nim_mhs" class="form-control form-control-lg rounded-3 shadow-none font-monospace" placeholder="9 Digit Angka (Contoh: 052378579)" maxlength="9">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark">
                        <i class="bi bi-geo-alt me-1"></i> UPBJJ - UT Daerah
                    </label>
                    <select v-model="form.kode_ut" class="form-select form-select-lg rounded-3 shadow-none fw-medium">
                        <option value="">-- Pilih UT Daerah --</option>
                        <option v-for="daerah in listDaerah" :key="daerah.kode_ut" :value="daerah.kode_ut">
                            {{ daerah.kode_ut }} - {{ daerah.nama_ut }}
                        </option>
                    </select>
                </div>

            </div>

            <div class="d-flex gap-2 justify-content-end mt-5 pt-3 border-top">
                <a href="/" class="btn btn-outline-secondary btn-lg rounded-pill px-4 shadow-sm fw-bold fs-6">
                    Kembali
                </a>
                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm fw-bold fs-6" :disabled="isSavingProfil">
                    <span v-if="isSavingProfil"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Menyimpan...</span>
                    <span v-else><i class="bi bi-check-circle-fill me-1"></i> Simpan Profil</span>
                </button>
            </div>
        </form>

    </div>

    </main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/js/profil.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
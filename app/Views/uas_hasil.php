<?= $this->extend('layout/modul_layout') ?>

<?= $this->section('extra_styles') ?>
<link rel="stylesheet" href="/css/uas_hasil.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<main class="container result-container mb-5 flex-grow-1 content-wrapper" id="ut">
    <div class="row justify-content-center">
        <div class="col-12">
            
            <div class="card result-card p-4 p-md-5 text-center mb-5 bg-white">
                <h3 class="fw-bold text-dark mb-4">Lembar Hasil Simulasi UAS</h3>
                
                <div class="score-circle mb-4">
                    <span class="fs-1 fw-bolder text-primary">{{ percentage }}%</span>
                </div>
                
                <p class="text-secondary fs-5 mb-4">
                    Anda berhasil menjawab benar <strong class="text-dark">{{ score }}</strong> dari total <strong class="text-dark">{{ total }}</strong> butir soal ujian.
                </p>
                
                <div class="d-flex justify-content-center">
                    <span class="badge status-badge shadow-sm" :class="parseFloat(percentage) >= 70 ? 'bg-success text-white' : 'bg-warning text-dark'">
                        <i class="bi me-2" :class="parseFloat(percentage) >= 70 ? 'bi-patch-check-fill' : 'bi-exclamation-circle-fill'"></i>
                        Status: {{ passingStatus }}
                    </span>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-card-checklist me-2 text-primary"></i>Tinjauan Evaluasi Jawaban</h5>
                <span class="text-muted small">Mata Kuliah: <strong>{{ kodeMk }}</strong></span>
            </div>
            <div v-if="results.length > 0" class="card review-card p-4 mb-4 bg-white shadow-sm border-0 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge bg-secondary px-3 py-1.5 rounded-pill font-monospace">Nomor {{ currentReviewIdx + 1 }} dari {{ total }}</span>
                    
                    <span :class="results[currentReviewIdx].isCorrect ? 'text-success fw-bold' : 'text-danger fw-bold'" class="small d-flex align-items-center">
                        <i class="bi me-1 fs-5" :class="results[currentReviewIdx].isCorrect ? 'bi-check-circle-fill' : 'bi-x-circle-fill'"></i>
                        {{ results[currentReviewIdx].isCorrect ? 'Benar' : 'Salah' }}
                    </span>
                </div>
                
                <p class="fw-semibold text-dark lh-base mb-4" v-html="results[currentReviewIdx].soal"></p>
                
                <div class="row row-cols-1 g-2">
                    <div v-for="opt in ['A', 'B', 'C', 'D']" :key="opt" 
                         class="option-item d-flex align-items-center" :class="getOptionClass(results[currentReviewIdx], opt)">
                        <span class="fw-bold me-3">{{ opt }}.</span>
                        <span v-html="results[currentReviewIdx]['opsi' + opt]"></span>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <button class="btn btn-outline-secondary px-4 rounded-pill shadow-sm fw-bold" @click="prevReview" :disabled="currentReviewIdx === 0">
                        <i class="bi bi-arrow-left me-1"></i> Mundur
                    </button>
                    <button class="btn btn-primary px-4 rounded-pill shadow-sm fw-bold" @click="nextReview" :disabled="currentReviewIdx === results.length - 1">
                        Maju <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <div class="text-center mt-5">
                <a href="/" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm">
                    <i class="bi bi-house-door-fill me-2"></i> Selesai & Kembali ke Beranda
                </a>
            </div>

        </div>
    </div>
</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.utHasilKodeMk = <?= json_encode($kodeMk ?? '') ?>;
</script>
<script src="/js/uas_hasil.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
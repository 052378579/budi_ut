<?= $this->extend('layout/modul_layout') ?>

<?= $this->section('extra_styles') ?>
<link rel="stylesheet" href="/css/tes_formatif.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<main class="container quiz-container mb-5 flex-grow-1 content-wrapper">
    
    <div v-if="quizStarted && !quizFinished">
        
        <div v-if="loadingData" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3 text-muted">Menyiapkan lembar soal...</p>
        </div>
        
        <div v-else-if="soal.length > 0">
            <div class="progress mb-4 shadow-sm" style="height: 10px; border-radius: 10px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                     :class="mk.theme_color" 
                     :style="{width: progress + '%'}"></div>
            </div>

            <div class="card card-soal p-4 border-0">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <span class="badge px-3 py-2 rounded-pill shadow-sm" :class="mk.theme_color || 'bg-primary'">
                        Soal {{ currentIndex + 1 }} dari {{ soal.length }}
                    </span>
                    
                    <a :href="'/' + kodeMk" class="btn btn-sm btn-outline-danger rounded-pill shadow-sm" title="Batal Mengerjakan">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </a>
                </div>
                
                <h5 class="mb-4 lh-base text-dark fw-semibold" v-html="currentSoal.pertanyaan"></h5>
                
                <div class="list-group">
                    <button v-for="(opt, key) in currentSoal.pilihan" 
                            :key="key"
                            @click="selectAnswer(key)"
                            :disabled="userAnswers[currentIndex] !== undefined"
                            class="list-group-item list-group-item-action mb-2 rounded border transition-all cursor-pointer"
                            :class="getOptionClass(key)">
                        <strong class="me-2">{{ key }}.</strong> <span v-html="opt"></span>
                    </button>
                </div>

                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <button @click="prevSoal" :disabled="currentIndex === 0" class="btn btn-outline-secondary px-4 rounded-pill shadow-sm">
                        <i class="bi bi-arrow-left me-md-1"></i> <span class="d-none d-md-inline">Sebelumnya</span>
                    </button>
                    
                    <button v-if="currentIndex < soal.length - 1" 
                            @click="nextSoal" 
                            class="btn text-white px-5 rounded-pill shadow-sm" 
                            :class="mk.theme_color || 'btn-primary'" 
                            :disabled="userAnswers[currentIndex] === undefined">
                        <span class="d-none d-md-inline">Selanjutnya</span> <i class="bi bi-arrow-right ms-md-1"></i>
                    </button>
                    
                    <button v-else 
                            @click="finishQuiz" 
                            class="btn btn-success px-4 rounded-pill shadow-sm fw-bold" 
                            :disabled="userAnswers[currentIndex] === undefined || isSubmitting">
                        {{ isSubmitting ? 'Menyimpan...' : 'Selesai & Kirim' }}
                    </button>
                </div>
            </div>
        </div>
        
        <div v-else-if="!loadingData && soal.length === 0" class="text-center mt-5">
            <div class="alert alert-warning text-center rounded-4 shadow-sm py-5 border-0 mx-auto" style="max-width: 600px; background-color: #fff3cd;">
                <div class="display-4 mb-3">📂</div>
                <h4 class="alert-heading fw-bold mb-2">Skema Ujian ESSAY (E)</h4>
                <p class="mb-0 text-muted">Bank soal objektif tidak tersedia untuk mata kuliah ini.</p>
                <a :href="'/' + kodeMk" class="btn btn-warning rounded-pill px-4 mt-4 shadow-sm fw-bold">Kembali ke Daftar Modul</a>
            </div>
        </div>
    </div>

    <div v-if="quizFinished" class="card card-soal p-5 text-center">
        <h3 class="fw-bold text-dark">Skor Anda</h3>
        
        <h1 class="display-1 fw-bold my-3" :class="mk.theme_color ? mk.theme_color.replace('bg-', 'text-') : 'text-primary'">
            {{ parseFloat(finalScore).toFixed(2) }}
        </h1>
        
        <p class="text-muted mb-4 fs-5">Terima kasih telah menyelesaikan Tes Formatif <strong>{{ mk.kode_mk }} (Modul {{ modul }})</strong></p>
        
        <div v-if="submitError" class="alert alert-danger shadow-sm border-0 rounded-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ submitError }}
        </div>
        <div v-else-if="submitSuccess" class="alert alert-success fw-medium shadow-sm border-0 rounded-3">
            <i class="bi bi-check-circle-fill me-2"></i> Skor Anda berhasil dicatat!
        </div>

        <div class="d-flex justify-content-center gap-3 mt-4">
            <button @click="resetQuiz" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                <i class="bi bi-arrow-clockwise me-1"></i> Ulangi
            </button>
            <a :href="'/peringkat/' + kodeMk + '/MODUL-' + modul" class="btn btn-warning fw-bold rounded-pill px-4 shadow-sm">
                <i class="bi bi-trophy-fill me-1"></i> Lihat Peringkat
            </a>
        </div>
    </div>
</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.utFormatifKodeMk = '<?= $kodeMk ?>';
    window.utFormatifModul = <?= $modul ?>;
</script>
<script src="/js/tes_formatif.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
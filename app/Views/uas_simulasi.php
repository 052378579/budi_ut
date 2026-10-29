<?= $this->extend('layout/modul_layout') ?>

<?= $this->section('extra_styles') ?>
<link rel="stylesheet" href="/css/uas_simulasi.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<main class="container simulasi-container mb-5 flex-grow-1 content-wrapper" id="ut">
    <div class="row justify-content-center">
        <div class="col-12">
            
            <div v-if="loading" class="text-center py-5 my-5">
                <div class="spinner-border text-primary border-4" style="width: 3rem; height: 3rem;" role="status"></div>
                <h5 class="mt-4 text-dark fw-bold">Menyiapkan Lembar Simulasi UAS...</h5>
                <p class="text-muted">Mengacak bank soal khusus untuk Anda.</p>
            </div>

            <div v-else-if="quizQuestions.length > 0" class="card card-simulasi mb-4">
                
                <div class="card-header bg-white border-bottom p-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div>
                        <h4 class="fw-bold mb-1 text-dark">Simulasi UAS</h4>
                        <span class="text-muted small fw-medium">{{ mkData.nama_mk }} ({{ kodeMk }}) — <span class="badge bg-light text-dark border">{{ mkData.jumlah_sks || 3 }} SKS</span></span>
                    </div>
                    <div>
                        <div class="badge bg-dark text-white py-2 px-4 rounded-pill shadow-sm timer-badge d-flex align-items-center">
                            <i class="bi bi-stopwatch fs-5 me-2" :class="{'text-danger animate__animated animate__flash animate__infinite': timeLeft < 300}"></i> 
                            {{ formattedTime }}
                        </div>
                    </div>
                </div>
                
                <div class="progress rounded-0" style="height: 6px; background-color: #e9ecef;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         :class="mkData.theme_color || 'bg-primary'" 
                         :style="{width: progress + '%'}"></div>
                </div>

                <div class="card-body p-4 p-md-5" v-if="currentSoal">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex gap-2">
                            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill fw-bold">
                                Soal {{ currentIdx + 1 }} / {{ totalQuestions }}
                            </span>
                            
                            <span v-if="currentSoal.source" 
                                  :class="['badge text-white px-3 py-2 rounded-pill fw-medium shadow-sm', mkData.theme_color || 'bg-primary']">
                                <i class="bi bi-bookmarks-fill me-1 small"></i> 
                                {{ currentSoal.source }} <span v-if="currentSoal.original_no">No. {{ currentSoal.original_no }}</span>
                            </span>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-4 lh-base text-dark" v-html="currentSoal.pertanyaan"></h5>
                    
                    <div class="d-grid mt-4">
                        <button v-for="(text, key) in currentSoal.pilihan" :key="key"
                                @click="pilihJawaban(key)"
                                class="opsi-btn transition-all"
                                :class="userAnswers[currentIdx] === key ? (mkData.theme_color + ' text-white selected shadow-sm') : ''">
                            <span class="fw-bold me-3">{{ key }}.</span> <span v-html="text"></span>
                        </button>
                    </div>
                </div>

                <div class="card-footer bg-light border-top p-4 d-flex justify-content-between align-items-center">
                    <button class="btn btn-outline-secondary px-4 py-2 rounded-pill fw-bold shadow-sm" 
                            :disabled="currentIdx === 0" 
                            @click="prevSoal">
                        <i class="bi bi-arrow-left me-1"></i> Sebelumnya
                    </button>
                    
                    <button v-if="currentIdx < totalQuestions - 1" 
                            class="btn px-5 py-2 rounded-pill text-white fw-bold shadow-sm" 
                            :class="mkData.theme_color || 'bg-primary'" 
                            @click="nextSoal">
                        Selanjutnya <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                    
                    <button v-else 
                            class="btn btn-success px-5 py-2 rounded-pill fw-bold shadow-sm" 
                            @click="konfirmasiSubmit"
                            :disabled="isSubmitting">
                        {{ isSubmitting ? 'Memproses...' : 'Selesai Ujian' }} <i class="bi bi-check-circle ms-1"></i>
                    </button>
                </div>
            </div>

            <div v-else class="text-center mt-5">
                <div class="alert alert-warning text-center rounded-4 shadow-sm py-5 border-0 mx-auto" style="max-width: 600px; background-color: #fff3cd;">
                    <div class="display-4 mb-3">📂</div>
                    <h4 class="alert-heading fw-bold mb-2">Skema Ujian ESSAY (E)</h4>
                    <p class="mb-0 text-muted">Bank soal objektif tidak tersedia untuk mata kuliah ini.</p>
                    <a href="/" class="btn btn-warning rounded-pill px-4 mt-4 shadow-sm fw-bold">Kembali ke Beranda</a>
                </div>
            </div>

            <!-- Modal Konfirmasi Submit -->
            <div class="modal fade" id="submitConfirmModal" tabindex="-1" aria-labelledby="submitConfirmModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                  <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="submitConfirmModalLabel">Konfirmasi Ujian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body py-4">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-question-circle-fill text-warning" style="font-size: 2.5rem;"></i>
                        <p class="mb-0 fs-6 text-dark" id="submitConfirmMessage">
                            {{ confirmMessage }}
                        </p>
                    </div>
                  </div>
                  <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light fw-medium rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn px-4 rounded-pill text-white fw-bold shadow-sm" :class="mkData.theme_color || 'bg-primary'" @click="eksekusiSubmit">Selesai Sekarang</button>
                  </div>
                </div>
              </div>
            </div>

        </div>
    </div>
</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.utSimulasiKodeMk = <?= json_encode($kodeMk ?? '') ?>;
</script>
<script src="/js/uas_simulasi.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
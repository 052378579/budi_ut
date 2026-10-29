<?= $this->extend('layout/modul_layout') ?>

<?= $this->section('extra_styles') ?>
<link rel="stylesheet" href="/css/peringkat.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<main class="container content-wrapper mb-5 flex-grow-1">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            
            <div class="text-center mb-5">
                <img :src="mk.kode_bmp ? '/api/dokumen/cover/' + mk.kode_bmp : 'https://placehold.co/160x220/004da1/white?text=Cover+BMP'" 
                     @error="handleImageError"
                     class="img-fluid rounded shadow-sm mb-3" 
                     style="max-height: 120px; object-fit: cover;" 
                     loading="lazy"
                     alt="Cover Buku Materi Pokok">
                <h2 class="fw-bold text-dark mb-1">10 | Top Peringkat</h2>
                <h5 class="text-muted fw-normal">
                    Mata Kuliah: <span class="fw-bold text-primary">{{ mk.nama_mk || kodeMk }}</span> 
                    <span class="mx-2">|</span> 
                    Modul: <span class="fw-bold text-primary">{{ modul }}</span>
                </h5>
            </div>

            <div class="card leaderboard-card shadow-lg mb-4">
                <div class="card-body p-0">
                    
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-3 text-muted">Memuat data peringkat terbaru...</p>
                    </div>

                    <div v-else-if="rankings.length === 0" class="text-center py-5 px-3">
                        <div class="display-1 text-muted opacity-25 mb-3">🏆</div> 
                        <h4 class="fw-bold text-secondary">Belum Ada Juara</h4>
                        <p class="text-muted mb-0">Jadilah Mahasiswa pertama yang menaklukkan Tes Formatif <span class="fw-bold text-primary">Modul {{ modul }}</span> ini!</p>
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table table-hover mb-0 align-middle text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th width="15%" class="py-3">Peringkat</th>
                                    <th width="40%" class="text-start py-3">Nama Mahasiswa</th>
                                    <th width="25%" class="py-3">No. HP</th>
                                    <th width="20%" class="py-3">Skor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(user, index) in rankings" :key="index" :class="getRankClass(index)">
                                    <td>
                                        <div v-if="index === 0" class="medal-icon" title="Juara 1">🥇</div>
                                        <div v-else-if="index === 1" class="medal-icon" title="Juara 2">🥈</div>
                                        <div v-else-if="index === 2" class="medal-icon" title="Juara 3">🥉</div>
                                        <div v-else class="fw-bold text-secondary fs-5">{{ index + 1 }}</div>
                                    </td>
                                    <td class="text-start">
                                        <div class="fw-bold text-dark">{{ user.nama_user }}</div>
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            Selesai: {{ formatWaktu(user.waktu_selesai) }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                            {{ user.no_hp }}
                                        </span>
                                    </td>
                                    <td class="score-text text-primary">
                                        {{ parseFloat(user.skor).toFixed(2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <a :href="'/' + kodeMk" class="btn btn-outline-primary btn-lg px-5 rounded-pill shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Modul
                </a>
            </div>

        </div>
    </div>
</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.utPeringkatIdMk = <?= json_encode($idMk) ?>;
    window.utPeringkatKodeMk = <?= json_encode($kodeMk) ?>;
    window.utPeringkatModul = <?= json_encode($modul) ?>;
</script>
<script src="/js/peringkat.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
<?= $this->extend('layout/admin_layout') ?>

<?= $this->section('extra_styles') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="stylesheet" href="/css/admin/dashboard.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<main class="container content-wrapper mb-5 flex-grow-1">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">
                <i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard
            </h2>
            <p class="text-secondary small mt-1">Statistik Aplikasi UT Tes Formatif</p>
        </div>
        <div class="d-none d-md-flex align-items-center gap-4">
            <div class="d-flex align-items-center bg-white px-3 py-1 rounded-pill shadow-sm border">
                <div class="spinner-grow text-success me-2" role="status" style="width: 12px; height: 12px; animation-duration: 1.5s;"></div>
                <span class="fw-bolder text-dark me-1" style="font-size: 1.1rem;"><?= isset($onlineUsersCount) ? number_format($onlineUsersCount, 0, ',', '.') : 0 ?></span>
                <span class="text-secondary fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Users Online</span>
            </div>
            <span class="badge bg-dark px-3 py-2 rounded-pill shadow-sm">
                <i class="bi bi-shield-lock-fill text-warning me-1"></i> Mode : Admin
            </span>
        </div>
    </div>

    <div class="row g-3 g-md-4 mb-4">
        <div class="col-12">
            <!-- Mode Akses Pengunjung Toggle (Vue 3) -->
            <div id="appMaintenance" class="mb-0">
                <div class="card admin-card border-0 shadow-sm transition-all" :class="isMaintenance ? 'bg-danger-subtle border-danger border-start border-4' : (isComingSoon ? 'bg-warning-subtle border-warning border-start border-4' : 'bg-white')">
                    <div class="card-body p-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" :class="isMaintenance ? 'bg-danger text-white' : (isComingSoon ? 'bg-warning text-dark' : 'bg-light text-secondary')" style="width: 48px; height: 48px;">
                                <i class="fs-4" :class="isMaintenance ? 'bi bi-tools' : (isComingSoon ? 'bi bi-rocket-takeoff-fill' : 'bi bi-shield-check')"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0" :class="isMaintenance ? 'text-danger' : (isComingSoon ? 'text-warning-emphasis' : 'text-dark')">Mode Akses Pengunjung Publik</h6>
                                    <span class="badge" :class="isMaintenance ? 'bg-danger' : (isComingSoon ? 'bg-warning text-dark' : 'bg-success')">
                                        {{ isMaintenance ? 'MAINTENANCE AKTIF' : (isComingSoon ? 'COMING SOON AKTIF' : 'NORMAL') }}
                                    </span>
                                </div>
                                <small class="text-secondary" v-if="!isMaintenance && !isComingSoon">Sistem berjalan normal. Akses pengunjung publik terbuka penuh.</small>
                                <small class="text-danger fw-bold" v-else-if="isMaintenance"><i class="bi bi-exclamation-triangle-fill me-1"></i> AKTIF! Pengunjung diarahkan ke halaman pemeliharaan (production.php).</small>
                                <small class="text-warning-emphasis fw-bold" v-else-if="isComingSoon"><i class="bi bi-stars me-1"></i> AKTIF! Pengunjung diarahkan ke halaman Segera Hadir (coming_soon.php).</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <!-- 1. Toggle Coming Soon (Ditempatkan SEBELUM Toggle Maintenance) -->
                            <div class="d-flex align-items-center gap-2 bg-white px-3 py-2 rounded-3 border shadow-sm">
                                <i class="bi bi-rocket-takeoff-fill text-warning fs-5"></i>
                                <label class="form-check-label small fw-bold mb-0 text-dark" for="comingSoonSwitch" style="cursor:pointer;">
                                    Coming Soon
                                </label>
                                <div class="form-check form-switch fs-4 mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="comingSoonSwitch" v-model="isComingSoon" @change="toggleComingSoon" style="cursor:pointer;" title="Toggle Coming Soon Mode">
                                </div>
                            </div>
                            <!-- 2. Toggle Maintenance Mode -->
                            <div class="d-flex align-items-center gap-2 bg-white px-3 py-2 rounded-3 border shadow-sm">
                                <i class="bi bi-tools text-danger fs-5"></i>
                                <label class="form-check-label small fw-bold mb-0 text-dark" for="maintenanceSwitch" style="cursor:pointer;">
                                    Maintenance
                                </label>
                                <div class="form-check form-switch fs-4 mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="maintenanceSwitch" v-model="isMaintenance" @change="toggleMaintenance" style="cursor:pointer;" title="Toggle Maintenance Mode">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <a href="/admin/users" class="text-decoration-none d-block h-100">
                <div class="card metric-card bg-gradient-primary h-100 p-3 p-md-4" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="bi bi-people-fill metric-icon"></i>
                    <div class="position-relative z-1">
                        <div class="metric-value"><?= number_format($totalMahasiswa ?? 0, 0, ',', '.') ?></div>
                        <div class="small fw-semibold text-white-50 text-uppercase metric-title">Total Mahasiswa</div>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-6 col-lg-3">
            <a href="/admin/matakuliah" class="text-decoration-none d-block h-100">
                <div class="card metric-card bg-gradient-success h-100 p-3 p-md-4" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="bi bi-journals metric-icon"></i>
                    <div class="position-relative z-1">
                        <div class="metric-value"><?= number_format($totalMk ?? 0, 0, ',', '.') ?></div>
                        <div class="small fw-semibold text-white-50 text-uppercase metric-title">Mata Kuliah</div>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-6 col-lg-3">
            <a href="/admin/skor" class="text-decoration-none d-block h-100">
                <div class="card metric-card bg-gradient-warning h-100 p-3 p-md-4" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="bi bi-card-checklist metric-icon text-white"></i>
                    <div class="position-relative z-1">
                        <div class="metric-value text-white"><?= number_format($totalUjian ?? 0, 0, ',', '.') ?></div>
                        <div class="small fw-bold text-white-50 text-uppercase metric-title">Ujian Selesai</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card metric-card bg-gradient-danger h-100 p-3 p-md-4">
                <i class="bi bi-cloud-arrow-down-fill metric-icon"></i>
                <div class="position-relative z-1">
                    <div class="metric-value"><?= number_format($totalUnduhan ?? 0, 0, ',', '.') ?></div>
                    <div class="small fw-semibold text-white-50 text-uppercase metric-title">Unduhan APK</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Chart Pendaftar Baru (Vue 3) -->
    <div class="row g-4 mb-4" id="appPendaftar">
        <div class="col-12">
            <div class="card admin-card h-100 border-0 shadow-sm chart-card">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-person-plus-fill text-primary me-2"></i>Pendaftar Baru
                    </h5>
                    <select v-model="filterWaktu" @change="fetchDataChart" class="form-select form-select-sm w-auto shadow-none border-primary text-primary fw-semibold">
                        <option value="7">7 Hari Terakhir</option>
                        <option value="14">14 Hari Terakhir</option>
                        <option value="30">30 Hari Terakhir</option>
                        <option value="semua">Semua Waktu</option>
                    </select>
                </div>
                <div class="card-body p-4 position-relative">
                    <div v-if="isLoadingPendaftar" class="position-absolute top-50 start-50 translate-middle z-1">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Memuat...</span>
                        </div>
                    </div>
                    <div style="height: 350px; width: 100%;">
                        <canvas id="chartPendaftar"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Charts 1 & 2 -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card admin-card h-100 border-0 shadow-sm chart-card">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Tren Aktivitas</h5>
                </div>
                <div class="card-body p-4">
                    <canvas id="trenChart" class="chart-canvas"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card admin-card h-100 border-0 shadow-sm chart-card">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-pie-chart-fill text-success me-2"></i>Proporsi Ujian</h5>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center">
                    <canvas id="proporsiChart" class="chart-canvas"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Charts 3 & 4 -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card admin-card h-100 border-0 shadow-sm chart-card">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-fill text-warning me-2"></i>Rata-Rata Skor per MK</h5>
                </div>
                <div class="card-body p-4">
                    <canvas id="avgChart" class="chart-canvas"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card admin-card h-100 border-0 shadow-sm chart-card">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-reception-4 text-info me-2"></i>Distribusi Rentang Nilai</h5>
                </div>
                <div class="card-body p-4">
                    <canvas id="sebaranChart" class="chart-canvas"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Chart 5 -->
    <div class="row g-4 mb-4">
        
        <div class="col-12">
            <div class="card admin-card h-100 border-0 shadow-sm chart-card">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Top 5 MK Terfavorit
                    </h5>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center">
                    <canvas id="favoritChart" class="chart-canvas-full"></canvas>
                </div>
            </div>
        </div>

    </div>

    <!-- Row Saran -->
    <div class="row g-4 mb-4">
        
        <div class="col-12">
            <div class="card admin-card h-100 border-0 shadow-sm chart-card">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-chat-left-dots-fill text-success me-2"></i>Saran Baru
                    </h5>
                    <span class="badge badge-soft-danger badge-pill"><?= count($saranTerbaru ?? []) ?> Belum Dibaca</span>
                </div>
                <div class="card-body p-4 overflow-auto">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th width="30%">Pengirim</th>
                                    <th width="50%">Masukan</th>
                                    <th width="20%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($saranTerbaru)) : ?>
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">
                                        <i class="bi bi-emoji-smile fs-1 d-block mb-2 opacity-50"></i>
                                        Belum ada saran atau masukan baru.
                                    </td>
                                </tr>
                                <?php else : ?>
                                    <?php foreach ($saranTerbaru as $saran) : ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= esc($saran['nama_user'] ?? 'Dihapus') ?></div>
                                            <small class="text-muted"><?= esc($saran['no_hp'] ?? '-') ?></small>
                                        </td>
                                        <td>
                                            <?php 
                                                $badgeColor = 'badge-soft-secondary';
                                                if ($saran['kategori'] == 'bug') $badgeColor = 'badge-soft-danger';
                                                if ($saran['kategori'] == 'fitur') $badgeColor = 'badge-soft-info';
                                            ?>
                                            <span class="badge <?= $badgeColor ?> badge-pill mb-2 text-uppercase saran-badge"><?= esc($saran['kategori']) ?></span>
                                            <p class="mb-1 text-dark saran-text"><?= esc($saran['isi_saran']) ?></p>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-action border shadow-sm text-success" title="Tandai Dibaca">
                                                <i class="bi bi-check2-all"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- CDN Vue 3 -->
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<script>
    // Pass PHP data to external JS
    window.dashboardData = {
        trenLabels: <?= isset($chartTrenLabels) ? $chartTrenLabels : '[]' ?>,
        trenData: <?= isset($chartTrenData) ? $chartTrenData : '[]' ?>,
        propLabels: <?= isset($chartPropLabels) ? $chartPropLabels : '[]' ?>,
        propData: <?= isset($chartPropData) ? $chartPropData : '[]' ?>,
        avgLabels: <?= isset($chartAvgLabels) ? $chartAvgLabels : '[]' ?>,
        avgData: <?= isset($chartAvgData) ? $chartAvgData : '[]' ?>,
        sebaranLabels: <?= isset($chartSebaranLabels) ? $chartSebaranLabels : '[]' ?>,
        sebaranData: <?= isset($chartSebaranData) ? $chartSebaranData : '[]' ?>,
        favLabels: <?= isset($chartFavLabels) ? $chartFavLabels : '[]' ?>,
        favData: <?= isset($chartFavData) ? $chartFavData : '[]' ?>
    };
</script>
<script src="/js/admin/dashboard.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
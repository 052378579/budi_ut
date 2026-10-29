<?= $this->extend('layout/admin_layout') ?>

<?= $this->section('content') ?>
<main class="container content-wrapper mb-5 flex-grow-1" id="statistikApp">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Statistik 10 Nama Kota Terbanyak</h2>
            <p class="text-secondary small mt-1">Pemetaan lokasi pengunjung berdasarkan data IP Geolocation.</p>
        </div>
    </div>

    <div class="card admin-card border-0 mb-4 shadow-sm">
        <div class="card-body">
            <div v-if="loading" class="text-center py-5 text-muted">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Memuat data...</span>
                </div>
                <p class="mt-2">Mengambil data statistik...</p>
            </div>
            <div v-else-if="error" class="alert alert-danger text-center">
                {{ error }}
            </div>
            <div v-else>
                <canvas id="cityChart" height="100"></canvas>
            </div>
        </div>
    </div>
</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Memastikan Vue dideklarasikan dari global layout (jika belum, menggunakan Vue global)
    const { createApp } = Vue;

    createApp({
        data() {
            return {
                adminUser: null,
                logoutModalInstance: null,
                loading: true,
                error: null,
                cities: [],
                totals: [],
                chartInstance: null
            };
        },
        mounted() {
            this.checkLoginState();
            this.fetchStatistics();
        },
        methods: {
            checkLoginState() {
                const savedUser = localStorage.getItem('ut_user_session');
                if (savedUser) {
                    this.adminUser = JSON.parse(savedUser);
                    if (this.adminUser.role !== 'admin' && this.adminUser.id_user != 1) {
                        window.location.href = '/';
                    }
                } else {
                    window.location.href = '/';
                }
            },
            logout() {
                if (!this.logoutModalInstance) {
                    this.logoutModalInstance = new bootstrap.Modal(document.getElementById('logoutModal'));
                }
                this.logoutModalInstance.show();
            },
            async confirmLogout() {
                if (this.adminUser) {
                    try {
                        await fetch('/api/auth/keluar', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ nama_user: this.adminUser.nama_user })
                        });
                    } catch (e) {}
                }
                localStorage.removeItem('ut_user_session');
                window.location.href = '/';
            },
            fetchStatistics() {
                // Sesuaikan dengan Routes Anda, misalnya /api/statistik/topCities
                fetch('/api/statistik/topCities')
                    .then(response => {
                        if (!response.ok) throw new Error('Gagal mengambil data statistik');
                        return response.json();
                    })
                    .then(res => {
                        this.loading = false;
                        if (res.status === 'success' && res.data) {
                            this.cities = res.data.map(item => item.kota);
                            this.totals = res.data.map(item => item.total);
                            this.$nextTick(() => {
                                this.renderChart();
                            });
                        } else {
                            throw new Error('Data tidak valid');
                        }
                    })
                    .catch(err => {
                        this.loading = false;
                        this.error = err.message;
                    });
            },
            renderChart() {
                const ctx = document.getElementById('cityChart').getContext('2d');
                if (this.chartInstance) {
                    this.chartInstance.destroy();
                }
                
                this.chartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.cities,
                        datasets: [{
                            label: 'Jumlah Aktivitas/Kunjungan',
                            data: this.totals,
                            backgroundColor: 'rgba(13, 110, 253, 0.7)',
                            borderColor: 'rgba(13, 110, 253, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        }
    }).mount('#ut');
</script>
<?= $this->endSection() ?>

const { createApp } = Vue;

createApp({
    data() {
        return {
            adminUser: null, 
            logoutModalInstance: null,
            imageVersion: Date.now(),
            filterWaktu: '7',
            isLoadingPendaftar: false,
            isMaintenance: false,
            isComingSoon: false
        }
    },
    computed: {
        navThemeColor() { 
            return 'bg-dark'; 
        }
    },
    methods: {
        checkLoginState() {
            const savedUser = localStorage.getItem('ut_user_session');
            if (savedUser) {
                this.adminUser = JSON.parse(savedUser);
                
                if (this.adminUser.role !== 'admin' && this.adminUser.id_user != 1) {
                    alert('Akses Ditolak! Anda bukan Administrator.');
                    window.location.href = '/';
                }
            } else {
                window.location.href = '/';
            }
        },
        
        // FUNGSI INISIALISASI GRAFIK CHART.JS
        initCharts() {
            // Mengambil data yang diinjeksi dari PHP melalui window.dashboardData
            const dbData = window.dashboardData || {};

            // 1. Tren Aktivitas (Line Chart)
            const trenLabels = dbData.trenLabels || [];
            const trenData = dbData.trenData || [];
            if(trenLabels.length > 0) {
                new Chart(document.getElementById('trenChart'), {
                    type: 'line',
                    data: {
                        labels: trenLabels,
                        datasets: [{
                            label: 'Jumlah Ujian Selesai',
                            data: trenData,
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            // 2. Proporsi Ujian (Doughnut)
            const propLabels = dbData.propLabels || [];
            const propData = dbData.propData || [];
            if(propLabels.length > 0) {
                new Chart(document.getElementById('proporsiChart'), {
                    type: 'doughnut',
                    data: {
                        labels: propLabels,
                        datasets: [{
                            data: propData,
                            backgroundColor: ['#10b981', '#f59e0b'],
                            borderWidth: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '70%' }
                });
            }

            // 3. Rata-Rata Skor per MK (Bar Chart)
            const avgLabels = dbData.avgLabels || [];
            const avgData = dbData.avgData || [];
            if(avgLabels.length > 0) {
                new Chart(document.getElementById('avgChart'), {
                    type: 'bar',
                    data: {
                        labels: avgLabels,
                        datasets: [{
                            label: 'Rata-Rata Skor',
                            data: avgData,
                            backgroundColor: '#0ea5e9',
                            borderRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            // 4. Distribusi Rentang Nilai (Bar Chart)
            const sebaranLabels = dbData.sebaranLabels || [];
            const sebaranData = dbData.sebaranData || [];
            if(sebaranLabels.length > 0) {
                new Chart(document.getElementById('sebaranChart'), {
                    type: 'bar',
                    data: {
                        labels: sebaranLabels,
                        datasets: [{
                            label: 'Jumlah Mahasiswa',
                            data: sebaranData,
                            backgroundColor: '#8b5cf6',
                            borderRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            // 5. Top 5 MK Favorit (Horizontal Bar Chart)
            const favLabels = dbData.favLabels || [];
            const favData = dbData.favData || [];
            if(favLabels.length > 0) {
                new Chart(document.getElementById('favoritChart'), {
                    type: 'bar',
                    data: {
                        labels: favLabels,
                        datasets: [{
                            label: 'Difavoritkan',
                            data: favData,
                            backgroundColor: '#f43f5e',
                            borderRadius: 4
                        }]
                    },
                    options: { 
                        indexAxis: 'y', 
                        responsive: true, 
                        maintainAspectRatio: false 
                    }
                });
            }
        },

        // --- MANAJEMEN AKSES PENGUNJUNG (MAINTENANCE & COMING SOON) ---
        async fetchMaintenanceStatus() {
            try {
                const response = await fetch('/api/pengaturan/maintenance');
                const result = await response.json();
                if (result.status === 'success') {
                    this.isMaintenance = result.data.maintenance_mode;
                    this.isComingSoon = result.data.coming_soon_mode;
                }
            } catch (error) {
                console.error("Gagal memuat status pengaturan:", error);
            }
        },

        async toggleMaintenance() {
            try {
                const response = await fetch('/api/pengaturan/maintenance', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ status: this.isMaintenance })
                });
                const result = await response.json();
                
                if (result.status === 'success') {
                    if (this.isMaintenance) {
                        this.isComingSoon = false;
                    }
                } else {
                    alert('Gagal memperbarui status maintenance.');
                    this.isMaintenance = !this.isMaintenance;
                }
            } catch (error) {
                console.error("Gagal memperbarui maintenance:", error);
                alert('Terjadi kesalahan jaringan.');
                this.isMaintenance = !this.isMaintenance;
            }
        },

        async toggleComingSoon() {
            try {
                const response = await fetch('/api/pengaturan/coming-soon', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ status: this.isComingSoon })
                });
                const result = await response.json();
                
                if (result.status === 'success') {
                    if (this.isComingSoon) {
                        this.isMaintenance = false;
                    }
                } else {
                    alert('Gagal memperbarui status coming soon.');
                    this.isComingSoon = !this.isComingSoon;
                }
            } catch (error) {
                console.error("Gagal memperbarui coming soon:", error);
                alert('Terjadi kesalahan jaringan.');
                this.isComingSoon = !this.isComingSoon;
            }
        },

        async fetchDataChart() {
            this.isLoadingPendaftar = true;
            try {
                const response = await fetch(`/api/dashboard/chart-pendaftar?filter=${this.filterWaktu}`);
                const result = await response.json();
                
                if (result.status === 200) {
                    this.renderChartPendaftar(result.data);
                }
            } catch (error) {
                console.error("Gagal memuat data grafik:", error);
            } finally {
                this.isLoadingPendaftar = false;
            }
        },
        renderChartPendaftar(chartData) {
            const ctx = document.getElementById('chartPendaftar');
            
            if (this.chartPendaftarInstance) {
                this.chartPendaftarInstance.destroy();
            }

            this.chartPendaftarInstance = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { mode: 'index', intersect: false, padding: 10, cornerRadius: 8 }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 },
                            grid: { borderDash: [5, 5] }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                maxTicksLimit: 8,
                                maxRotation: 45,
                                minRotation: 0
                            }
                        }
                    },
                    interaction: { mode: 'nearest', axis: 'x', intersect: false }
                }
            });
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
        }
    },
    mounted() {
        this.checkLoginState();
        this.fetchMaintenanceStatus(); // Ambil status awal saat dimuat
        this.fetchDataChart();
        this.$nextTick(() => {
            this.initCharts();
        });
    }
}).mount('#ut');

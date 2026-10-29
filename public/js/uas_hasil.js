const { createApp } = Vue;

const app = createApp({
    data() {
        return {
            kodeMk: window.utHasilKodeMk || '',
            score: 0,
            total: 0,
            results: [],
            currentReviewIdx: 0,

            currentUser: null,
            logoutModalInstance: null
        }
    },
    computed: {
        // Sinkronisasi komponen header template global
        navThemeColor() { return 'bg-dark'; },
        courseName() { return `Hasil Simulasi UAS - ${this.kodeMk}`; },
        isLightTheme() {
            const theme = this.navThemeColor || '';
            const lightThemes = [
                'bg-light', 'bg-white', 'bg-warning', 'bg-info', 'bg-transparent',
                'bg-primary-subtle', 'bg-secondary-subtle', 'bg-success-subtle',
                'bg-danger-subtle', 'bg-warning-subtle', 'bg-info-subtle',
                'bg-light-subtle', 'bg-dark-subtle', 'bg-body', 'bg-body-secondary', 'bg-body-tertiary'
            ];
            return lightThemes.includes(theme);
        },

        percentage() {
            return this.total > 0 ? ((this.score / this.total) * 100).toFixed(2) : '0.00';
        },
        passingStatus() {
            return parseFloat(this.percentage) >= 70 ? 'LULUS EVALUASI' : 'PERLU BELAJAR LAGI';
        }
    },
    methods: {
        checkLoginState() {
            const savedUser = localStorage.getItem('ut_user_session');
            if (savedUser) {
                this.currentUser = JSON.parse(savedUser);
            } else {
                window.location.href = '/';
            }
        },

        loadResultsFromSession() {
            // Membaca transaksi lembar jawaban dari localStorage yang disinkronisasi oleh sesi simulasi
            const savedData = localStorage.getItem(`uas_result_${this.kodeMk}`);

            if (savedData) {
                const parsedData = JSON.parse(savedData);
                this.score = parsedData.skorBenar;
                this.total = parsedData.totalSoal;
                this.results = parsedData.detailJawaban;
            } else {
                // Proteksi jika mahasiswa mencoba melompati rute tanpa menempuh ujian
                alert("Data hasil ujian tidak ditemukan. Anda akan dialihkan ke halaman utama.");
                window.location.href = '/';
            }
        },

        getOptionClass(item, optKey) {
            const currentOptValue = item['opsi' + optKey];

            // Aturan 1: Jika teks opsi ini adalah kunci jawaban yang benar, warnai hijau
            if (currentOptValue === item.jawabanBenar) {
                return 'option-correct';
            }

            // Aturan 2: Jika opsi ini dipilih oleh user dan salah, warnai merah
            if (item.jawabanUser === currentOptValue && !item.isCorrect) {
                return 'option-wrong';
            }

            return '';
        },

        nextReview() {
            if (this.currentReviewIdx < this.results.length - 1) {
                this.currentReviewIdx++;
            }
        },

        prevReview() {
            if (this.currentReviewIdx > 0) {
                this.currentReviewIdx--;
            }
        },

        logout() {
            if (!this.logoutModalInstance) {
                this.logoutModalInstance = new bootstrap.Modal(document.getElementById('logoutModal'));
            }
            this.logoutModalInstance.show();
        },

        async confirmLogout() {
            // SINKRONISASI TELEGRAM: Kirim request POST ke server agar memicu Bot Telegram
            if (this.currentUser) {
                try {
                    await fetch('/api/auth/keluar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ nama_user: this.currentUser.nama_user })
                    });
                } catch (error) {
                    console.error("Gagal mengirim log logout ke server Telegram:", error);
                }
            }

            // Bersihkan sesi memori lokal browser dan redirect ke halaman utama
            localStorage.removeItem('ut_user_session');
            this.currentUser = null;

            if (this.logoutModalInstance) {
                this.logoutModalInstance.hide();
            }

            window.location.href = '/';
        }
    },
    mounted() {
        this.checkLoginState();
        this.loadResultsFromSession();
    }
});

app.mount('#ut');

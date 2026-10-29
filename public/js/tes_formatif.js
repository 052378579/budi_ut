const { createApp } = Vue;

const app = createApp({
    data() {
        return {
            kodeMk: window.utFormatifKodeMk || '',
            modul: window.utFormatifModul || 1,
            
            mk: {},
            soal: [],
            currentIndex: 0,
            userAnswers: [],
            
            loadingData: true,
            quizStarted: true, 
            quizFinished: false,
            finalScore: 0,
            
            isSubmitting: false,
            submitSuccess: false,
            submitError: '',
            
            currentUser: null,
            logoutModalInstance: null,
            imageVersion: new Date().getTime()
        }
    },
    computed: {
        navThemeColor() { return this.mk.theme_color || 'bg-primary'; },
        courseName() { return this.mk.nama_mk || this.kodeMk; },
        currentSoal() { return this.soal[this.currentIndex] || {}; },
        progress() { return this.soal.length > 0 ? ((this.currentIndex + 1) / this.soal.length) * 100 : 0; },
        isLightTheme() {
            const theme = this.navThemeColor || '';
            const lightThemes = [
                'bg-light', 'bg-white', 'bg-warning', 'bg-info', 'bg-transparent',
                'bg-primary-subtle', 'bg-secondary-subtle', 'bg-success-subtle',
                'bg-danger-subtle', 'bg-warning-subtle', 'bg-info-subtle',
                'bg-light-subtle', 'bg-dark-subtle', 'bg-body', 'bg-body-secondary', 'bg-body-tertiary'
            ];
            return lightThemes.includes(theme);
        }
    },
    methods: {
        handleLogoError(event) { event.target.style.display = 'none'; },
        
        checkLoginState() {
            const savedUser = localStorage.getItem('ut_user_session');
            if (savedUser) { 
                this.currentUser = JSON.parse(savedUser); 
            } else { 
                alert("Akses ditolak. Sesi Anda telah berakhir.");
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
        },

        // RE-DESAIN BARU: Fetch Berantai (Chain Loading)
        async loadMetadataAndSoal() {
            this.loadingData = true;
            try {
                // 1. Ambil Metadata Mata Kuliah
                const resMeta = await fetch(`/api/mk/show/${this.kodeMk}`);
                if (!resMeta.ok) throw new Error('Gagal memuat metadata mata kuliah');
                this.mk = await resMeta.json();

                // 2. Ambil Berkas Soal menggunakan KODE BMP dari metadata
                const resSoal = await fetch(`/api/dokumen/soal/${this.mk.kode_bmp}/${this.modul}`);
                
                if (resSoal.ok) {
                    this.soal = await resSoal.json();
                } else {
                    // Toleransi jika file modul belum diunggah admin
                    this.soal = [];
                    console.warn("Berkas soal JSON untuk modul ini belum tersedia di server.");
                }
            } catch (error) {
                console.error("Kesalahan inisialisasi kuis:", error);
                alert("Gagal memuat data. Pastikan Anda terhubung ke jaringan internet.");
            } finally {
                this.loadingData = false;
            }
        },
        
        selectAnswer(key) {
            // Mencegah penggantian jawaban
            if (this.userAnswers[this.currentIndex] !== undefined) return; 
            this.userAnswers[this.currentIndex] = key;
            this.userAnswers = [...this.userAnswers]; 
        },

        getOptionClass(key) {
            const selectedAnswer = this.userAnswers[this.currentIndex];
            const correctAnswer = this.currentSoal.jawaban;

            if (selectedAnswer === undefined) return ''; 
            
            // Pembahasan Instan Visual
            if (key === correctAnswer) return 'bg-success text-white border-success shadow-sm fw-bold';
            if (key === selectedAnswer) return 'bg-danger text-white border-danger shadow-sm fw-bold';
            
            return 'opacity-50 bg-light text-muted';
        },

        nextSoal() { 
            if (this.currentIndex < this.soal.length - 1) this.currentIndex++; 
        },
        
        prevSoal() { 
            if (this.currentIndex > 0) this.currentIndex--; 
        },
        
        async finishQuiz() {
            this.isSubmitting = true;
            this.submitError = '';
            
            // Kalkulasi Nilai
            let correct = 0;
            this.soal.forEach((s, index) => {
                if (this.userAnswers[index] === s.jawaban) correct++;
            });
            
            // Konversi persentase untuk decimal(5,2)
            this.finalScore = (correct / this.soal.length) * 100;
            this.quizFinished = true;
            
            if (!this.currentUser || !this.currentUser.id_user) {
                this.submitError = 'Gagal menyimpan skor: Identitas Anda tidak ditemukan. Silakan masuk ulang dari beranda.';
                this.isSubmitting = false;
                return;
            }

            const payload = {
                id_user: this.currentUser.id_user,
                id_mk: this.mk.id_mk,
                modul_ke: this.modul,
                skor: this.finalScore
            };

            try {
                const response = await fetch('/api/skor/create', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });
                
                const result = await response.json();
                
                if (response.ok && result.status) {
                    this.submitSuccess = true;
                } else {
                    let errorMsg = result.message || "Gagal menyimpan skor ke pangkalan data.";
                    if (result.messages) errorMsg = Object.values(result.messages)[0];
                    this.submitError = errorMsg;
                }
            } catch (error) {
                console.error("Kesalahan pengiriman POST:", error);
                this.submitError = "Koneksi terputus. Gagal mengirim riwayat ke server.";
            } finally {
                this.isSubmitting = false;
            }
        },
        
        resetQuiz() { 
            window.location.reload(); 
        }
    },
    mounted() {
        this.checkLoginState();
        this.loadMetadataAndSoal();
    }
});

app.config.compilerOptions.isCustomElement = (tag) => tag === 'g-emoji';
app.mount('#ut');

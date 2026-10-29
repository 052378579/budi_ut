const { createApp } = Vue;

createApp({
    data() {
        return {
            kodeMk: window.utSimulasiKodeMk || '',
            mkData: {},
            modul: 'Simulasi UAS', 
            
            loading: true,
            quizQuestions: [],
            userAnswers: {},
            currentIdx: 0,
            
            // Nilai default awal, nantinya akan ditimpa oleh durasi dinamis sesuai jumlah SKS
            timeLeft: 3600, 
            timer: null,
            isSubmitting: false,
            
            currentUser: null,
            logoutModalInstance: null,
            submitModalInstance: null,
            confirmMessage: ''
        }
    },
    computed: {
        navThemeColor() { return this.mkData.theme_color || 'bg-primary'; },
        courseName() { return this.mkData.nama_mk || `Simulasi UAS - ${this.kodeMk}`; },
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
        
        totalQuestions() { return this.quizQuestions.length; },
        currentSoal() { return this.quizQuestions[this.currentIdx]; },
        progress() { return this.totalQuestions > 0 ? ((this.currentIdx + 1) / this.totalQuestions) * 100 : 0; },
        
        formattedTime() {
            const m = Math.floor(this.timeLeft / 60);
            const s = this.timeLeft % 60;
            return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }
    },
    methods: {
        handleLogoError(event) { event.target.style.display = 'none'; },

        checkLoginState() {
            const savedUser = localStorage.getItem('ut_user_session');
            if (savedUser) {
                this.currentUser = JSON.parse(savedUser);
            } else {
                alert("Akses ditolak! Sesi Anda tidak valid. Silakan login kembali.");
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
            // SINKRONISASI TELEGRAM: Kirim request logout ke server sebelum redirect
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

            // Bersihkan sesi lokal browser dan redirect ke beranda
            localStorage.removeItem('ut_user_session'); 
            this.currentUser = null;
            if (this.logoutModalInstance) { 
                this.logoutModalInstance.hide(); 
            }
            window.location.href = '/'; 
        },

        async fetchUasQuestions() {
            this.loading = true;
            try {
                const res = await fetch(`/api/mk/uas-questions/${this.kodeMk}`);
                if (!res.ok) throw new Error('Gagal mengambil data soal UAS dari server.');
                const data = await res.json();
                
                if (data && data.length > 0) {
                    this.quizQuestions = data;
                    
                    // Tarik metadata MK dari database untuk menentukan durasi waktu
                    const resMeta = await fetch(`/api/mk/show/${this.kodeMk}`);
                    if (resMeta.ok) {
                        this.mkData = await resMeta.json();
                        
                        // Durasi pengerjaan dinamis berdasarkan bobot SKS
                        const sksNum = parseInt(this.mkData.jumlah_sks) || 3; 
                        
                        if (sksNum === 2) {
                            this.timeLeft = 60 * 60;  // 60 Menit = 3600 Detik
                        } else if (sksNum === 4) {
                            this.timeLeft = 120 * 60; // 120 Menit = 7200 Detik
                        } else {
                            this.timeLeft = 90 * 60;  // 3 SKS / Lainnya = 90 Menit = 5400 Detik
                        }
                    }
                    
                    // Menjalankan pewaktu ujian
                    this.startTimer();
                } else {
                    this.quizQuestions = [];
                }
            } catch (e) {
                console.error("Kesalahan saat memuat soal UAS:", e);
                this.quizQuestions = [];
            } finally {
                this.loading = false;
            }
        },

        startTimer() {
            if (this.timer) clearInterval(this.timer);

            this.timer = setInterval(() => {
                if (this.timeLeft > 0) {
                    this.timeLeft--;
                } else {
                    clearInterval(this.timer);
                    alert("Waktu ujian telah habis! Seluruh jawaban Anda akan otomatis dikirimkan ke server evaluasi.");
                    this.submitUas();
                }
            }, 1000);
        },

        pilihJawaban(key) { 
            this.userAnswers[this.currentIdx] = key; 
            this.userAnswers = { ...this.userAnswers }; 
        },

        nextSoal() {
            if (this.currentIdx < this.totalQuestions - 1) {
                this.currentIdx++;
            }
        },
        
        prevSoal() {
            if (this.currentIdx > 0) {
                this.currentIdx--;
            }
        },

        konfirmasiSubmit() {
            const dijawab = Object.keys(this.userAnswers).length;
            if (dijawab < this.totalQuestions) {
                this.confirmMessage = `Anda baru menjawab ${dijawab} dari ${this.totalQuestions} soal. Yakin ingin menyelesaikan ujian sekarang?`;
            } else {
                this.confirmMessage = "Yakin ingin menyelesaikan Simulasi UAS? Anda tidak dapat mengubah jawaban setelah ini.";
            }
            if (!this.submitModalInstance) {
                 this.submitModalInstance = new bootstrap.Modal(document.getElementById('submitConfirmModal'));
            }
            this.submitModalInstance.show();
        },

        eksekusiSubmit() {
            if (this.submitModalInstance) {
                this.submitModalInstance.hide();
            }
            this.submitUas();
        },

        async submitUas() {
            this.isSubmitting = true;
            clearInterval(this.timer);
            
            let skorBenar = 0;
            const results = this.quizQuestions.map((q, i) => {
                const jawabanUserKey = this.userAnswers[i];
                const isCorrect = jawabanUserKey === q.jawaban_benar;
                if (isCorrect) skorBenar++;
                
                return {
                    no: i + 1,
                    soal: q.pertanyaan,
                    jawabanUser: jawabanUserKey ? q.pilihan[jawabanUserKey] : 'Tidak Dijawab',
                    jawabanBenar: q.pilihan[q.jawaban_benar],
                    isCorrect: isCorrect,
                    opsiA: q.pilihan['A'] || '',
                    opsiB: q.pilihan['B'] || '',
                    opsiC: q.pilihan['C'] || '',
                    opsiD: q.pilihan['D'] || ''
                };
            });

            const nilaiAkhir = Math.round((skorBenar / this.totalQuestions) * 100);
            const hasilUas = {
                kodeMk: this.kodeMk,
                waktuSelesai: new Date().toISOString(),
                skorBenar: skorBenar,
                totalSoal: this.totalQuestions,
                nilaiAkhir: nilaiAkhir,
                detailJawaban: results
            };

            // SIMPAN KE BACKEND SERVER AGAR MUNCUL DI PERINGKAT UAS
            if (this.currentUser && this.mkData.id_mk) {
                const payload = {
                    id_user: this.currentUser.id_user,
                    id_mk: this.mkData.id_mk,
                    jenis_ujian: 'uas',
                    skor: nilaiAkhir
                };
                
                try {
                    await fetch('/api/skor/create', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload)
                    });
                } catch (err) {
                    console.error("Gagal mengirim skor UAS ke server:", err);
                }
            }

            localStorage.setItem(`uas_result_${this.kodeMk}`, JSON.stringify(hasilUas));
            window.location.href = `/${this.kodeMk}/uas-hasil`;
        }
    },
    mounted() {
        this.checkLoginState();
        if (this.currentUser) {
            this.fetchUasQuestions();
        }
    }
}).mount('#ut');

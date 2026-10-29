/* ==========================================================================
   BERANDA JAVASCRIPT (public/js/beranda.js)
   Menangani logika pencarian, filter, favorit, dan otentikasi login.
   ========================================================================== */
const { createApp } = Vue;

const app = createApp({
    mixins: [globalMixin], // Mewarisi fungsi Induk dari global_mixin.js
    data() {
        return {
            listMK: window.listMKData || [], // Ditarik dari global window PHP
            searchQuery: '',
            activeFilter: 'SEMUA',
            categories: ['SEMUA', 'FEB', 'FHISIP', 'FKIP', 'FST'],
            authModalInstance: null,
            isAuthenticating: false,
            authForm: { nama_user: '', no_hp: '', captchaAnswer: '' },
            captcha: { num1: 0, num2: 0, operator: '+', correctAnswer: 0 },
            favoritIds: [],
            isLoadingData: true,
            pengumumanQueue: [],
            currentPengumuman: null
        }
    },
    computed: {
        filteredList() {
            if (!this.listMK || !Array.isArray(this.listMK)) return [];
            let result = this.listMK.filter(mk => {
                const matchesSearch = mk.nama_mk.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                      mk.kode_mk.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                      mk.kode_bmp.toLowerCase().includes(this.searchQuery.toLowerCase());
                const matchesFilter = this.activeFilter === 'SEMUA' || 
                                      (mk.program_studi && mk.program_studi.includes(this.activeFilter));
                return matchesSearch && matchesFilter;
            });
            // Mengurutkan A-Z secara default berdasarkan nama mata kuliah
            return result.sort((a, b) => a.nama_mk.localeCompare(b.nama_mk));
        }
    },
    methods: {
        // Hook dari globalMixin saat pengguna terdeteksi Login
        onLoginSuccess() {
            this.fetchFavoritIds();
        },
        // Hook dari globalMixin saat pengguna belum Login
        onLoginFailed() {
            const modalElement = document.getElementById('authModal');
            if (modalElement) {
                this.authModalInstance = new bootstrap.Modal(modalElement);
                this.generateCaptcha();
                this.authModalInstance.show();
            }
        },
        // Hook dari globalMixin saat berhasil Logout
        onLogoutSuccess() {
            this.authForm.nama_user = '';
            this.authForm.no_hp = '';
            this.authForm.captchaAnswer = '';
            this.favoritIds = []; 
            if (!this.authModalInstance) {
                this.authModalInstance = new bootstrap.Modal(document.getElementById('authModal'));
            }
            this.generateCaptcha(); 
            this.authModalInstance.show();
        },

        openCourse(kode) { window.location.href = '/' + kode; },
        splitProdi(str) { return str ? str.split(',').map(s => s.trim()) : []; },
        
        generateCaptcha() {
            let n1 = Math.floor(Math.random() * 10);
            let n2 = Math.floor(Math.random() * 10);
            const operators = ['+', '-'];
            const selectedOperator = operators[Math.floor(Math.random() * operators.length)];

            if (selectedOperator === '-') {
                if (n1 < n2) { let temp = n1; n1 = n2; n2 = temp; }
                this.captcha.correctAnswer = n1 - n2;
            } else {
                this.captcha.correctAnswer = n1 + n2;
            }
            this.captcha.num1 = n1;
            this.captcha.num2 = n2;
            this.captcha.operator = selectedOperator;
            this.authForm.captchaAnswer = ''; 
        },
        sanitizePhoneInput() {
            this.authForm.no_hp = this.authForm.no_hp.replace(/[^0-9]/g, '');
        },
        validatePhoneNumber(phone) {
            if (!/^(08|628)/.test(phone)) { this.showToast("Format salah! Nomor HP diawali '08x'"); return false; }
            if (phone.length < 10 || phone.length > 13) { this.showToast("Nomor HP tidak Valid"); return false; }
            if (/([0-9])\1{5,}/.test(phone) || /(1234567|2345678|3456789|4567890)/.test(phone)) {
                this.showToast("Login ditolak! Nomor HP terindikasi spam"); return false;
            }
            return true;
        },

        async submitAuth() {
            if (!this.validatePhoneNumber(this.authForm.no_hp)) return; 
            
            // Validasi Input Captcha Kosong atau Salah
            if (this.authForm.captchaAnswer === '' || parseInt(this.authForm.captchaAnswer) !== this.captcha.correctAnswer) {
                this.showToast("Jawaban verifikasi keamanan salah! Coba lagi");
                this.generateCaptcha(); 
                return;
            }
            
            this.isAuthenticating = true;
            try {
                const response = await fetch('/api/auth/masuk', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nama_user: this.authForm.nama_user.trim(), no_hp: this.authForm.no_hp.trim() })
                });
                const result = await response.json();
                
                if (response.ok && result.status) {
                    this.currentUser = result.data;
                    localStorage.setItem('ut_user_session', JSON.stringify(result.data));
                    this.fetchFavoritIds();
                    if (this.authModalInstance) this.authModalInstance.hide();
                    setTimeout(() => { this.showToast(`Selamat datang, ${this.currentUser.nama_user}!`, 'success'); }, 300);
                } else {
                    const errorMsg = result.messages ? Object.values(result.messages)[0] : (result.message || "Terjadi kesalahan.");
                    this.showToast(errorMsg); 
                    this.generateCaptcha();
                }
            } catch (error) {
                this.showToast("Gagal terhubung ke server. Pastikan Anda online.");
            } finally {
                this.isAuthenticating = false;
            }
        },

        async fetchFavoritIds() {
            if (!this.currentUser) return;
            try {
                const response = await fetch('/api/favorit/ids/' + this.currentUser.id_user);
                if (response.status === 401 || response.status === 403) {
                    this.showToast("Sesi Anda telah berakhir. Silakan masuk kembali.");
                    this.confirmLogout(); return;
                }
                const result = await response.json();
                if (response.ok && result.status === 'success') {
                    this.favoritIds = result.data.map(id => Number(id));
                }
            } catch (error) {}
        },
        
        isFavorite(idMk) {
            return this.favoritIds.includes(Number(idMk));
        },
        
        async toggleFavorite(idMk) {
            if (!this.currentUser) { this.onLoginFailed(); return; }
            
            const id = Number(idMk);
            const index = this.favoritIds.indexOf(id);
            if (index !== -1) { this.favoritIds.splice(index, 1); } 
            else { this.favoritIds.push(id); }
            
            try {
                const response = await fetch('/api/favorit/toggle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_user: this.currentUser.id_user, id_mk: id })
                });
                if (response.status === 401 || response.status === 403) {
                    this.confirmLogout(); return;
                }
                const result = await response.json();
                if (!response.ok || result.status !== 'success') {
                    this.fetchFavoritIds(); 
                    this.showToast("Gagal mengubah favorit.");
                }
            } catch (error) {
                this.fetchFavoritIds(); 
                this.showToast("Koneksi terputus.");
            }
        },
        
        async fetchPengumuman() {
            try {
                const response = await fetch('/api/pengumuman/active');
                const result = await response.json();
                
                if (response.ok && result.status === 'success' && result.data.length > 0) {
                    const hariIni = new Date().toLocaleDateString('en-CA'); // Format: YYYY-MM-DD
                    
                    // Filter pengumuman yang belum dilihat hari ini
                    this.pengumumanQueue = result.data.filter(p => {
                        const key = `pengumuman_shown_${p.id_pengumuman}_${hariIni}`;
                        return !localStorage.getItem(key);
                    });
                    
                    if (this.pengumumanQueue.length > 0) {
                        // Menunggu authModal ditutup jika sedang terbuka (UX)
                        const authModalDiv = document.getElementById('authModal');
                        if (authModalDiv && authModalDiv.classList.contains('show')) {
                            authModalDiv.addEventListener('hidden.bs.modal', () => {
                                this.showNextPengumuman();
                            }, { once: true });
                        } else {
                            this.showNextPengumuman();
                        }
                    }
                }
            } catch (error) {
                console.error("Fetch Pengumuman Error:", error);
            }
        },
        showNextPengumuman() {
            if (this.pengumumanQueue.length > 0) {
                // Logika Jeda 1 Jam (3600000 ms)
                const lastClosedStr = localStorage.getItem('pengumuman_last_closed');
                const lastClosed = lastClosedStr ? parseInt(lastClosedStr) : 0;
                const sisaWaktu = (lastClosed + 3600000) - Date.now();

                // Jika belum 1 jam sejak pengumuman terakhir ditutup, tunggu sisa waktunya
                if (sisaWaktu > 0) {
                    setTimeout(() => {
                        this.showNextPengumuman();
                    }, sisaWaktu);
                    return; // Keluar dari eksekusi agar modal tidak dimunculkan sekarang
                }

                this.currentPengumuman = this.pengumumanQueue.shift();
                this.$nextTick(() => {
                    const modalEl = document.getElementById('pengumumanModal');
                    if (modalEl) {
                        let modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (!modalInstance) {
                            modalInstance = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
                            modalEl.addEventListener('hidden.bs.modal', () => {
                                const hariIni = new Date().toLocaleDateString('en-CA');
                                if (this.currentPengumuman) {
                                    const key = `pengumuman_shown_${this.currentPengumuman.id_pengumuman}_${hariIni}`;
                                    localStorage.setItem(key, 'true');
                                }
                                
                                // Simpan histori penutupan untuk memicu jeda 1 jam berikutnya
                                localStorage.setItem('pengumuman_last_closed', Date.now().toString());
                                
                                this.showNextPengumuman();
                            });
                        }
                        modalInstance.show();
                    }
                });
            } else {
                this.currentPengumuman = null;
            }
        },
        
        async fetchMataKuliah() {
            try {
                const response = await fetch('/api/mk');
                const result = await response.json();
                if (response.ok) {
                    this.listMK = result;
                } else {
                    this.showToast("Gagal memuat data mata kuliah dari server.");
                }
            } catch (error) {
                console.error("Fetch MK Error:", error);
                this.showToast("Koneksi terputus saat memuat data mata kuliah.");
            } finally {
                // Memberikan delay kecil agar skeleton tidak berkedip terlalu cepat jika internet kencang
                setTimeout(() => {
                    this.isLoadingData = false;
                }, 300);
            }
        }
    },
    mounted() {
        this.fetchMataKuliah();
        this.fetchPengumuman();
        this.checkLoginState();
        // Menangkap Pesan Flashdata dari PHP jika ada
        if (window.serverFlashError) {
            setTimeout(() => { this.showToast(window.serverFlashError); }, 300); 
        }
    }
});

// Mengabaikan tag custom bawaan Github Emoji dari parsing Vue
app.config.compilerOptions.isCustomElement = (tag) => tag === 'g-emoji';
app.mount('#ut');
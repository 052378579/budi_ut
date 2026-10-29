/* ==========================================================================
   VUE GLOBAL MIXIN (public/js/global_mixin.js)
   Menangani fungsionalitas inti yang dibagikan ke seluruh halaman aplikasi.
   ========================================================================== */
const globalMixin = {
    data() {
        return {
            currentUser: null,
            logoutModalInstance: null,
            navThemeColor: '', 
            imageVersion: new Date().getTime(),
            
            // Fungsionalitas Kotak Saran
            saranModalInstance: null,
            isSubmittingSaran: false,
            isLoggingOut: false,
            saranForm: {
                kategori: 'lainnya',
                isi_saran: ''
            },
            
            // Fungsionalitas Notifikasi Bawaan (Toast)
            toastMessage: '',
            toastType: 'error'
        }
    },
    computed: {
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
        handleImageError(event) { event.target.src = 'https://placehold.co/600x400/004da1/white?text=Cover+UT'; },
        
        // Pengecek Tema Kartu (Dinamis per MK)
        isCardLightTheme(theme) {
            if (!theme) return false;
            const lightThemes = [
                'bg-light', 'bg-white', 'bg-warning', 'bg-info', 'bg-transparent',
                'bg-primary-subtle', 'bg-secondary-subtle', 'bg-success-subtle',
                'bg-danger-subtle', 'bg-warning-subtle', 'bg-info-subtle',
                'bg-light-subtle', 'bg-dark-subtle', 'bg-body', 'bg-body-secondary', 'bg-body-tertiary'
            ];
            return lightThemes.includes(theme);
        },
        
        // Pemanggil Notifikasi Cerdas
        showToast(message, type = 'error') {
            this.toastMessage = message;
            this.toastType = type;
            this.$nextTick(() => {
                // Mencari elemen Toast di DOM (appToast untuk Beranda, profilToast untuk Profil)
                const el = document.getElementById('appToast') || document.getElementById('profilToast');
                if (el) {
                    const toast = bootstrap.Toast.getOrCreateInstance(el);
                    toast.show();
                } else {
                    alert(message); // Fallback jika HTML Toast tidak ada
                }
            });
        },
        
        // Pengecek Sesi Login Utama (Kini Divalidasi oleh Server)
        async checkLoginState() {
            const savedUser = localStorage.getItem('ut_user_session');
            if (savedUser) {
                try {
                    // PING KE SERVER: Pastikan sesi PHP backend benar-benar aktif!
                    const response = await fetch('/api/auth/validate');
                    if (!response.ok) throw new Error('Sesi Server Invalid');

                    const result = await response.json();
                    if (result.data) {
                        this.currentUser = result.data;
                        localStorage.setItem('ut_user_session', JSON.stringify(result.data));
                    } else {
                        this.currentUser = JSON.parse(savedUser);
                    }

                    // Memicu Hook khusus jika halaman memiliki fungsi yang harus dijalankan setelah login
                    if (typeof this.onLoginSuccess === 'function') this.onLoginSuccess();
                } catch (error) {
                    // Jika server menolak (401), hancurkan sesi lokal palsu/kadaluwarsa
                    localStorage.removeItem('ut_user_session');
                    this.currentUser = null;
                    alert('Keamanan: Sesi Anda telah berakhir atau tidak valid. Silakan masuk kembali.');
                    window.location.href = '/';
                }
            } else {
                if (typeof this.onLoginFailed === 'function') {
                    this.onLoginFailed();
                } else {
                    alert('Akses ditolak! Silakan masuk (login) terlebih dahulu.');
                    window.location.href = '/';
                }
            }
        },

        // --- MANAJEMEN MODAL KOTAK SARAN ---
        bukaModalSaran() {
            if (!this.saranModalInstance) {
                const el = document.getElementById('saranModal');
                if (el) this.saranModalInstance = new bootstrap.Modal(el);
            }
            this.saranForm.kategori = 'lainnya';
            this.saranForm.isi_saran = '';
            if (this.saranModalInstance) this.saranModalInstance.show();
        },
        async kirimSaran() {
            if (!this.currentUser) return;
            this.isSubmittingSaran = true;
            try {
                const response = await fetch('/api/saran/create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_user: this.currentUser.id_user,
                        kategori: this.saranForm.kategori,
                        isi_saran: this.saranForm.isi_saran
                    })
                });
                const result = await response.json();
                if (response.ok && result.status === 'success') {
                    if (this.saranModalInstance) this.saranModalInstance.hide();
                    this.showToast("Terima kasih! Saran Anda telah terkirim ke Tim Dev.", "success");
                } else {
                    this.showToast("Gagal mengirim saran: " + (result.message || "Kesalahan internal."));
                }
            } catch (error) {
                this.showToast("Koneksi terputus. Gagal menghubungi Server.");
            } finally {
                this.isSubmittingSaran = false;
            }
        },

        // --- MANAJEMEN LOGOUT (KELUAR) ---
        logout() {
            if (!this.logoutModalInstance) {
                const el = document.getElementById('logoutModal');
                if (el) this.logoutModalInstance = new bootstrap.Modal(el);
            }
            if (this.logoutModalInstance) this.logoutModalInstance.show();
        },
        async confirmLogout() {
            this.isLoggingOut = true;
            if (this.currentUser) {
                try {
                    await fetch('/api/auth/keluar', {
                        method: 'POST'
                    });
                } catch (error) {} // Abaikan error senyap pada webhook Telegram
            }
            localStorage.removeItem('ut_user_session'); 
            this.currentUser = null;
            if (this.logoutModalInstance) this.logoutModalInstance.hide();
            
            // Jika halaman punya respon khusus saat logout, eksekusi. Jika tidak, lempar ke Beranda.
            if (typeof this.onLogoutSuccess === 'function') {
                this.onLogoutSuccess();
            } else {
                window.location.href = '/'; 
            }
            this.isLoggingOut = false;
        }
    },
    mounted() {
        // Ekspos fungsi saran ke Navbar Global
        window.bukaModalSaran = () => { this.bukaModalSaran(); };
        
        // Deteksi perubahan memori sesi lintas Tab
        window.addEventListener('storage', () => {
            const updatedUser = localStorage.getItem('ut_user_session');
            if (updatedUser) {
                this.currentUser = JSON.parse(updatedUser);
                this.imageVersion = new Date().getTime(); // Refresh Foto
            } else {
                this.currentUser = null;
            }
        });
    }
};
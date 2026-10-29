const { createApp } = Vue;

const app = createApp({
    data() {
        return {
            kodeMk: window.utKodeMk || '',
            mkData: window.utMkData || {},
            modules: window.utModules || [],
            
            currentUser: null,
            logoutModalInstance: null,
            premiumModalInstance: null,
            favoritIds: [],
            modul: null,
            imageVersion: new Date().getTime()
        }
    },
    computed: {
        navThemeColor() { return this.mkData.theme_color || 'bg-primary'; },
        courseName() { return this.mkData.nama_mk || 'Universitas Terbuka'; },
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
        handleImageError(event) { 
            event.target.src = 'https://placehold.co/160x220/004da1/white?text=Cover+BMP'; 
        },
        handleLogoError(event) { event.target.style.display = 'none'; },
        
        async checkLoginState() {
            const savedUser = localStorage.getItem('ut_user_session');
            if (savedUser) {
                try {
                    const response = await fetch('/api/auth/validate');
                    if (!response.ok) throw new Error('Sesi Server Invalid');

                    const result = await response.json();
                    if (result.data) {
                        this.currentUser = result.data;
                        localStorage.setItem('ut_user_session', JSON.stringify(result.data));
                    } else {
                        this.currentUser = JSON.parse(savedUser);
                    }
                    this.fetchFavoritIds();
                } catch (error) {
                    localStorage.removeItem('ut_user_session');
                    this.currentUser = null;
                    alert('Keamanan: Sesi Anda telah berakhir atau tidak valid. Silakan masuk kembali.');
                    window.location.href = '/';
                }
            } else {
                alert("Akses ditolak! Silakan masuk (login) terlebih dahulu.");
                window.location.href = '/';
            }
        },

        async fetchFavoritIds() {
            if (!this.currentUser) return;
            try {
                const response = await fetch('/api/favorit/ids/' + this.currentUser.id_user);
                
                if (response.status === 401 || response.status === 403) {
                    alert("Sesi Anda telah berakhir. Silakan login kembali.");
                    this.confirmLogout();
                    return;
                }

                const result = await response.json();
                if (response.ok && result.status === 'success') {
                    this.favoritIds = result.data.map(id => Number(id));
                }
            } catch (error) { 
                console.error("Gagal melakukan sinkronisasi data favorit:", error); 
            }
        },
        
        isFavorite(idMk) { 
            return this.favoritIds.includes(Number(idMk)); 
        },

        // --- Logika Freemium Dinamis ---
        isModulPremium(modulNum) {
            if (!this.mkData || !this.mkData.modul_premium) return false;
            const premiumModules = this.mkData.modul_premium.split(',').map(m => m.trim());
            return premiumModules.includes(modulNum.toString());
        },
        
        showPremiumModal() {
            if (!this.premiumModalInstance) {
                const el = document.getElementById('premiumModal');
                if (el) this.premiumModalInstance = new bootstrap.Modal(el);
            }
            if (this.premiumModalInstance) this.premiumModalInstance.show();
        },
        
        async toggleFavorite(idMk) {
            if (!this.currentUser) {
                alert("Sesi tidak valid. Silakan kembali ke halaman utama untuk masuk.");
                window.location.href = '/';
                return;
            }

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
                    alert("Sesi Anda telah kedalwarsa.");
                    this.confirmLogout();
                    return;
                }

                const result = await response.json();
                if (!response.ok || result.status !== 'success') { 
                    this.fetchFavoritIds(); 
                }
            } catch (error) {
                console.error("Gagal memperbarui status bintang minat:", error);
                this.fetchFavoritIds(); 
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
    }
});

app.config.compilerOptions.isCustomElement = (tag) => tag === 'g-emoji';
app.mount('#ut');

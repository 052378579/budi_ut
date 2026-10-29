const { createApp } = Vue;

const app = createApp({
    data() {
        return {
            idMk: window.utPeringkatIdMk || '',
            kodeMk: window.utPeringkatKodeMk || '',
            modul: window.utPeringkatModul || '',
            
            mk: {}, // Penampung objek metadata mata kuliah
            rankings: [],
            loading: true,
            currentUser: null,
            logoutModalInstance: null,
            imageVersion: new Date().getTime()
        }
    },
    computed: {
        // Properti komputasi dinamis yang dikonsumsi otomatis oleh modul_header.php
        navThemeColor() { return this.mk.theme_color || 'bg-primary'; },
        courseName() { return `Papan Peringkat - ${this.kodeMk}`; },
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

        checkLoginState() {
            const savedUser = localStorage.getItem('ut_user_session');
            if (savedUser) { 
                this.currentUser = JSON.parse(savedUser); 
            } else {
                alert("Sesi Anda tidak valid. Harap login kembali dari beranda.");
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

        // RE-DESAIN BARU: Fetching Rantai Informasi Metadata & Leaderboard
        async loadMetadataAndLeaderboard() {
            try {
                // 1. Dapatkan info master mata kuliah untuk mengambil properti 'kode_bmp' dan 'nama_mk'
                const resMeta = await fetch(`/api/mk/show/${this.kodeMk}`);
                if (resMeta.ok) {
                    this.mk = await resMeta.json();
                }

                // 2. Tarik top 10 besar rekap nilai berdasarkan id_mk dan modul_ke
                let url = `/api/skor/ranking/${this.idMk}/${this.modul}`;
                if (this.modul === 'Simulasi UAS') {
                    url = `/api/skor/ranking/${this.idMk}/0?tipe=uas`;
                }
                const response = await fetch(url);
                if (!response.ok) throw new Error('Gagal menarik transaksi peringkat server.');
                
                this.rankings = await response.json();
            } catch (error) {
                console.error("Gagal melakukan sinkronisasi data:", error);
            } finally {
                this.loading = false;
            }
        },
        
        getRankClass(index) {
            if (index === 0) return 'rank-1';
            if (index === 1) return 'rank-2';
            if (index === 2) return 'rank-3';
            return '';
        },
        
        formatWaktu(datetimeString) {
            if (!datetimeString) return '-';
            
            const date = new Date(datetimeString);
            const optionsTanggal = { day: 'numeric', month: 'short', year: 'numeric' };
            const tanggal = date.toLocaleDateString('id-ID', optionsTanggal);
            
            const optionsWaktu = { hour: '2-digit', minute: '2-digit' };
            const waktu = date.toLocaleTimeString('id-ID', optionsWaktu);
            
            return `${tanggal}, ${waktu} WIB`; 
        }
    },
    mounted() {
        this.checkLoginState();
        this.loadMetadataAndLeaderboard();
    }
});

app.config.compilerOptions.isCustomElement = (tag) => tag === 'g-emoji';
app.mount('#ut');

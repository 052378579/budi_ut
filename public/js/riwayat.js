const { createApp } = Vue;

const app = createApp({
    mixins: [globalMixin],
    data() {
        return {
            riwayatList: [],
            isLoading: true
        }
    },
    methods: {
        onLoginSuccess() {
            this.fetchRiwayat();
        },
        async fetchRiwayat() {
            this.isLoading = true;
            try {
                const response = await fetch('/api/skor/user/' + this.currentUser.id_user);
                if (response.status === 401 || response.status === 403) {
                    this.showToast("Sesi Anda telah kedalwarsa. Silakan masuk kembali.");
                    this.confirmLogout(); 
                    return;
                }
                const result = await response.json();
                if (response.ok) {
                    this.riwayatList = Array.isArray(result) ? result : (result.data || []);
                }
            } catch (error) {
                console.error("Gagal memuat log transaksi nilai:", error);
            } finally {
                this.isLoading = false;
            }
        },
        formatWaktu(datetime) {
            if (!datetime) return '-';
            const date = new Date(datetime);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + 
                   ' ' + date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        },
        handleImageError(event) {
            // Jika gambar tidak ditemukan (404), gunakan placeholder default
            event.target.src = 'https://placehold.co/450x600/eeeeee/999999?text=Cover\\nBelum+Ada';
        }
    },
    mounted() {
        this.checkLoginState();
    }
});

app.config.compilerOptions.isCustomElement = (tag) => tag === 'g-emoji';
app.mount('#ut');
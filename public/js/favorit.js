const { createApp } = Vue;

const app = createApp({
    mixins: [globalMixin],
    data() {
        return {
            favoritList: [],
            isLoading: true,
            hoverCard: null
        }
    },
    methods: {
        onLoginSuccess() {
            this.fetchFavorit();
        },
        openCourse(kode) { 
            window.location.href = '/' + kode; 
        },
        async fetchFavorit() {
            this.isLoading = true;
            try {
                const response = await fetch('/api/favorit/list/' + this.currentUser.id_user);
                if (response.status === 401 || response.status === 403) {
                    this.showToast("Sesi Anda telah berakhir. Silakan login kembali.");
                    this.confirmLogout(); 
                    return;
                }
                const result = await response.json();
                if (response.ok && result.status === 'success') {
                    this.favoritList = result.data;
                }
            } catch (error) {} finally {
                this.isLoading = false;
            }
        },
        async hapusFavorit(idMk) {
            if(!confirm("Anda yakin ingin menghapus mata kuliah ini dari daftar favorit?")) return;
            try {
                const response = await fetch('/api/favorit/toggle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_user: this.currentUser.id_user, id_mk: idMk })
                });
                if (response.status === 401 || response.status === 403) {
                    this.confirmLogout(); return;
                }
                const result = await response.json();
                if (response.ok && result.status === 'success') {
                    this.fetchFavorit(); 
                } else {
                    this.showToast("Gagal menghapus mata kuliah dari favorit.");
                }
            } catch (error) {
                this.showToast("Terjadi kesalahan jaringan.");
            }
        },
        formatDate(datetime) {
            if (!datetime) return '-';
            return new Date(datetime).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        }
    },
    mounted() {
        this.checkLoginState();
    }
});

app.config.compilerOptions.isCustomElement = (tag) => tag === 'g-emoji';
app.mount('#ut');
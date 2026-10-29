const { createApp } = Vue;

// PERBAIKAN: Memisahkan instance app untuk mengaktifkan config kustom
const app = createApp({
    data() {
        return {
            currentUser: null,
            totalDownloads: 0,
            isDownloading: false,
            logoutModalInstance: null,

            // PERBAIKAN: Variabel penunjang kompatibilitas personal_header.php
            navThemeColor: '', 
            imageVersion: new Date().getTime(),
            
            // PERBAIKAN: Variabel untuk fitur Kotak Saran
            saranModalInstance: null,
            isSubmittingSaran: false,
            saranForm: {
                kategori: 'lainnya',
                isi_saran: ''
            },
            
            // Variabel untuk fitur Toast/Notifikasi bawaan dari personal_footer
            toastMessage: '',
            toastType: 'error'
        }
    },
    methods: {
        checkLoginState() {
            const savedUser = localStorage.getItem('ut_user_session');
            if (savedUser) {
                this.currentUser = JSON.parse(savedUser);
            }
        },
        
        // PERBAIKAN: Fungsi penunjang error rendering header
        handleLogoError(event) { 
            event.target.style.display = 'none'; 
        },
        
        async fetchTotalCount() {
            try {
                const response = await fetch('/api/apk/count');
                const result = await response.json();
                if (response.ok) {
                    this.totalDownloads = result.total;
                }
            } catch (e) {
                console.error("Gagal mengambil jumlah unduhan", e);
            }
        },
        
        async unduhApk() {
            this.isDownloading = true;
            try {
                const payload = {
                    id_user: this.currentUser ? this.currentUser.id_user : null
                };
                
                const response = await fetch('/api/apk/download', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const result = await response.json();
                
                if (response.ok && result.status === 'success') {
                    this.totalDownloads++;
                    
                    const link = document.createElement('a');
                    link.href = result.url;
                    link.download = 'TesFormatif.apk';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert("Gagal menginisialisasi tautan unduhan.");
                }
            } catch (e) {
                alert("Terjadi kesalahan jaringan saat mengunduh.");
            } finally {
                this.isDownloading = false;
            }
        },

        // PERBAIKAN: Fungsionalitas Kotak Saran
        bukaModalSaran() {
            if (!this.saranModalInstance) {
                this.saranModalInstance = new bootstrap.Modal(document.getElementById('saranModal'));
            }
            this.saranForm.kategori = 'lainnya';
            this.saranForm.isi_saran = '';
            this.saranModalInstance.show();
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
                    if (this.saranModalInstance) {
                        this.saranModalInstance.hide();
                    }
                    alert("Terima kasih! Saran Anda telah terkirim.");
                } else {
                    alert("Gagal mengirim saran: " + result.message);
                }
            } catch (error) {
                alert("Koneksi terputus. Gagal menghubungi Server");
            } finally {
                this.isSubmittingSaran = false;
            }
        },

        tutupHalaman() {
            window.location.href = '/';
        },

        logout() {
            if (!this.logoutModalInstance) {
                this.logoutModalInstance = new bootstrap.Modal(document.getElementById('logoutModal'));
            }
            this.logoutModalInstance.show();
        },
        
        async confirmLogout() {
            if (this.currentUser) {
                try {
                    await fetch('/api/auth/keluar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ nama_user: this.currentUser.nama_user })
                    });
                } catch (e) {}
            }
            localStorage.removeItem('ut_user_session');
            window.location.href = '/';
        }
    },
    mounted() {
        this.checkLoginState();
        this.fetchTotalCount();
        
        // PERBAIKAN: Ekspos fungsi saran ke objek window
        window.bukaModalSaran = () => {
            this.bukaModalSaran();
        };
        
        // Sinkronisasi pembaruan Storage
        window.addEventListener('storage', () => {
            const updatedUser = localStorage.getItem('ut_user_session');
            if (updatedUser) {
                this.currentUser = JSON.parse(updatedUser);
                this.imageVersion = new Date().getTime(); 
            }
        });
    }
});

// PERBAIKAN: Mengabaikan tag g-emoji dari footer agar konsol bersih
app.config.compilerOptions.isCustomElement = (tag) => tag === 'g-emoji';

app.mount('#ut');

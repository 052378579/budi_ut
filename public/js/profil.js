const { createApp } = Vue;

const app = createApp({
    mixins: [globalMixin],
    data() {
        return {
            isPageLoading: true,
            isSavingProfil: false,
            isUploadingFoto: false,
            listDaerah: [],
            form: { nama_user: '', no_hp: '', nim_mhs: '', kode_ut: '', img_mhs: '', role: '' },
            navThemeColor: 'bg-primary'
        }
    },
    methods: {
        handleAvatarError(event) { event.target.src = 'https://placehold.co/150x150/004da1/white?text=User'; },
        
        onLoginSuccess() {
            this.sinkronisasiDataProfil();
        },
        async sinkronisasiDataProfil() {
            try {
                const resDaerah = await fetch('/api/daerah/list');
                const dataResDaerah = await resDaerah.json();
                this.listDaerah = dataResDaerah.data;

                const resProfil = await fetch('/api/profil/show/' + this.currentUser.id_user);
                const dataResProfil = await resProfil.json();
                
                if (resProfil.ok && dataResProfil.status === 'success') {
                    this.form = dataResProfil.data;
                    if (this.form.kode_ut === null) this.form.kode_ut = '';
                } else {
                    this.showToast("Gagal memuat rekap biodata dari server STB.");
                }
            } catch (error) {
                this.showToast("Gangguan koneksi internet atau server sibuk.");
            } finally {
                this.isPageLoading = false;
            }
        },
        async prosesUnggahFoto(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                this.showToast("File terlalu besar! Maksimal pas foto 2 MB."); return;
            }

            this.isUploadingFoto = true;
            const payloadFormData = new FormData();
            payloadFormData.append('foto_profil', file);

            try {
                const response = await fetch('/api/profil/uploadFoto/' + this.currentUser.id_user, {
                    method: 'POST', body: payloadFormData 
                });
                const result = await response.json();
                
                if (response.ok && result.status) {
                    this.form.img_mhs = result.img_mhs;
                    this.imageVersion = new Date().getTime(); 
                    
                    this.currentUser.img_mhs = result.img_mhs;
                    localStorage.setItem('ut_user_session', JSON.stringify(this.currentUser));
                    window.dispatchEvent(new Event('storage'));

                    this.showToast("Foto profil berhasil disimpan!", "success");
                } else {
                    this.showToast(result.message || "Server menolak file (Status: " + response.status + ")");
                }
            } catch (e) {
                this.showToast("Terjadi kegagalan sistem.");
            } finally {
                this.isUploadingFoto = false;
                event.target.value = '';
            }
        },
        async simpanPerubahanProfil() {
            if (this.form.nim_mhs && !/^[0-9]{9}$/.test(this.form.nim_mhs)) {
                this.showToast("NIM tidak valid! Harus berupa 9 digit angka murni."); return;
            }

            this.isSavingProfil = true;
            try {
                const response = await fetch('/api/profil/update/' + this.currentUser.id_user, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.form)
                });
                const result = await response.json();
                
                if (response.ok && result.status) {
                    this.showToast("Biodata profil sukses disinkronisasikan ke server!", "success");
                    this.currentUser.nama_user = this.form.nama_user.toUpperCase();
                    this.currentUser.img_mhs = this.form.img_mhs; 
                    localStorage.setItem('ut_user_session', JSON.stringify(this.currentUser));
                    window.dispatchEvent(new Event('storage'));
                } else {
                    this.showToast(result.message || "Gagal memperbarui data.");
                }
            } catch (error) {
                this.showToast("Gagal memproses request POST. Silakan coba sesaat lagi.");
            } finally {
                this.isSavingProfil = false;
            }
        }
    },
    mounted() {
        this.checkLoginState();
    }
});

app.config.compilerOptions.isCustomElement = (tag) => tag === 'g-emoji';
app.mount('#ut');
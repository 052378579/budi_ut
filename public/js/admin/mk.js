const { createApp } = Vue;

createApp({
    data() {
        return {
            listMK: [],
            searchQuery: '',
            isLoading: false,
            isSaving: false,
            isEdit: false,
            errorMessages: [],
            prodiOptions: ['FEB', 'FHISIP', 'FKIP', 'FST'],
            
            form: {
                id_mk: null,
                kode_bmp: '',
                kode_mk: '',
                nama_mk: '',
                program_studi: [], 
                jumlah_sks: 3, 
                theme_color: 'bg-primary text-white',
                modul_premium: ''
            },
            
            selectedFile: null,
            modalInstance: null,
            adminUser: null,
            logoutModalInstance: null,

            currentPage: 1,
            itemsPerPage: 10,
            perPageOptions: [10, 15, 25, 'Semua'],
            imageVersion: new Date().getTime()
        }
    },
    computed: {
        filteredListMK() {
            if (!this.searchQuery) return this.listMK;
            const query = this.searchQuery.toLowerCase();
            return this.listMK.filter(mk => 
                (mk.nama_mk && mk.nama_mk.toLowerCase().includes(query)) || 
                (mk.kode_mk && mk.kode_mk.toLowerCase().includes(query)) ||
                (mk.kode_bmp && mk.kode_bmp.toLowerCase().includes(query))
            );
        },
        totalPages() {
            if (this.itemsPerPage === 'Semua') return 1;
            return Math.ceil(this.filteredListMK.length / this.itemsPerPage);
        },
        paginatedList() {
            if (this.itemsPerPage === 'Semua') return this.filteredListMK;
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredListMK.slice(start, end);
        }
    },
    methods: {
        handleImageError(event) {
            event.target.src = 'https://placehold.co/600x400/004da1/white?text=Gambar+Belum+Ada';
        },
        
        getThemeTextClass(theme) {
            if (!theme) return 'text-dark';
            const lightThemes = [
                'bg-light', 'bg-white', 'bg-warning', 'bg-info', 'bg-transparent',
                'bg-primary-subtle', 'bg-secondary-subtle', 'bg-success-subtle',
                'bg-danger-subtle', 'bg-warning-subtle', 'bg-info-subtle',
                'bg-light-subtle', 'bg-dark-subtle', 'bg-body', 'bg-body-secondary', 'bg-body-tertiary'
            ];
            const baseTheme = theme.replace(' text-white', '').replace(' text-dark', '').trim();
            return lightThemes.includes(baseTheme) ? 'text-dark' : 'text-white';
        },

        // MODERNISASI: Otentikasi berbasis Role 'admin'
        checkAdminSession() {
            const savedUser = localStorage.getItem('ut_user_session');
            if (savedUser) {
                const user = JSON.parse(savedUser);
                // Mendukung kompatibilitas peran baru maupun Fallback ID 1
                if (user.role === 'admin' || user.id_user == 1) {
                    this.adminUser = user;
                } else {
                    alert("Akses Ilegal! Akun Anda tidak memiliki otoritas Administrator.");
                    window.location.href = '/';
                }
            } else {
                window.location.href = '/';
            }
        },

        logout() {
            if (!this.logoutModalInstance) {
                this.logoutModalInstance = new bootstrap.Modal(document.getElementById('logoutModal'));
            }
            this.logoutModalInstance.show();
        },

        confirmLogout() {
            localStorage.removeItem('ut_user_session'); 
            this.adminUser = null;
            if (this.logoutModalInstance) this.logoutModalInstance.hide();
            window.location.href = '/'; 
        },

        async fetchMK() {
            this.isLoading = true;
            try {
                const response = await fetch('/api/mk');
                this.listMK = await response.json();
            } catch (error) {
                console.error("Gagal mengambil master data:", error);
            } finally {
                this.isLoading = false;
            }
        },

        showModal(data = null) {
            this.errorMessages = []; 
            this.selectedFile = null;
            if (this.$refs.coverFile) this.$refs.coverFile.value = '';

            if (data) {
                this.isEdit = true;
                this.form = { ...data };
                // Default fallback jika jumlah SKS belum tersetting pada data lawas
                this.form.jumlah_sks = data.jumlah_sks || 3; 

                if (typeof data.program_studi === 'string') {
                    this.form.program_studi = data.program_studi.split(',').map(s => s.trim());
                } else {
                    this.form.program_studi = [];
                }
            } else {
                this.isEdit = false;
                this.form = { 
                    id_mk: null, kode_bmp: '', kode_mk: '', nama_mk: '', 
                    program_studi: [], jumlah_sks: 3, theme_color: 'bg-primary text-white', modul_premium: '' 
                };
            }

            if (!this.modalInstance) {
                this.modalInstance = new bootstrap.Modal(document.getElementById('modalMK'));
            }
            this.modalInstance.show();
        },

        onFileSelected(event) {
            this.selectedFile = event.target.files[0];
        },

        async saveMK() {
            this.isSaving = true;
            this.errorMessages = [];
            try {
                const url = this.isEdit ? `/api/mk/update/${this.form.id_mk}` : '/api/mk/create';
                const method = this.isEdit ? 'PUT' : 'POST';
                const payload = { ...this.form };
                payload.program_studi = payload.program_studi.join(', ');

                const response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (response.ok) {
                    // Jika dalam mode edit dan admin memilih berkas gambar cover baru
                    if (this.isEdit && this.selectedFile) {
                        await this.executeUploadCover(this.form.id_mk);
                    }
                    this.modalInstance.hide();
                    this.fetchMK(); 
                } else {
                    this.errorMessages = result.messages ? Object.values(result.messages) : [result.message || "Terjadi kesalahan."];
                }
            } catch (error) {
                this.errorMessages = ["Gagal menghubungi server database."];
            } finally {
                this.isSaving = false;
            }
        },

        async executeUploadCover(idMk) {
            const formData = new FormData();
            // Nama field 'image' telah diselaraskan dengan parameter request pada Dokumen.php
            formData.append('image', this.selectedFile);

            try {
                const res = await fetch(`/api/dokumen/uploadBmp/${idMk}`, {
                    method: 'POST',
                    body: formData
                });
                if (!res.ok) console.error("Cover upload rejected by storage core.");
            } catch (err) {
                console.error("Network error during image transfer:", err);
            }
        },

        async deleteMK(id, namaMK) {
            if (!confirm(`Apakah Anda yakin ingin menghapus mata kuliah ${namaMK}? Seluruh riwayat skor mahasiswa akan ikut terhapus secara Cascade.`)) return;
            try {
                const response = await fetch(`/api/mk/delete/${id}`, { method: 'DELETE' });
                if (response.ok) {
                    this.fetchMK();
                } else {
                    alert("Gagal menghapus data mata kuliah.");
                }
            } catch (error) {
                alert("Terjadi kesalahan jaringan.");
            }
        }
    },
    mounted() {
        this.checkAdminSession();
        this.fetchMK();
    }
}).mount('#ut');

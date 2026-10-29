const { createApp } = Vue;

createApp({
    data() {
        return {
            listPengumuman: [],
            isEditMode: false,
            isSaving: false,
            toastMessage: '',
            toastType: 'success',
            modalInstance: null,
            quillInstance: null,
            
            // Variabel yang diseragamkan untuk integrasi Navbar Global
            adminUser: null,
            logoutModalInstance: null,
            imageVersion: new Date().getTime(),
            
            form: {
                id_pengumuman: null,
                judul: '',
                isi_pengumuman: '',
                tipe_pengumuman: 'info',
                tgl_mulai: '',
                tgl_selesai: '',
                gambar: null
            },
            fileGambar: null
        }
    },
    methods: {
        // FUNGSI WAJIB: Otentikasi dan Logika Navbar Layout Induk
        checkAdminSession() {
            const savedUser = localStorage.getItem('ut_user_session');
            if (savedUser) {
                const user = JSON.parse(savedUser);
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

        // LOGIKA HALAMAN PENGUMUMAN
        showToast(message, type = 'success') {
            this.toastMessage = message;
            this.toastType = type;
            this.$nextTick(() => {
                const el = document.getElementById('adminToast');
                if (el) bootstrap.Toast.getOrCreateInstance(el).show();
            });
        },

        handleFileChange(event) {
            const file = event.target.files[0];
            if (file) {
                // Opsional: Validasi ukuran file di frontend (misal 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    this.showToast("Ukuran file gambar maksimal 2MB.", "danger");
                    this.$refs.gambarInput.value = '';
                    this.fileGambar = null;
                    return;
                }
                this.fileGambar = file;
            } else {
                this.fileGambar = null;
            }
        },

        async loadAllPengumuman() {
            try {
                const res = await fetch('/api/pengumuman');
                const result = await res.json();
                if (res.ok && result.status === 'success') {
                    this.listPengumuman = result.data;
                }
            } catch (e) {
                this.showToast("Gagal menarik data riwayat pengumuman dari server.", "danger");
            }
        },

        formatDateForInput(dateString) {
            if (!dateString) return '';
            return dateString.replace(' ', 'T').substring(0, 16);
        },

        bukaModalTambah() {
            this.isEditMode = false;
            this.form = {
                id_pengumuman: null,
                judul: '',
                isi_pengumuman: '',
                tipe_pengumuman: 'info',
                tgl_mulai: '',
                tgl_selesai: '',
                gambar: null
            };
            this.fileGambar = null;
            if (this.$refs.gambarInput) this.$refs.gambarInput.value = '';
            
            if (this.quillInstance) {
                this.quillInstance.root.innerHTML = '';
            }
            
            if (!this.modalInstance) {
                this.modalInstance = new bootstrap.Modal(document.getElementById('crudAnnouncementModal'));
            }
            this.modalInstance.show();
        },

        bukaModalEdit(item) {
            this.isEditMode = true;
            this.form = {
                id_pengumuman: item.id_pengumuman,
                judul: item.judul,
                isi_pengumuman: item.isi_pengumuman,
                tipe_pengumuman: item.tipe_pengumuman,
                tgl_mulai: this.formatDateForInput(item.tgl_mulai),
                tgl_selesai: this.formatDateForInput(item.tgl_selesai),
                gambar: item.gambar || null
            };
            this.fileGambar = null;
            if (this.$refs.gambarInput) this.$refs.gambarInput.value = '';

            if (this.quillInstance) {
                this.quillInstance.root.innerHTML = item.isi_pengumuman || '';
            }
            
            if (!this.modalInstance) {
                this.modalInstance = new bootstrap.Modal(document.getElementById('crudAnnouncementModal'));
            }
            this.modalInstance.show();
        },

        async simpanDataPengumuman() {
            this.isSaving = true;
            
            // Gunakan adminUser hasil dari session check
            if (this.adminUser) {
                this.form.created_by = this.adminUser.id_user;
            }

            // Tentukan URL
            const url = this.isEditMode ? `/api/pengumuman/update/${this.form.id_pengumuman}` : '/api/pengumuman/create';
            
            const payload = new FormData();
            payload.append('judul', this.form.judul);
            payload.append('isi_pengumuman', this.form.isi_pengumuman);
            payload.append('tipe_pengumuman', this.form.tipe_pengumuman);
            payload.append('tgl_mulai', this.form.tgl_mulai);
            payload.append('tgl_selesai', this.form.tgl_selesai);
            
            if (this.form.created_by) {
                payload.append('created_by', this.form.created_by);
            }
            
            // HTTP Spoofing untuk update di CI4
            if (this.isEditMode) {
                payload.append('_method', 'PUT');
            }
            
            // Menambahkan file gambar jika ada
            if (this.fileGambar) {
                payload.append('gambar', this.fileGambar);
            }
            
            try {
                const response = await fetch(url, {
                    method: 'POST', // WAJIB POST untuk pengiriman FormData (multipart/form-data)
                    // Hapus 'Content-Type': 'application/json'
                    body: payload
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    this.showToast(result.message, "success");
                    this.modalInstance.hide();
                    this.loadAllPengumuman(); 
                } else {
                    this.showToast(result.message || "Gagal memproses data pengumuman.", "danger");
                }
            } catch (err) {
                this.showToast("Sistem mengalami kegagalan transmisi data dengan server.", "danger");
            } finally {
                this.isSaving = false;
            }
        },
                
        async hapusPengumuman(id) {
            if (!confirm("Apakah Anda yakin ingin menghapus permanen maklumat pengumuman ini?")) return;

            try {
                const response = await fetch(`/api/pengumuman/delete/${id}`, { 
                    method: 'DELETE' 
                });
                const result = await response.json();
                
                if (response.ok) {
                    this.showToast(result.message, "success");
                    this.loadAllPengumuman(); 
                } else {
                    this.showToast(result.message || "Gagal menghapus pengumuman.", "danger");
                }
            } catch (e) {
                this.showToast("Terputus dari pangkalan data saat mencoba menghapus.", "danger");
            }
        }
    },
    mounted() {
        // Pastikan mengecek sesi Admin terlebih dahulu untuk mengamankan halaman dan menyalakan Navbar
        this.checkAdminSession();
        this.loadAllPengumuman();
        
        // Inisialisasi Quill Editor
        this.quillInstance = new Quill('#quillEditor', {
            theme: 'snow',
            placeholder: 'Tulis rincian jadwal, tautan Zoom, hari pelaksanaan, dan maklumat lengkap di sini...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'clean']
                ]
            }
        });
        
        this.quillInstance.on('text-change', () => {
            let html = this.quillInstance.root.innerHTML;
            if (html === '<p><br></p>') html = '';
            this.form.isi_pengumuman = html;
        });
    }
}).mount('#ut');

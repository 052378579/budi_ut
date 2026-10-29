const { createApp } = Vue;

createApp({
    data() {
        return {
            usersList: [],
            searchQuery: '',
            isLoading: false,
            adminUser: null,
            logoutModalInstance: null,
            currentPage: 1,
            itemsPerPage: 10,
            perPageOptions: [10, 15, 25, 'Semua'],
            imageVersion: new Date().getTime(),
            
            // Modal Edit
            editModalInstance: null,
            isSaving: false,
            formUser: {
                id_user: null,
                nama_user: '',
                no_hp: '',
                is_premium: 0
            }
        }
    },
    computed: {
        filteredUsers() {
            if (!this.searchQuery) return this.usersList;
            const query = this.searchQuery.toLowerCase();
            return this.usersList.filter(user => 
                (user.nama_user && user.nama_user.toLowerCase().includes(query)) || 
                (user.no_hp && user.no_hp.toLowerCase().includes(query))
            );
        },
        totalPages() {
            if (this.itemsPerPage === 'Semua') return 1;
            return Math.ceil(this.filteredUsers.length / this.itemsPerPage);
        },
        paginatedList() {
            if (this.itemsPerPage === 'Semua') return this.filteredUsers;
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredUsers.slice(start, end);
        },
        visiblePages() {
            if (this.itemsPerPage === 'Semua') return [1];
            
            const total = this.totalPages;
            const current = this.currentPage;

            if (total <= 7) {
                return Array.from({ length: total }, (_, i) => i + 1);
            }

            if (current <= 4) {
                return [1, 2, 3, 4, 5, '...', total];
            } else if (current >= total - 3) {
                return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
            } else {
                return [1, '...', current - 1, current, current + 1, '...', total];
            }
        }
    },
    methods: {
        // MODERNISASI: Otentikasi berbasis Role 'admin'
        checkAdminSession() {
            const savedUser = localStorage.getItem('ut_user_session');
            if (savedUser) {
                const user = JSON.parse(savedUser);
                if (user.role === 'admin' || user.id_user == 1) { 
                    this.adminUser = user;
                } else {
                    alert("Akses Ilegal! Anda tidak memiliki otoritas Administrator.");
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

        async fetchUsers() {
            this.isLoading = true;
            try {
                const response = await fetch('/api/users');
                if (response.ok) {
                    this.usersList = await response.json();
                }
            } catch (error) {
                console.error("Gagal terhubung dengan RESTful Users API:", error);
            } finally {
                this.isLoading = false;
            }
        },

        async hapusUser(id, nama) {
            if (!confirm(`HATI-HATI! Menghapus akun mahasiswa "${nama}" akan melenyapkan riwayat nilai kuis serta penanda favorit kuis secara otomatis (ON DELETE CASCADE). Yakin tetap melanjutkan?`)) return;
            try {
                const response = await fetch(`/api/users/delete/${id}`, { method: 'DELETE' });
                const result = await response.json();
                
                if (response.ok) {
                    this.fetchUsers();
                } else {
                    alert(result.message || "Gagal menghapus data pengguna.");
                }
            } catch (error) {
                alert("Terjadi kesalahan koneksi database.");
            }
        },

        editUser(user) {
            this.formUser = {
                id_user: user.id_user,
                nama_user: user.nama_user,
                no_hp: user.no_hp,
                is_premium: user.is_premium
            };
            if (!this.editModalInstance) {
                const el = document.getElementById('editUserModal');
                if (el) this.editModalInstance = new bootstrap.Modal(el);
            }
            if (this.editModalInstance) this.editModalInstance.show();
        },

        async savePremium() {
            this.isSaving = true;
            try {
                const response = await fetch(`/api/users/premium/${this.formUser.id_user}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ is_premium: this.formUser.is_premium })
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    if (this.editModalInstance) this.editModalInstance.hide();
                    this.fetchUsers(); // Refresh data untuk memastikan UI sinkron
                } else {
                    alert(result.message || "Gagal menyimpan perubahan.");
                }
            } catch (error) {
                alert("Terjadi kesalahan jaringan.");
            } finally {
                this.isSaving = false;
            }
        },

        formatTanggal(datetime) {
            if (!datetime) return '-';
            const date = new Date(datetime);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        }
    },
    mounted() {
        this.checkAdminSession();
        this.fetchUsers();
    }
}).mount('#ut');

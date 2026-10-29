const { createApp } = Vue;

createApp({
    data() {
        return {
            adminUser: null,
            searchQuery: '',
            saranList: window.saranData?.saranList || [],
            logoutModalInstance: null,
            itemsPerPage: 10,
            perPageOptions: [10, 15, 25, 'Semua'],
            currentPage: 1,
            imageVersion: new Date().getTime()
        }
    },
    computed: {
        filteredSaran() {
            let list = [...this.saranList];
            if (this.searchQuery) {
                const sq = this.searchQuery.toLowerCase();
                list = list.filter(l => 
                    (l.nama_user && l.nama_user.toLowerCase().includes(sq)) || 
                    (l.kategori && l.kategori.toLowerCase().includes(sq)) || 
                    (l.isi_saran && l.isi_saran.toLowerCase().includes(sq))
                );
            }
            return list.sort((a, b) => {
                const dateA = new Date((a.created_at || '').replace(' ', 'T')).getTime() || 0;
                const dateB = new Date((b.created_at || '').replace(' ', 'T')).getTime() || 0;
                return dateB - dateA; // Urutkan terbaru di atas
            });
        },
        totalPages() {
            if (this.itemsPerPage === 'Semua') return 1;
            return Math.ceil(this.filteredSaran.length / this.itemsPerPage) || 1;
        },
        paginatedSaran() {
            if (this.itemsPerPage === 'Semua') return this.filteredSaran;
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredSaran.slice(start, start + this.itemsPerPage);
        }
    },
    methods: {
        resetPages() {
            this.currentPage = 1;
        },
        getBadgeClass(kategori) {
            if (!kategori) return 'badge-soft-secondary';
            const k = kategori.toLowerCase();
            if (k === 'bug') return 'badge-soft-danger';
            if (k === 'fitur') return 'badge-soft-info';
            if (k === 'desain') return 'badge-soft-primary';
            if (k === 'konten') return 'badge-soft-warning';
            return 'badge-soft-secondary';
        },
        checkLoginState() {
            const savedUser = localStorage.getItem('ut_user_session');
            if (savedUser) {
                this.adminUser = JSON.parse(savedUser);
                if (this.adminUser.role !== 'admin' && this.adminUser.id_user != 1) {
                    window.location.href = '/';
                }
            } else {
                window.location.href = '/';
            }
        },
        formatDateTime(datetimeStr) {
            if (!datetimeStr) return '-';
            const d = new Date(datetimeStr.replace(' ', 'T'));
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + 
                   ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        },
        logout() {
            if (!this.logoutModalInstance) {
                this.logoutModalInstance = new bootstrap.Modal(document.getElementById('logoutModal'));
            }
            this.logoutModalInstance.show();
        },
        async confirmLogout() {
            if (this.adminUser) {
                try {
                    await fetch('/api/auth/keluar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ nama_user: this.adminUser.nama_user })
                    });
                } catch (e) {}
            }
            localStorage.removeItem('ut_user_session');
            window.location.href = '/';
        }
    },
    mounted() {
        this.checkLoginState();
    }
}).mount('#ut');

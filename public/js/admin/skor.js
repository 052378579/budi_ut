const { createApp } = Vue;

createApp({
    data() {
        return {
            skorList: [],
            isLoading: false,
            errorMessage: '',
            adminUser: null,
            logoutModalInstance: null,
            
            // DATA PAGINATION
            currentPage: 1,
            itemsPerPage: 10,
            perPageOptions: [10, 15, 25, 'Semua'],
            
            imageVersion: new Date().getTime()
        }
    },
    computed: {
        totalPages() {
            if (this.itemsPerPage === 'Semua') return 1;
            return Math.ceil(this.skorList.length / this.itemsPerPage);
        },
        paginatedList() {
            if (this.itemsPerPage === 'Semua') return this.skorList;
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.skorList.slice(start, end);
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
                // Mendukung kompatibilitas peran baru maupun Fallback ID 1
                if (user.role === 'admin' || user.id_user == 1) {
                    this.adminUser = user;
                } else {
                    alert("Akses Ilegal! Anda tidak memiliki hak akses Administrator.");
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

        async fetchSemuaSkor() {
            this.isLoading = true;
            this.errorMessage = '';
            try {
                const response = await fetch('/api/skor');
                if (!response.ok) throw new Error('Gagal memuat data dari server');
                this.skorList = await response.json();
            } catch (error) {
                this.errorMessage = "Terjadi masalah jaringan saat mengambil rekap nilai.";
            } finally {
                this.isLoading = false;
            }
        },

        async hapusSkor(idSkor, nama) {
            if (confirm(`Yakin ingin menghapus riwayat skor milik ${nama}? Tindakan ini permanen.`)) {
                this.isLoading = true;
                try {
                    const response = await fetch(`/api/skor/delete/${idSkor}`, { method: 'DELETE' });
                    if (response.ok) {
                        await this.fetchSemuaSkor();
                    } else {
                        const resData = await response.json();
                        alert(resData.message || 'Gagal menghapus data.');
                    }
                } catch (error) {
                    alert("Terjadi masalah jaringan.");
                } finally {
                    this.isLoading = false;
                }
            }
        },

        formatWaktu(datetime) {
            if (!datetime) return '-';
            const date = new Date(datetime);
            return date.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) + '';
        },

        formatWhatsAppLink(item) {
            if (!item.no_hp) return '#';
            
            let phone = String(item.no_hp).replace(/[^0-9]/g, '');
            if (phone.startsWith('0')) {
                phone = '62' + phone.substring(1);
            }
            
            let nama = item.nama_user || 'Mahasiswa';
            let mkName = item.nama_mk || 'MK-' + item.id_mk;
            let skor = item.skor ? parseFloat(item.skor).toFixed(2) : '0.00';
            
            let evaluasi = item.jenis_ujian === 'uas' ? 'Simulasi UAS' : 'Modul Ke-' + item.modul_ke;
            
            let pesan = `Halo *${nama}*, terima kasih telah melakukan Latihan Tes Formatif *${mkName}* ${evaluasi}, Anda memperoleh skor ${skor}.\nTetap semangat!\n\nSalam,\ndev.TesFormatif`;
            
            return `https://wa.me/${phone}?text=${encodeURIComponent(pesan)}`;
        }
    },
    mounted() {
        this.checkAdminSession();
        this.fetchSemuaSkor();
    }
}).mount('#ut');

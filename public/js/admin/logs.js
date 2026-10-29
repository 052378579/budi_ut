const { createApp } = Vue;

createApp({
    data() {
        return {
            adminUser: null,
            searchQuery: '',
            auditList: window.logsData?.auditList || [],
            sessionList: window.logsData?.sessionList || [],
            logoutModalInstance: null,
            itemsPerPage: 10,
            perPageOptions: [10, 15, 25, 'Semua'],
            currentAuditPage: 1,
            currentSessionPage: 1,
            currentOnlinePage: 1,
            activeTab: 'online',
            cityCache: {},
            imageVersion: new Date().getTime(),
            timeRemaining: 60,
            progressWidth: 100,
            refreshTimer: null
        }
    },
    computed: {
        filteredAudit() {
            let list = [...this.auditList];
            if (this.searchQuery) {
                const sq = this.searchQuery.toLowerCase();
                list = list.filter(l => 
                    (l.nama_user && l.nama_user.toLowerCase().includes(sq)) || 
                    (l.aksi && l.aksi.toLowerCase().includes(sq)) || 
                    (l.ip_address && l.ip_address.includes(sq))
                );
            }
            return list.sort((a, b) => {
                const dateA = new Date((a.created_at || '').replace(' ', 'T')).getTime() || 0;
                const dateB = new Date((b.created_at || '').replace(' ', 'T')).getTime() || 0;
                return dateB - dateA;
            });
        },
        filteredSession() {
            let list = [...this.sessionList];
            if (this.searchQuery) {
                const sq = this.searchQuery.toLowerCase();
                list = list.filter(s => 
                    (s.nama_user && s.nama_user.toLowerCase().includes(sq)) || 
                    (s.ip_address && s.ip_address.includes(sq))
                );
            }
            return list.sort((a, b) => {
                const dateA = new Date((a.last_activity || '').replace(' ', 'T')).getTime() || 0;
                const dateB = new Date((b.last_activity || '').replace(' ', 'T')).getTime() || 0;
                return dateB - dateA;
            });
        },
        filteredOnline() {
            return this.filteredSession.filter(s => this.isOnline(s.last_activity));
        },
        totalAuditPages() {
            if (this.itemsPerPage === 'Semua') return 1;
            return Math.ceil(this.filteredAudit.length / this.itemsPerPage);
        },
        totalSessionPages() {
            if (this.itemsPerPage === 'Semua') return 1;
            return Math.ceil(this.filteredSession.length / this.itemsPerPage);
        },
        totalOnlinePages() {
            if (this.itemsPerPage === 'Semua') return 1;
            return Math.ceil(this.filteredOnline.length / this.itemsPerPage);
        },
        paginatedAudit() {
            if (this.itemsPerPage === 'Semua') return this.filteredAudit;
            const start = (this.currentAuditPage - 1) * this.itemsPerPage;
            return this.filteredAudit.slice(start, start + this.itemsPerPage);
        },
        paginatedSession() {
            if (this.itemsPerPage === 'Semua') return this.filteredSession;
            const start = (this.currentSessionPage - 1) * this.itemsPerPage;
            return this.filteredSession.slice(start, start + this.itemsPerPage);
        },
        paginatedOnline() {
            if (this.itemsPerPage === 'Semua') return this.filteredOnline;
            const start = (this.currentOnlinePage - 1) * this.itemsPerPage;
            return this.filteredOnline.slice(start, start + this.itemsPerPage);
        }
    },
    methods: {
        resetPages() {
            this.currentAuditPage = 1;
            this.currentSessionPage = 1;
            this.currentOnlinePage = 1;
        },
        formatIP(ip) {
            if (ip === '::1') return '127.0.0.1';
            return ip;
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
            const d = new Date(datetimeStr);
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
        },
        loadGeoLocation() {
            const ips = new Set();
            this.auditList.forEach(item => { if(item.ip_address) ips.add(item.ip_address) });
            this.sessionList.forEach(item => { if(item.ip_address) ips.add(item.ip_address) });

            let delay = 0;
            ips.forEach(ip => {
                if(ip === '::1' || ip === '127.0.0.1' || ip.startsWith('192.168.') || ip.startsWith('10.')) {
                    this.cityCache[ip] = 'Localhost / Jaringan Internal';
                    return;
                }
                
                setTimeout(() => {
                    fetch(`https://get.geojs.io/v1/ip/geo/${ip}.json`)
                        .then(res => {
                            if(!res.ok) throw new Error('API error');
                            return res.json();
                        })
                        .then(data => {
                            let kota = (data.city && data.city !== '-') ? data.city : 
                                       (data.region && data.region !== '-') ? data.region : null;
                                       
                            if (!kota && data.timezone) {
                                let tzParts = data.timezone.split('/');
                                if (tzParts.length > 1) {
                                    kota = tzParts[tzParts.length - 1].replace('_', ' ');
                                }
                            }
                        
                            let kodeNegara = (data.country_code && data.country_code !== '-') ? data.country_code : 
                                             (data.country && data.country !== '-') ? data.country : null;
                        
                            if (kota && kodeNegara) {
                                this.cityCache[ip] = `${kota}, ${kodeNegara}`;
                            } else if (kodeNegara) {
                                this.cityCache[ip] = kodeNegara;
                            } else if (kota) {
                                this.cityCache[ip] = kota;
                            } else {
                                this.cityCache[ip] = 'Lokasi tidak diketahui';
                            }
                        })
                        .catch(() => {
                            this.cityCache[ip] = 'Gagal memuat peta lokasi';
                        });
                }, delay);
                delay += 100; // GeoJS sangat stabil tanpa rate-limit yang ketat, delay bisa diperkecil
            });
        },
        isOnline(lastActivity) {
            if (!lastActivity) return false;
            const lastDate = new Date(lastActivity.replace(' ', 'T')).getTime();
            const now = new Date().getTime();
            const diffMinutes = (now - lastDate) / (1000 * 60);
            return diffMinutes <= 60 && diffMinutes >= -5;
        },
        async hentikanSesi(id_session) {
            if (!confirm('Apakah Anda yakin ingin menghentikan sesi ini secara paksa?')) return;
            
            try {
                const response = await fetch('/api/admin/kill-session', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_session: id_session })
                });
                
                if (response.ok) {
                    this.sessionList = this.sessionList.filter(s => s.id_session !== id_session);
                    alert('Sesi berhasil dihentikan.');
                } else {
                    alert('Gagal menghentikan sesi.');
                }
            } catch (e) {
                alert('Terjadi kesalahan jaringan.');
                console.error(e);
            }
        },
        startAutoRefresh() {
            this.refreshTimer = setInterval(() => {
                this.timeRemaining--;
                this.progressWidth = (this.timeRemaining / 60) * 100;
                
                if (this.timeRemaining <= 0) {
                    window.location.reload();
                }
            }, 1000);
        }
    },
    mounted() {
        this.checkLoginState();
        this.loadGeoLocation();
        this.startAutoRefresh();
    },
    beforeUnmount() {
        if (this.refreshTimer) clearInterval(this.refreshTimer);
    }
}).mount('#ut');

const { createApp } = Vue;

createApp({
    data() {
        return {
            adminUser: null,
            logoutModalInstance: null,
            metodeList: [],
            paketList: [],
            formMetode: { id: null, nama_bank: '', nomor_rekening: '', atas_nama: '', logo_icon: '', is_active: 1 },
            formPaket: { id: null, nama_paket: '', nominal: '', deskripsi: '', is_active: 1 },
            modalMetodeInstance: null,
            modalPaketInstance: null
        }
    },
    methods: {
        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        },
        checkAdminSession() {
            const savedUser = localStorage.getItem('ut_user_session');
            if (savedUser) {
                const user = JSON.parse(savedUser);
                if (user.role === 'admin' || user.id_user == 1) {
                    this.adminUser = user;
                } else {
                    window.location.href = '/';
                }
            } else {
                window.location.href = '/';
            }
        },
        logout() {
            if (!this.logoutModalInstance) {
                const el = document.getElementById('logoutModal');
                if(el) this.logoutModalInstance = new bootstrap.Modal(el);
            }
            if(this.logoutModalInstance) this.logoutModalInstance.show();
        },
        async fetchData() {
            try {
                const [resMetode, resPaket] = await Promise.all([
                    fetch('/api/donasi/metode').then(r => r.json()),
                    fetch('/api/donasi/paket').then(r => r.json())
                ]);
                this.metodeList = resMetode;
                this.paketList = resPaket;
            } catch (err) {
                console.error('Failed to load data:', err);
            }
        },
        showMetodeModal(data = null) {
            if (data) {
                this.formMetode = { ...data, is_active: Number(data.is_active) };
            } else {
                this.formMetode = { id: null, nama_bank: '', nomor_rekening: '', atas_nama: '', logo_icon: '', is_active: 1 };
            }
            if (!this.modalMetodeInstance) {
                this.modalMetodeInstance = new bootstrap.Modal(document.getElementById('modalMetode'));
            }
            this.modalMetodeInstance.show();
        },
        async saveMetode() {
            const isEdit = !!this.formMetode.id;
            const url = isEdit ? `/api/donasi/metode/update/${this.formMetode.id}` : '/api/donasi/metode/create';
            const method = isEdit ? 'PUT' : 'POST';
            
            try {
                const res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.formMetode)
                });
                if (res.ok) {
                    this.modalMetodeInstance.hide();
                    this.fetchData();
                } else {
                    alert('Gagal menyimpan metode');
                }
            } catch (err) {
                alert('Kesalahan jaringan');
            }
        },
        async deleteMetode(id) {
            if (!confirm('Yakin hapus metode ini?')) return;
            try {
                const res = await fetch(`/api/donasi/metode/delete/${id}`, { method: 'DELETE' });
                if (res.ok) this.fetchData();
            } catch (err) { }
        },
        showPaketModal(data = null) {
            if (data) {
                this.formPaket = { ...data, is_active: Number(data.is_active) };
            } else {
                this.formPaket = { id: null, nama_paket: '', nominal: '', deskripsi: '', is_active: 1 };
            }
            if (!this.modalPaketInstance) {
                this.modalPaketInstance = new bootstrap.Modal(document.getElementById('modalPaket'));
            }
            this.modalPaketInstance.show();
        },
        async savePaket() {
            const isEdit = !!this.formPaket.id;
            const url = isEdit ? `/api/donasi/paket/update/${this.formPaket.id}` : '/api/donasi/paket/create';
            const method = isEdit ? 'PUT' : 'POST';
            
            try {
                const res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.formPaket)
                });
                if (res.ok) {
                    this.modalPaketInstance.hide();
                    this.fetchData();
                } else {
                    alert('Gagal menyimpan paket');
                }
            } catch (err) {
                alert('Kesalahan jaringan');
            }
        },
        async deletePaket(id) {
            if (!confirm('Yakin hapus paket ini?')) return;
            try {
                const res = await fetch(`/api/donasi/paket/delete/${id}`, { method: 'DELETE' });
                if (res.ok) this.fetchData();
            } catch (err) { }
        }
    },
    mounted() {
        this.checkAdminSession();
        this.fetchData();
    }
}).mount('#ut');

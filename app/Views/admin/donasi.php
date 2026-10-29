<?= $this->extend('layout/admin_layout') ?>

<?= $this->section('content') ?>
<main class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark fw-bold"><i class="bi bi-wallet2 me-2 text-primary"></i>Kelola Donasi & Premium</h2>
    </div>

    <div class="row">
        <!-- Metode Pembayaran -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Metode Pembayaran</h5>
                    <button class="btn btn-sm btn-primary rounded-pill" @click="showMetodeModal()">
                        <i class="bi bi-plus-lg"></i> Tambah
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Metode</th>
                                    <th>Rekening</th>
                                    <th>A.N</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="m in metodeList" :key="m.id">
                                    <td>
                                        <i :class="m.logo_icon || 'bi bi-bank'" class="text-primary me-2"></i>
                                        {{ m.nama_bank }}
                                    </td>
                                    <td>{{ m.nomor_rekening }}</td>
                                    <td>{{ m.atas_nama }}</td>
                                    <td>
                                        <span v-if="m.is_active == 1" class="badge bg-success">Aktif</span>
                                        <span v-else class="badge bg-danger">Nonaktif</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm text-warning" @click="showMetodeModal(m)"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm text-danger" @click="deleteMetode(m.id)"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paket Harga -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Daftar Paket Harga</h5>
                    <button class="btn btn-sm btn-primary rounded-pill" @click="showPaketModal()">
                        <i class="bi bi-plus-lg"></i> Tambah
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Nama Paket</th>
                                    <th>Nominal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="p in paketList" :key="p.id">
                                    <td>{{ p.nama_paket }}</td>
                                    <td>Rp {{ formatRupiah(p.nominal) }}</td>
                                    <td>
                                        <span v-if="p.is_active == 1" class="badge bg-success">Aktif</span>
                                        <span v-else class="badge bg-danger">Nonaktif</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm text-warning" @click="showPaketModal(p)"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm text-danger" @click="deletePaket(p.id)"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Modal Metode -->
    <div class="modal fade" id="modalMetode" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title fw-bold">{{ formMetode.id ? 'Edit' : 'Tambah' }} Metode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Bank / eWallet</label>
                        <input type="text" class="form-control" v-model="formMetode.nama_bank" placeholder="Misal: GoPay, BCA">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nomor Rekening / HP</label>
                        <input type="text" class="form-control" v-model="formMetode.nomor_rekening">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Atas Nama</label>
                        <input type="text" class="form-control" v-model="formMetode.atas_nama">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Bootstrap Icon (Opsional)</label>
                        <input type="text" class="form-control" v-model="formMetode.logo_icon" placeholder="Misal: bi-wallet-fill">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="aktifMetode" v-model="formMetode.is_active" :true-value="1" :false-value="0">
                        <label class="form-check-label" for="aktifMetode">Aktifkan Metode Ini</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-primary px-4 rounded-pill shadow-sm" @click="saveMetode">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Paket -->
    <div class="modal fade" id="modalPaket" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title fw-bold">{{ formPaket.id ? 'Edit' : 'Tambah' }} Paket Harga</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Paket</label>
                        <input type="text" class="form-control" v-model="formPaket.nama_paket" placeholder="Misal: Akses Selamanya">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nominal (Rp)</label>
                        <input type="number" class="form-control" v-model="formPaket.nominal">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Deskripsi Keuntungan</label>
                        <textarea class="form-control" v-model="formPaket.deskripsi" rows="3"></textarea>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="aktifPaket" v-model="formPaket.is_active" :true-value="1" :false-value="0">
                        <label class="form-check-label" for="aktifPaket">Aktifkan Paket Ini</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-primary px-4 rounded-pill shadow-sm" @click="savePaket">Simpan</button>
                </div>
            </div>
        </div>
    </div>

</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/js/admin/donasi.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>

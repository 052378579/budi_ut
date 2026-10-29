<?= $this->extend('layout/admin_layout') ?>

<?= $this->section('extra_styles') ?>
<link rel="stylesheet" href="/css/admin/user.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<main class="container pb-5 user-container">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark fw-bold"><i class="bi bi-people-fill me-2 text-primary"></i>Kelola Users</h2>
        <button class="btn btn-secondary shadow-sm rounded-pill px-4 fw-bold" @click="fetchUsers" :disabled="isLoading">
            {{ isLoading ? 'Memuat...' : '🔄 Segarkan Data' }}
        </button>
    </div>

    <div class="row mb-3 align-items-center gy-3">
        <div class="col-12 col-md-auto d-flex align-items-center">
            <label class="me-2 small fw-bold text-muted">Tampilkan:</label>
            <select class="form-select form-select-sm w-auto rounded-pill shadow-sm" v-model="itemsPerPage" @change="currentPage = 1">
                <option v-for="opt in perPageOptions" :key="opt" :value="opt">{{ opt }}</option>
            </select>
        </div>
        
        <div class="col-12 col-md d-flex justify-content-md-center">
            <div class="input-group input-group-sm w-75 shadow-sm search-wrapper">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control border-start-0 focus-ring" v-model="searchQuery" @input="currentPage = 1" placeholder="Cari nama mahasiswa atau nomor handphone...">
            </div>
        </div>
        
        <div class="col-12 col-md-6 text-md-end text-center">
            <span class="small text-muted bg-white px-3 py-2 rounded-pill shadow-sm">
                Total Pendaftar: <strong class="text-primary fs-6">{{ filteredUsers.length }}</strong> Pengguna
            </span>
        </div>
    </div>

    <div class="card admin-card border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Nama Lengkap</th>
                            <th>Nomor Handphone</th>
                            <th class="text-center">Total Favorit</th>
                            <th class="text-center">Status Premium</th>
                            <th>Tanggal Daftar</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="isLoading">
                            <td colspan="7" class="text-center py-5 text-primary">
                                <div class="spinner-border text-primary" role="status"></div>
                                <div class="mt-2 text-muted">Memuat data pendaftaran mahasiswa baru...</div>
                            </td>
                        </tr>
                        <tr v-else-if="filteredUsers.length === 0">
                            <td colspan="7" class="text-center py-5 text-muted">
                                Tidak ada data pendaftaran mahasiswa yang cocok dengan pencarian.
                            </td>
                        </tr>

                        <tr v-for="(user, index) in paginatedList" :key="user.id_user">
                            <td class="ps-4 fw-bold text-secondary">{{ index + 1 }}</td>
                            <td class="fw-semibold text-dark">
                                {{ user.nama_user }}
                                <span v-if="user.role === 'admin' || user.id_user == 1" class="badge badge-soft-danger badge-pill ms-1">Admin</span>
                            </td>
                            <td><code class="fs-6 phone-badge">{{ user.no_hp }}</code></td>
                            <td class="text-center">
                                <span class="badge badge-soft-danger badge-pill px-3 py-2 shadow-sm">
                                    <i class="bi bi-heart-fill me-1"></i> {{ user.total_favorit || 0 }} BMP
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge" :class="user.is_premium == 1 ? 'bg-warning text-dark' : 'bg-secondary'">
                                    <i class="bi" :class="user.is_premium == 1 ? 'bi-star-fill' : 'bi-person-fill'"></i>
                                    {{ user.is_premium == 1 ? 'Premium' : 'Gratis' }}
                                </span>
                            </td>
                            <td>{{ formatTanggal(user.created_at) }}</td>
                            <td class="text-center text-nowrap">
                                <button class="btn btn-action text-primary shadow-sm me-1" 
                                        :disabled="user.role === 'admin' || user.id_user == 1"
                                        @click="editUser(user)" title="Edit Status">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-action text-danger shadow-sm" 
                                        :disabled="user.role === 'admin' || user.id_user == 1"
                                        @click="hapusUser(user.id_user, user.nama_user)" title="Hapus Akun Permanen">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white border-0 py-3" v-if="filteredUsers.length > 0 && itemsPerPage !== 'Semua'">
            <nav>
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                        <button class="page-link shadow-sm px-3 pagination-prev" @click="currentPage--">Sebelumnya</button>
                    </li>
                    <li v-for="(page, index) in visiblePages" :key="index" class="page-item" :class="{ active: currentPage === page, disabled: page === '...' }">
                        <button v-if="page !== '...'" class="page-link shadow-sm" @click="currentPage = page">{{ page }}</button>
                        <span v-else class="page-link shadow-sm text-muted bg-light">...</span>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                        <button class="page-link shadow-sm px-3 pagination-next" @click="currentPage++">Berikutnya</button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 bg-light pb-2">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square text-primary fs-4"></i> Edit Pengguna
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nama Lengkap</label>
                        <input type="text" class="form-control bg-light" v-model="formUser.nama_user" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nomor Handphone</label>
                        <input type="text" class="form-control bg-light" v-model="formUser.no_hp" disabled>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-dark">Status Akun</label>
                        <select class="form-select rounded-3 shadow-none border-secondary-subtle" v-model="formUser.is_premium">
                            <option value="0">Gratis (Regular)</option>
                            <option value="1">Premium (VIP)</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm" @click="savePremium" :disabled="isSaving">
                        <span v-if="isSaving"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Menyimpan...</span>
                        <span v-else><i class="bi bi-save-fill me-1"></i> Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/js/admin/user.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
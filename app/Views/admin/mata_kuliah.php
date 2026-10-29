<?= $this->extend('layout/admin_layout') ?>

<?= $this->section('extra_styles') ?>
<link rel="stylesheet" href="/css/admin/mk.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<main class="container pb-5 mk-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark fw-bold"><i class="bi bi-journal-bookmark-fill me-2 text-primary"></i>Kelola Mata Kuliah</h2>
        <button class="btn btn-primary shadow-sm rounded-pill px-4 fw-bold" @click="showModal()">
            <i class="bi bi-plus-lg"></i> Tambah MK
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
                <input type="text" class="form-control border-start-0 focus-ring" v-model="searchQuery" @input="currentPage = 1" placeholder="Cari Kode BMP, Kode MK, atau Nama MK...">
            </div>
        </div>

        <div class="col-12 col-md-auto text-md-end text-center">
            <span class="small text-muted bg-white px-3 py-2 rounded-pill shadow-sm">
                Total: <strong>{{ filteredListMK.length }}</strong> Mata Kuliah
            </span>
        </div>
    </div>

    <div class="card admin-card border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th class="ps-4">Cover</th>
                            <th>Kode BMP</th>
                            <th>Kode MK</th>
                            <th>Mata Kuliah</th>
                            <th>Program Studi / SKS</th>
                            <th>Tema</th>
                            <th class="text-center action-column">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="isLoading">
                            <td colspan="7" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <div class="mt-2 text-muted">Memuat data modul...</div>
                            </td>
                        </tr>
                        <tr v-else-if="filteredListMK.length === 0">
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                                Data mata kuliah tidak ditemukan.
                            </td>
                        </tr>

                        <tr v-for="mk in paginatedList" :key="mk.id_mk">
                            <td class="ps-4">
                                <img :src="'/api/dokumen/cover/' + mk.kode_bmp" 
                                     @error="handleImageError"
                                     class="border shadow-sm cover-img"
                                     loading="lazy"
                                     alt="BMP Cover">
                            </td>
                            <td><span class="fw-bold text-dark">{{ mk.kode_bmp }}</span></td>
                            <td><code class="kode-badge">{{ mk.kode_mk }}</code></td>
                            <td class="fw-semibold">{{ mk.nama_mk }}</td>
                            <td>
                                <div v-if="mk.program_studi" class="mb-2">
                                    <span class="badge badge-soft-secondary badge-pill me-1" v-for="prodi in mk.program_studi.split(',')" :key="prodi">
                                        {{ prodi.trim() }}
                                    </span>
                                </div>
                                <span class="badge badge-soft-info badge-pill">{{ mk.jumlah_sks }} SKS</span>
                            </td>
                            <td>
                                <span :class="['badge badge-pill shadow-sm theme-badge', mk.theme_color.replace(' text-white', '').replace(' text-dark', ''), getThemeTextClass(mk.theme_color)]">{{ mk.theme_color.replace('bg-', '').replace(' text-white', '').replace(' text-dark', '') }}</span>
                            </td>
                            <td class="text-center">
                                <a :href="'/' + mk.kode_mk" target="_blank" class="btn btn-action text-primary shadow-sm me-1" title="Lihat Halaman">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-action text-warning shadow-sm me-1" @click="showModal(mk)" title="Edit Data & Cover">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-action text-danger shadow-sm" @click="deleteMK(mk.id_mk, mk.nama_mk)" title="Hapus Permanen">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white border-0 py-3" v-if="filteredListMK.length > 0 && itemsPerPage !== 'Semua'">
            <nav>
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                        <button class="page-link shadow-sm px-3 pagination-prev" @click="currentPage--">Sebelumnya</button>
                    </li>
                    <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: currentPage === page }">
                        <button class="page-link shadow-sm" @click="currentPage = page">{{ page }}</button>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                        <button class="page-link shadow-sm px-3 pagination-next" @click="currentPage++">Berikutnya</button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="modal fade" id="modalMK" tabindex="-1" aria-hidden="true" ref="modalMK">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg modal-content-custom">
                <div class="modal-header bg-light border-0 modal-header-custom">
                    <h5 class="modal-title fw-bold">
                        {{ isEdit ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah Baru' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div v-if="errorMessages.length > 0" class="alert alert-danger p-2 mb-3 rounded shadow-sm">
                        <ul class="mb-0 ps-3 small">
                            <li v-for="(msg, index) in errorMessages" :key="index">{{ msg }}</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Kode BMP (Buku Materi Pokok)</label>
                        <input type="text" class="form-control" v-model="form.kode_bmp" placeholder="Contoh: MKWI4202">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Kode MK (Mata Kuliah)</label>
                        <input type="text" class="form-control" v-model="form.kode_mk" placeholder="Contoh: STSI4201">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Mata Kuliah</label>
                        <input type="text" class="form-control" v-model="form.nama_mk">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label d-block fw-bold small">Program Studi / Fakultas</label>
                        <div class="border p-2 rounded bg-light">
                            <div class="form-check form-check-inline" v-for="opt in prodiOptions" :key="opt">
                                <input class="form-check-input" type="checkbox" :id="'chk-'+opt" :value="opt" v-model="form.program_studi">
                                <label class="form-check-label small" :for="'chk-'+opt">{{ opt }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Jumlah SKS</label>
                        <select class="form-select" v-model="form.jumlah_sks">
                            <option value="2">2 SKS (Max 6 Modul)</option>
                            <option value="3">3 SKS (Max 9 Modul)</option>
                            <option value="4">4 SKS (Max 12 Modul)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Warna Tema</label>
                        <select class="form-select" v-model="form.theme_color">
                            <option value="bg-primary">Biru (Primary)</option>
                            <option value="bg-secondary">Abu-abu (Secondary)</option>
                            <option value="bg-success">Hijau (Success)</option>
                            <option value="bg-danger">Merah (Danger)</option>
                            <option value="bg-warning">Kuning (Warning)</option>
                            <option value="bg-info">Cyan (Info)</option>
                            <option value="bg-light">Terang (Light)</option>
                            <option value="bg-dark">Gelap (Dark)</option>
                            <option value="bg-primary-subtle">Biru Lembut (Primary Subtle)</option>
                            <option value="bg-secondary-subtle">Abu Lembut (Secondary Subtle)</option>
                            <option value="bg-success-subtle">Hijau Lembut (Success Subtle)</option>
                            <option value="bg-danger-subtle">Merah Lembut (Danger Subtle)</option>
                            <option value="bg-warning-subtle">Kuning Lembut (Warning Subtle)</option>
                            <option value="bg-info-subtle">Cyan Lembut (Info Subtle)</option>
                            <option value="bg-light-subtle">Terang Lembut (Light Subtle)</option>
                            <option value="bg-dark-subtle">Gelap Lembut (Dark Subtle)</option>
                            <option value="bg-black">Hitam Pekat (Black)</option>
                            <option value="bg-white">Putih Bersih (White)</option>
                            <option value="bg-body">Latar Standar (Body)</option>
                            <option value="bg-body-secondary">Latar Sekunder (Body Secondary)</option>
                            <option value="bg-body-tertiary">Latar Tersier (Body Tertiary)</option>
                            <option value="bg-transparent">Transparan (Transparent)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-warning"><i class="bi bi-star-fill me-1"></i>Modul Premium (Freemium)</label>
                        <input type="text" class="form-control" v-model="form.modul_premium" placeholder="Contoh: 9,10,11 (Kosongkan jika gratis semua)">
                        <div class="form-text small">Masukkan nomor modul yang akan dikunci khusus pengguna Premium. Pisahkan dengan tanda koma tanpa spasi.</div>
                    </div>

                    <div class="mb-3" v-if="isEdit">
                        <label class="form-label fw-bold small text-primary"><i class="bi bi-image me-1"></i>Ganti Gambar Cover (.webp)</label>
                        <input type="file" class="form-control" ref="coverFile" accept="image/webp" @change="onFileSelected">
                        <div class="form-text small">File fisik akan otomatis masuk ke folder terproteksi <code>writable/MK/</code></div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light modal-footer-custom">
                    <button type="button" class="btn btn-secondary rounded-pill shadow-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" @click="saveMK" :disabled="isSaving">
                        {{ isSaving ? 'Memproses...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/js/admin/mk.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
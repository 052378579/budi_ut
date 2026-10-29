<div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3 mt-5" style="z-index: 9999; width: max-content; max-width: 90vw;">
        <div id="appToast" class="toast align-items-center bg-white border border-2 shadow-lg" 
             :class="toastType === 'success' ? 'border-success text-success' : 'border-danger text-danger'"
             role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000" style="border-radius: 12px;">
            <div class="d-flex">
                <div class="toast-body fs-6 d-flex align-items-center px-3 py-2 fw-bold">
                    <i class="bi fs-5 me-3" :class="toastType === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'"></i>
                    <span>{{ toastMessage }}</span>
                </div>
                <button type="button" class="btn-close me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="logoutModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 bg-light pb-2">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-box-arrow-right text-danger fs-4"></i> Konfirmasi Keluar
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-3">
                    <p class="text-secondary small mb-4">Anda yakin ingin keluar dari aplikasi <b>Tes Formatif</b>?</p>
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill fw-bold shadow-sm w-50" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger rounded-pill fw-bold shadow-sm w-50" @click="confirmLogout" :disabled="isLoggingOut">
                            <span v-if="isLoggingOut"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Keluar...</span>
                            <span v-else>Ya, Keluar</span>
                        </button>
                    </div>
                </div>
                
                <!-- Progressbar Logout -->
                <div class="progress" style="height: 6px; border-radius: 0 0 20px 20px;" v-if="isLoggingOut">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="saranModal" tabindex="-1" aria-hidden="true" ref="saranModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 bg-light pb-2">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-envelope-paper-heart text-primary fs-4"></i> Kotak Saran
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-3">
                    <p class="text-secondary small mb-4">Masukan Anda sangat berarti bagi pengembangan aplikasi <b>Tes Formatif</b>. Jangan ragu untuk melaporkan <i>bug</i> atau mengusulkan fitur baru!</p>
                    
                    <form @submit.prevent="kirimSaran">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-dark">Kategori Laporan</label>
                            <select v-model="saranForm.kategori" class="form-select rounded-3 shadow-none border-secondary-subtle" required>
                                <option value="bug">🐛 Laporan Eror / Bug</option>
                                <option value="fitur">💡 Usulan Fitur Baru</option>
                                <option value="desain">🎨 Kritik Desain / Tampilan</option>
                                <option value="konten">📝 Kesalahan Konten / Soal</option>
                                <option value="lainnya">📌 Lainnya</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-dark">Detail Masukan</label>
                            <textarea v-model="saranForm.isi_saran" class="form-control rounded-3 shadow-none border-secondary-subtle" rows="4" placeholder="Sampaikan secara rinci Masukan atau Kendala yang Anda alami..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm" :disabled="isSubmittingSaran">
                            <span v-if="isSubmittingSaran"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Mengirim...</span>
                            <span v-else><i class="bi bi-send-fill me-1"></i> Kirim Saran</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="py-3 bg-white border-top mt-auto">      
        <div class="container">
            <div class="row align-items-center">
                
                <div class="col-12 col-md-auto text-center text-md-start mb-3 mb-md-0">
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2" style="font-size: 0.75rem;">
                        <a href="/prestasi" class="text-muted text-decoration-none hover-primary text-warning">Prestasi</a>
                        <span class="text-muted opacity-50">&bull;</span>
                        <a href="/saran" class="text-muted text-decoration-none hover-primary">Saran</a>
                        <span class="text-muted opacity-50">&bull;</span>
                        <a href="/privacy-policy" class="text-muted text-decoration-none hover-primary">Kebijakan</a>
                        <span class="text-muted opacity-50">&bull;</span>
                        <a href="/contact-us" class="text-muted text-decoration-none hover-primary">Hubungi</a>
                    </div>
                </div>
                
                <div class="col-12 col-md text-center">
                    <p class="footer-heart small mb-0 mt-1 mt-md-2">
                        Didedikasikan untuk 
                        <g-emoji class="g-emoji" alias="heart" fallback-src="https://github.githubassets.com/images/icons/emoji/unicode/2764.png">
                            <img class="emoji" alt="heart" height="20" width="20" src="https://github.githubassets.com/images/icons/emoji/unicode/2764.png">
                        </g-emoji> 
                        Rekan<sup>2</sup> <a href="https://ut.ac.id" target="_blank" rel="noopener noreferrer">UnivTerbuka</a>
                    </p>
                </div>
                
                <div class="col-12 col-md-auto text-center text-md-end mt-3 mt-md-0">
                    <div class="d-flex flex-column align-items-center align-items-md-end">
                        <span class="text-secondary" style="font-size: 0.8rem;">
                            v.2.6.18 | <a href="/unduh" class="text-danger text-decoration-none fw-bold" style="transition: opacity 0.2s;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">Unduh APK</a>
                        </span>
                    </div>
                </div>
                
            </div>
        </div>
    </footer>

    <?php
        // Kueri mandiri agar indikator tetap berjalan di semua rute tanpa modifikasi tiap Controller
        $db = \Config\Database::connect();
        $onlineUsersCount = $db->table('user_sessions')
            ->where('last_activity >=', date('Y-m-d H:i:s', strtotime('-60 minutes')))
            ->where('last_activity <=', date('Y-m-d H:i:s', strtotime('+5 minutes')))
            ->countAllResults();
    ?>
    <!-- Float: User Online -->
    <div class="position-fixed" style="bottom: 85px; left: 25px; z-index: 1050; pointer-events: none;">
        <div class="bg-white rounded-pill shadow-lg d-flex align-items-center px-4 py-2 border-0" style="box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;">
            <div class="position-relative d-flex align-items-center justify-content-center me-3">
                <div class="spinner-grow text-success position-absolute" role="status" style="width: 1.5rem; height: 1.5rem; opacity: 0.4;"></div>
                <div class="bg-success rounded-circle" style="width: 0.75rem; height: 0.75rem;"></div>
            </div>
            <div class="d-flex flex-column lh-1" style="margin-top: 1px;">
                <span class="fw-bolder text-dark fs-5" style="letter-spacing: -0.5px;"><?= isset($onlineUsersCount) ? number_format($onlineUsersCount) : 0 ?></span>
                <span class="text-secondary fw-bold mt-1" style="font-size: 0.6rem; letter-spacing: 0.5px; text-transform: uppercase;">User Online</span>
            </div>
        </div>
    </div>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>

<script src="/js/global_mixin.js?v=<?= time() ?>"></script>

<?= $this->renderSection('scripts') ?>

</body>
</html>
<div class="modal fade" id="logoutModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" ref="logoutModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 text-dark justify-content-center pt-4 pb-0">
                    <div class="text-center">
                        <div class="display-5 mb-2" :class="navThemeColor ? navThemeColor.replace('bg-', 'text-') : 'text-primary'">
                            <i class="bi bi-door-open-fill"></i>
                        </div>
                        <h5 class="modal-title fw-bold">Akhiri Sesi Belajar?</h5>
                    </div>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="text-secondary mb-0">Apakah Anda yakin ingin keluar? Pastikan ujian Anda telah selesai dan nilainya tersimpan.</p>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-center bg-light gap-2 pt-3 pb-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn rounded-pill px-4 fw-bold text-white shadow-sm" :class="navThemeColor || 'bg-primary'" @click="confirmLogout">Ya, Keluar</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="py-3 bg-white border-top mt-auto">      
        <div class="container">
            <div class="row align-items-center">                
                
                <div class="col-md-3 d-none d-md-block"></div>                
                
                <div class="col-12 col-md-6 text-center">
                    <p class="footer-heart small mb-0 mt-1 mt-md-2">
                        Didedikasikan untuk 
                        <g-emoji class="g-emoji" alias="heart" fallback-src="https://github.githubassets.com/images/icons/emoji/unicode/2764.png">
                            <img class="emoji" alt="heart" height="20" width="20" src="https://github.githubassets.com/images/icons/emoji/unicode/2764.png">
                        </g-emoji> 
                        Rekan<sup>2</sup> <a href="https://ut.ac.id" target="_blank" rel="noopener noreferrer">UnivTerbuka</a>
                    </p>
                </div>
                
                <div class="col-12 col-md-3 text-center text-md-end mt-3 mt-md-0">
                    <span class="text-secondary" style="font-size: 0.8rem;">
                        v.2.6.1 | <a href="/unduh" class="text-danger text-decoration-none fw-bold" style="transition: opacity 0.2s;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">Unduh APK</a>
                    </span>
                </div>
                
            </div>
        </div>
    </footer>

</div> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>

<?= $this->renderSection('scripts') ?>

</body>
</html>
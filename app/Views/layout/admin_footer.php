<div class="modal fade" id="logoutModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" ref="logoutModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 text-dark justify-content-center pb-0">
                    <h5 class="modal-title fw-bold w-100 text-center">Konfirmasi Sesi Admin</h5>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="text-secondary fs-5 mb-0">Apakah Anda yakin ingin keluar dari Panel Sistem Administrasi?</p>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-center bg-light pt-3 pb-3" style="border-radius: 0 0 20px 20px;">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" @click="confirmLogout">Ya, Keluar</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="py-4 bg-white border-top text-center mt-auto">      
        <p class="footer-heart small mb-0 mt-2">
            Panel Admin
            <img class="emoji" alt="heart" height="20" width="20" src="https://github.githubassets.com/images/icons/emoji/unicode/2764.png"> 
            Tes Formatif <a href="https://ut.ac.id" target="_blank" rel="noopener noreferrer">UT</a>
        </p>
    </footer>



</div> <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?= $this->renderSection('scripts') ?>

</body>
</html>
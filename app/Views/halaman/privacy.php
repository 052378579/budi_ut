<?= $this->extend('layout/personal_template') ?>

<?= $this->section('content') ?>

<header class="hero-bg text-center py-4">
    <div class="container">
        <h2 class="fw-bold text-dark mb-0">Privacy Policy</h2>
        <p class="text-secondary mt-2">Kebijakan Privasi UT Tes Formatif</p>
    </div>
</header>

<main class="container my-5 flex-grow-1">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5 text-secondary" style="line-height: 1.8;">
                    
                    <h4 class="fw-bold text-dark mb-4">Kebijakan Privasi</h4>
                    <p>Selamat datang di UT Tes Formatif. Kami sangat menghargai privasi Anda dan berkomitmen untuk melindungi informasi pribadi Anda. Dokumen ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi data Anda saat menggunakan situs kami.</p>

                    <h5 class="fw-bold text-dark mt-5 mb-3">1. Informasi yang Kami Kumpulkan</h5>
                    <p>Kami hanya mengumpulkan informasi yang diperlukan untuk memberikan layanan edukasi yang optimal, antara lain:</p>
                    <ul>
                        <li><strong>Data Profil:</strong> Nama dan Nomor HP (digunakan eksklusif untuk sesi ujian Anda).</li>
                        <li><strong>Data Penggunaan:</strong> Skor simulasi dan riwayat pengerjaan kuis untuk keperluan analitik personal Anda.</li>
                    </ul>

                    <h5 class="fw-bold text-dark mt-5 mb-3">2. Penggunaan Cookie (Cookies)</h5>
                    <p>Situs ini menggunakan <em>cookies</em> dan teknologi pelacakan lokal (seperti <em>localStorage</em>) untuk memastikan Anda tetap masuk (login) selama sesi belajar dan menyimpan preferensi tema atau pengaturan filter Mata Kuliah. Anda dapat mematikan <em>cookie</em> melalui pengaturan peramban Anda, namun beberapa fitur CBT mungkin tidak berfungsi dengan baik.</p>

                    <h5 class="fw-bold text-dark mt-5 mb-3">3. Pihak Ketiga & Periklanan (Google AdSense)</h5>
                    <p>Situs kami berkolaborasi dengan penyedia layanan periklanan pihak ketiga, termasuk Google AdSense. Google menggunakan <em>cookie DART</em> untuk menayangkan iklan yang relevan kepada pengunjung berdasarkan kunjungan mereka ke situs kami dan situs lain di Internet. Pengguna dapat menyisih dari penggunaan <em>cookie DART</em> dengan mengunjungi <a href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener noreferrer">kebijakan privasi jaringan iklan dan konten Google</a>.</p>

                    <h5 class="fw-bold text-dark mt-5 mb-3">4. Keamanan Data</h5>
                    <p>Kami memprioritaskan keamanan informasi Anda. Data ujian dan profil disimpan secara aman menggunakan enkripsi standar industri. Kami tidak akan menjual atau membagikan data Nomor HP Anda kepada pihak pemasaran eksternal manapun.</p>

                    <h5 class="fw-bold text-dark mt-5 mb-3">5. Persetujuan</h5>
                    <p>Dengan menggunakan situs web kami, Anda dengan ini menyetujui Kebijakan Privasi kami dan menyetujui syarat-syaratnya.</p>
                    
                    <hr class="my-5 opacity-25">
                    <p class="text-center small text-muted">
                        Pembaruan Terakhir: <?= date('d M Y') ?>
                    </p>

                </div>
            </div>
        </div>
    </div>
</main>

<?= $this->section('scripts') ?>
<script>
    const { createApp } = Vue;
    const app = createApp({
        mixins: [globalMixin],
        data() { return {}; }
    });
    app.config.compilerOptions.isCustomElement = (tag) => tag === 'g-emoji';
    app.mount('#ut');
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <style>
        /* Latar Belakang Biru Pucat Khas Halaman Hasil SUO */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #e6f2fc; margin: 0; padding: 0; }
        
        /* Navigasi Atas */
        .top-nav { background-color: #f8f9fa; padding: 10px 20px; border-bottom: 2px solid #dee2e6; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .btn-back { background-color: #6c757d; color: white; border-radius: 20px; padding: 5px 20px; font-weight: bold; text-decoration: none; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;}
        .btn-back:hover { background-color: #5c636a; color: white;}
        
        /* Kontainer Utama Hasil */
        .result-container { text-align: center; margin-top: 50px; padding: 20px; }
        
        /* Tipografi SUO */
        .title-main { color: #3b82f6; font-weight: 800; font-size: 24px; margin-bottom: 5px; }
        .subtitle-info { color: #d97706; font-weight: 700; font-size: 15px; margin: 2px 0; }
        
        /* Skor Raksasa */
        .score-display { margin: 30px 0; border-bottom: 1px solid #cbd5e1; padding-bottom: 30px; }
        .score-number { font-size: 60px; font-weight: bold; color: #ea580c; }
        .score-text { font-size: 40px; font-weight: bold; color: #3b82f6; }
        
        /* Pesan Penutup & Logo */
        .closing-msg { color: #2563eb; font-weight: 800; font-size: 20px; margin-top: 30px; margin-bottom: 30px;}
        .ut-logo-area { display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .ut-logo-area img { width: 100px; margin-bottom: 10px; }
        .ut-text { color: #1e3a8a; font-weight: 800; font-size: 16px; line-height: 1.2; }
    </style>
</head>
<body>
<div id="suo-result-app">
    
    <div class="top-nav">
        <a href="/" class="btn-back">
            KEMBALI KE BERANDA
        </a>
    </div>

    <div class="result-container container">
        <h2 class="title-main">INFORMASI PEROLEHAN JUMLAH JAWABAN BENAR</h2>
        
        <p class="subtitle-info">(<?= session()->get('no_hp') ?: '082378579' ?>) <?= session()->get('nama_user') ?: 'Simulator' ?></p>
        <p class="subtitle-info">(<?= esc($mkData['kode_mk']) ?>) <?= esc($mkData['nama_mk']) ?></p>

        <div class="score-display">
            <span class="score-number">{{ report.benar }}</span> 
            <span class="score-text">DARI {{ report.total }} SOAL</span>
            <div class="mt-2" style="color: #1e3a8a; font-size: 24px; font-weight: bold;">Nilai Akhir: {{ report.nilaiAkhir || 0 }}%</div>
        </div>

        <!-- FITUR BARU: Tinjauan Evaluasi Jawaban Desktop -->
        <div class="evaluation-container text-start mx-auto" style="max-width: 800px; margin-bottom: 40px;" v-if="report.detailJawaban && report.detailJawaban.length > 0">
            <h4 class="title-main mb-4" style="text-align: left; font-size: 20px;">Tinjauan Evaluasi Jawaban</h4>
            
            <div class="card border-0 shadow-sm" style="border-radius: 12px; margin-bottom: 15px;">
                <div class="card-header bg-white pt-3 pb-2 px-4 d-flex justify-content-between align-items-center" style="border-bottom: none;">
                    <span class="badge bg-secondary" style="font-size: 14px; padding: 6px 12px; border-radius: 20px;">
                        Nomor {{ report.detailJawaban[currentReviewIdx].no }} dari {{ report.total }}
                    </span>
                    <span :class="report.detailJawaban[currentReviewIdx].isCorrect ? 'text-success' : 'text-danger'" style="font-weight: 800; font-size: 16px;">
                        {{ report.detailJawaban[currentReviewIdx].isCorrect ? 'Jawaban Anda Benar' : 'Jawaban Anda Salah' }}
                    </span>
                </div>
                
                <div class="card-body p-4">
                    <p style="font-size: 16px; color: #1e293b; line-height: 1.5; margin-bottom: 20px;" v-html="report.detailJawaban[currentReviewIdx].soal"></p>
                    
                    <div class="d-flex flex-column gap-2">
                        <div v-for="optKey in ['A','B','C','D']" :key="optKey"
                             class="p-3 border"
                             style="border-radius: 8px; transition: 0.2s;"
                             :class="getOptionClass(report.detailJawaban[currentReviewIdx], optKey)">
                            <div class="d-flex align-items-center">
                                <strong class="me-3" style="min-width: 20px;">{{ optKey }}.</strong>
                                <span>{{ report.detailJawaban[currentReviewIdx]['opsi' + optKey] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kontrol Slider -->
            <div class="d-flex justify-content-between">
                <button @click="prevReview" :disabled="currentReviewIdx === 0" class="btn btn-outline-primary" style="border-radius: 20px; font-weight: bold; padding: 6px 20px;">
                    &laquo; Mundur
                </button>
                <button @click="nextReview" :disabled="currentReviewIdx === report.detailJawaban.length - 1" class="btn btn-primary shadow-sm" style="border-radius: 20px; font-weight: bold; padding: 6px 20px;">
                    Maju &raquo;
                </button>
            </div>
        </div>

        <div class="closing-msg">
            TERIMAKASIH ATAS PERAN SERTA ANDA DALAM MENGIKUTI UJIAN ONLINE
        </div>

        <!-- Bagian Logo Universitas Terbuka -->
        <div class="ut-logo-area">
            <!-- PERBAIKAN: Menggunakan link logo-sign.png sesuai instruksi -->
            <img src="https://staging-uo.ut.ac.id/statics/images/logo-sign.png" alt="Logo UT">
        </div>
    </div>
</div>

<script>
    const app = Vue.createApp({
        data() {
            return {
                kodeMk: '<?= esc($kodeMk) ?>',
                report: {
                    total: 30,
                    benar: 0,
                    nilaiAkhir: 0,
                    detailJawaban: []
                },
                currentReviewIdx: 0
            }
        },
        methods: {
            getOptionClass(item, optKey) {
                const currentOptValue = item['opsi' + optKey];

                if (currentOptValue === item.jawabanBenar) {
                    return 'bg-success text-white border-success shadow-sm fw-bold';
                }

                if (item.jawabanUser === currentOptValue && !item.isCorrect) {
                    return 'bg-danger text-white border-danger shadow-sm fw-bold';
                }

                return 'bg-light text-muted border-light opacity-75';
            },
            nextReview() {
                if (this.currentReviewIdx < this.report.detailJawaban.length - 1) {
                    this.currentReviewIdx++;
                }
            },
            prevReview() {
                if (this.currentReviewIdx > 0) {
                    this.currentReviewIdx--;
                }
            }
        },
        mounted() {
            // Tarik data evaluasi dari Local Session yang disimpan oleh halaman soal sebelumnya
            const savedReport = sessionStorage.getItem('suo_report_' + this.kodeMk);
            if(savedReport) {
                this.report = JSON.parse(savedReport);
                // Bersihkan sesi setelah berhasil ditarik untuk mencegah kecurangan render ulang
                sessionStorage.removeItem('suo_report_' + this.kodeMk);
            }
        }
    });
    app.mount('#suo-result-app');
</script>
</body>
</html>
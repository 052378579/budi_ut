<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #e9ecef; 
            margin: 0; 
            padding: 0; 
            overflow: hidden; 
            /* Mencegah Teks Diblok (Anti-Copas) */
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Tema Warna SUO Klasik */
        .bg-suo-light { background-color: #dbeafe; } 
        .bg-suo-medium { background-color: #bfdbfe; border-bottom: 2px solid #93c5fd; }
        .bg-suo-dark { background-color: #1e3a8a; color: white; }
        .text-suo-blue { color: #1d4ed8; }
        
        /* Layout Grid Utama */
        .layout-wrapper { display: flex; height: calc(100vh - 175px); }
        .sidebar-nav { width: 150px; background-color: #f8f9fa; border-right: 1px solid #dee2e6; overflow-y: auto; }
        .main-content { flex-grow: 1; background-color: #ffffff; padding: 40px; overflow-y: auto; box-shadow: inset 0px 0px 10px rgba(0,0,0,0.05); }
        
        /* Gaya Tombol Navigasi Kiri */
        .q-btn { display: flex; justify-content: space-between; align-items: center; width: 100%; padding: 6px 15px; border: none; background: transparent; font-size: 14px; color: #495057; text-align: left; border-bottom: 1px solid #e9ecef; }
        .q-btn:hover { background-color: #e2e8f0; }
        .q-btn.active { background-color: #3b82f6; color: white; font-weight: bold; }
        .q-ans { font-weight: bold; color: #1d4ed8; }
        .q-btn.active .q-ans { color: #dbeafe; }

        /* Bilah Header Atas */
        .header-top { padding: 8px 20px; font-size: 13px; display: flex; justify-content: space-between; align-items: center; }
        .header-top strong { display: block; font-size: 15px; color: #0f172a; }
        
        /* Bilah Aksi (Tombol Jawaban) */
        .action-bar { padding: 10px 20px; display: flex; align-items: center; gap: 15px; }
        .btn-answer { font-weight: bold; padding: 5px 20px; border-radius: 4px; background-color: white; border: 1px solid #cbd5e1; color: #334155; }
        .btn-answer.selected { background-color: #1e3a8a; color: white; border-color: #1e3a8a; }
        .btn-orange { background-color: #f59e0b; color: white; font-weight: bold; border: none; border-radius: 4px; padding: 6px 15px; }
        
        /* Tombol Selesai Merah */
        .btn-selesai { background-color: #dc3545; color: white; font-weight: bold; padding: 6px 20px; border-radius: 4px; border: none; margin-left: auto; }
        
        /* Status Bar Bawah Action Bar */
        .status-bar { font-size: 13px; background-color: #f1f5f9; padding: 5px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;}
        .status-bar span.text-danger-custom { font-weight: bold; color: #dc3545; }
        
        /* Konten Soal */
        .question-text { 
            font-size: 15px; 
            line-height: 1.5; 
            color: #1e293b; 
            max-width: 800px; 
            margin: 0 auto; 
        }
        .option-list { list-style: none; padding-left: 0; margin-top: 15px; }
        .option-list li { 
            margin-bottom: 10px; 
            font-size: 15px; 
            cursor: pointer; 
            padding: 8px 14px; 
            border-radius: 6px;
            border: 1px solid transparent;
            transition: all 0.2s ease-in-out;
            display: flex;
            align-items: flex-start;
        }
        .option-list li:hover { background-color: #f1f5f9; border-color: #cbd5e1; }
        .option-list li.selected-option { 
            background-color: #dbeafe; 
            border-color: #3b82f6; 
            color: #1e3a8a; 
            font-weight: 500; 
        }

        /* PERBAIKAN: CSS Kustom untuk Badge Peralatan Ujian agar identik dengan referensi gambar */
        .badge-suo-red {
            background-color: #ef4444;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            font-weight: 500;
        }
        .badge-suo-green {
            background-color: #4CAF50; 
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            font-weight: 500;
        }
    </style>
</head>
<body>
<div id="suo-app">
    <header class="bg-suo-light header-top">
        <div class="d-flex align-items-center gap-3">
            <?php 
                $sessionNoHp = session()->get('no_hp') ?: '081313428000';
                $sessionNama = strtolower(session()->get('nama_user') ?: 'budi');
                $fileNameProfil = $sessionNoHp . '_' . $sessionNama . '.webp';
            ?>
            <img src="/api/dokumen/profil/<?= $fileNameProfil ?>" 
                 @error="event => event.target.src = 'https://placehold.co/50x50/1e3a8a/white?text=User'" 
                 alt="Foto Profil" 
                 loading="lazy"
                 class="rounded-circle shadow-sm" 
                 style="width:40px; height:40px; object-fit: cover;">
            <div>
                <strong><?= session()->get('no_hp') ?: '081313428000' ?></strong>
                <span class="text-muted"><?= session()->get('nama_user') ?: 'BUDI' ?></span>
            </div>
        </div>
        <div>
            <strong>(<?= esc($mkData['kode_mk']) ?>)</strong>
            <span class="text-muted"><?= esc($mkData['nama_mk']) ?></span>
        </div>
        <div>
            <strong><?= date('d F Y') ?></strong>
            <span class="text-muted">Sesi: 1</span>
        </div>
        <div>
            <strong>Objektif</strong>
            <span class="text-muted">(SIM) SIMULASI UJIAN</span>
        </div>
        <div class="text-end">
            <span class="text-muted d-block">Sisa Waktu</span>
            <strong class="fs-5 text-suo-blue">{{ formatTime(timeLeft) }}</strong>
        </div>
    </header>

    <div class="bg-suo-medium action-bar">
        <div class="text-suo-blue" style="font-size: 11px; width: 120px; text-align: center; line-height: 1.2;">
            Menjawab <strong>{{ answeredCount }}</strong> dari <strong>{{ questions.length }}</strong><br>
            No. &nbsp;&nbsp;&nbsp; Jawaban
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <span class="fw-bold text-suo-blue fs-5 me-2">JAWABAN:</span>
            <button v-for="opt in ['A','B','C','D']" :key="opt" 
                    @click="answerCurrent(opt)"
                    :class="['btn-answer', { 'selected': currentAnswer === opt }]">
                {{ opt }}
            </button>
        </div>
        
        <button class="btn-orange ms-4 small">PETUNJUK MENJAWAB SOAL UJIAN 📌</button>
        
        <button v-if="currentIndex === questions.length - 1" @click="bukaModalSelesai" class="btn-selesai">SELESAI 🚩</button>
    </div>

    <div class="status-bar">
        <div>NO. <span class="text-danger-custom">{{ currentIndex + 1 }}</span> DARI <strong>{{ questions.length }}</strong></div>
        <div class="text-primary small">Halaman: &lt; 1 / 1 &gt;</div>
        <div class="d-flex gap-2">
            <span class="badge-suo-red">Tutup Buku</span>
            <span class="badge-suo-green">
                Kalkulator 
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                </svg>
            </span>
            <span class="badge-suo-green">
                Tabel 
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                </svg>
            </span>
            <span class="badge-suo-red">
                Kamus 
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                </svg>
            </span>
        </div>
    </div>

    <div class="layout-wrapper">
        <div class="sidebar-nav">
            <button v-for="(q, index) in questions" :key="index"
                    @click="currentIndex = index"
                    :class="['q-btn', { 'active': currentIndex === index }]">
                <span>{{ index + 1 }}</span>
                <span class="q-ans">{{ answers[index] || '-' }}</span>
            </button>
        </div>
        
        <div class="main-content">
            <div v-if="loading" class="text-center mt-5 text-muted">
                <div class="spinner-border mb-3"></div>
                <p>Memuat butir soal...</p>
            </div>
            <div v-else-if="questions.length > 0" class="question-text">
                <p v-html="questions[currentIndex].pertanyaan"></p>
                <ul class="option-list">
                    <li v-for="(text, key) in questions[currentIndex].pilihan" 
                        :key="key" 
                        @click="answerCurrent(key)"
                        :class="{ 'selected-option': currentAnswer === key }">
                        <strong class="me-2 text-suo-blue" style="min-width: 24px;">{{ key }}.</strong>
                        <span v-html="text"></span>
                    </li>
                </ul>
            </div>
            <div v-else class="text-center mt-5">
                <div class="alert alert-warning text-center rounded-4 shadow-sm py-5 border-0 mx-auto" style="max-width: 600px; background-color: #fff3cd;">
                    <div class="display-4 mb-3">📂</div>
                    <h4 class="alert-heading fw-bold mb-2">Skema Ujian ESSAY (E)</h4>
                    <p class="mb-0 text-muted">Bank soal objektif tidak tersedia untuk mata kuliah ini.</p>
                    <a href="/" class="btn btn-warning rounded-pill px-4 mt-4 shadow-sm fw-bold">Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSelesai" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; border-top: 5px solid #1d4ed8 !important;">
                <div class="modal-body text-center p-4">
                    <h5 class="text-suo-blue fw-bold mb-3">Anda menjawab {{ answeredCount }} dari {{ questions.length }} soal.</h5>
                    <p class="fs-5 text-dark fw-medium mb-4">Apakah anda ingin menyatakan sudah selesai ujian?</p>
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-primary fw-bold px-4 shadow-sm" data-bs-dismiss="modal">TIDAK, SAYA INGIN LANJUT!</button>
                        <button type="button" @click="submitUjian" class="btn btn-danger fw-bold px-4 shadow-sm">IYA, SAYA SUDAH SELESAI!</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const app = Vue.createApp({
        data() {
            return {
                kodeMk: '<?= esc($kodeMk) ?>',
                questions: [],
                answers: [],
                currentIndex: 0,
                timeLeft: 5400, // 90 menit
                timer: null,
                modalInstance: null,
                loading: true
            }
        },
        computed: {
            currentAnswer() {
                return this.answers[this.currentIndex] || null;
            },
            answeredCount() {
                return this.answers.filter(a => a !== null && a !== undefined).length;
            }
        },
        methods: {
            formatTime(seconds) {
                const h = Math.floor(seconds / 3600);
                const m = Math.floor((seconds % 3600) / 60);
                const s = seconds % 60;
                return `${h}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
            },
            startTimer() {
                this.timer = setInterval(() => {
                    if(this.timeLeft > 0) this.timeLeft--;
                    else this.submitUjian();
                }, 1000);
            },
            async fetchQuestions() {
                this.loading = true;
                try {
                    const response = await fetch('/api/mk/uas-questions/' + this.kodeMk);
                    const data = await response.json();
                    if(response.ok && data && data.length > 0) {
                        this.questions = data;
                        this.answers = new Array(data.length).fill(null);
                        this.startTimer();
                    } else {
                        this.questions = [];
                    }
                } catch(e) {
                    alert("Koneksi terputus.");
                } finally {
                    this.loading = false;
                }
            },
            answerCurrent(opt) {
                this.answers[this.currentIndex] = opt;
            },
            bukaModalSelesai() {
                if(!this.modalInstance) {
                    this.modalInstance = new bootstrap.Modal(document.getElementById('modalSelesai'));
                }
                this.modalInstance.show();
            },
            handleUnload(event) {
                event.preventDefault();
                event.returnValue = '';
            },
            submitUjian() {
                // Melepas proteksi reload sebelum pindah halaman
                window.removeEventListener('beforeunload', this.handleUnload);

                let correct = 0;
                const resultsDetail = this.questions.map((q, idx) => {
                    const jawabanUserKey = this.answers[idx];
                    const isCorrect = jawabanUserKey === q.jawaban_benar;
                    if (isCorrect) correct++;
                    
                    return {
                        no: idx + 1,
                        soal: q.pertanyaan,
                        jawabanUser: jawabanUserKey ? q.pilihan[jawabanUserKey] : 'Tidak Dijawab',
                        jawabanBenar: q.pilihan[q.jawaban_benar],
                        isCorrect: isCorrect,
                        opsiA: q.pilihan['A'] || '',
                        opsiB: q.pilihan['B'] || '',
                        opsiC: q.pilihan['C'] || '',
                        opsiD: q.pilihan['D'] || ''
                    };
                });
                
                const nilaiAkhir = Math.round((correct / this.questions.length) * 100);

                const report = {
                    total: this.questions.length,
                    benar: correct,
                    waktuSisa: this.timeLeft,
                    nilaiAkhir: nilaiAkhir,
                    detailJawaban: resultsDetail
                };
                
                sessionStorage.setItem('suo_report_' + this.kodeMk, JSON.stringify(report));

                // POST data nilai akhir ke database (Tabel skor_mhs)
                const payload = {
                    id_user: <?= session()->get('id_user') ?: 0 ?>,
                    id_mk: <?= esc($mkData['id_mk']) ?>,
                    jenis_ujian: 'uas',
                    skor: nilaiAkhir
                };

                fetch('/api/skor/create', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                }).then(() => {
                    window.location.href = `/${this.kodeMk}/uas-hasil`;
                }).catch(err => {
                    console.error('Gagal mensinkronisasikan skor UAS:', err);
                    window.location.href = `/${this.kodeMk}/uas-hasil`;
                });
            }
        },
        mounted() {
            this.fetchQuestions();

            document.addEventListener('contextmenu', event => event.preventDefault());
            window.addEventListener('beforeunload', this.handleUnload);
        },
        unmounted() {
            clearInterval(this.timer);
            window.removeEventListener('beforeunload', this.handleUnload);
        }
    });
    app.mount('#suo-app');
</script>
</body>
</html>
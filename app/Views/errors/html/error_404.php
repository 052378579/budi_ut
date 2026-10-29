<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= lang('Errors.pageNotFound') ?> - 404</title>
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Vue.js 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>

    <style>
        :root {
            --primary: #4facfe;
            --secondary: #00f2fe;
            --accent: #ff0844;
        }

        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a; /* Deep dark blue */
            color: #f8fafc;
            overflow: hidden;
        }

        .error-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 2rem;
            z-index: 10;
        }

        /* Latar Belakang Orb Glowing */
        .glowing-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.5;
            z-index: 0;
            transition: transform 0.2s ease-out;
            pointer-events: none;
        }
        .orb-1 {
            width: 40vw; height: 40vw;
            background: linear-gradient(135deg, var(--primary), #667eea);
            top: -10%; left: -10%;
            animation: float 8s ease-in-out infinite alternate;
        }
        .orb-2 {
            width: 30vw; height: 30vw;
            background: linear-gradient(135deg, var(--accent), #ffb199);
            bottom: -5%; right: -5%;
            animation: float 6s ease-in-out infinite alternate-reverse;
        }
        .orb-3 {
            width: 20vw; height: 20vw;
            background: linear-gradient(135deg, var(--secondary), #43e97b);
            top: 40%; left: 50%;
            animation: float 10s ease-in-out infinite alternate;
        }

        /* Glassmorphism Card */
        .glass-card {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 32px;
            padding: 4rem 3rem;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 2;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 40px 70px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        /* Shimmer Effect dalam Card */
        .glass-card::before {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.05), transparent);
            transform: skewX(-20deg);
            animation: shimmer 6s infinite;
        }

        .number-404 {
            font-size: clamp(8rem, 20vw, 14rem);
            font-weight: 900;
            line-height: 0.9;
            margin: 0;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 20px 40px rgba(0,242,254,0.2);
            position: relative;
            display: inline-block;
        }

        /* Planet Icon Animasi */
        .planet-icon {
            font-size: clamp(4rem, 10vw, 6rem);
            background: linear-gradient(to top right, #f6d365 0%, #fda085 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: float-spin 6s ease-in-out infinite;
            display: inline-block;
            margin-bottom: -20px;
        }

        .error-title {
            font-size: clamp(1.5rem, 4vw, 2.5rem);
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .error-desc {
            font-size: 1.1rem;
            color: #94a3b8;
            font-weight: 400;
            line-height: 1.7;
            max-width: 500px;
            margin: 0 auto 2.5rem;
        }

        /* Premium Buttons */
        .btn-premium {
            background: linear-gradient(135deg, var(--primary), #3b82f6);
            color: white !important;
            border: none;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-premium::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.2), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .btn-premium:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 25px rgba(79, 172, 254, 0.4);
        }
        .btn-premium:hover::after { opacity: 1; }

        .btn-glass {
            background: rgba(255, 255, 255, 0.05);
            color: #e2e8f0 !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
            color: white !important;
        }

        /* Countdown Timer */
        .countdown-badge {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 0.5rem 1.5rem;
            border-radius: 30px;
            display: inline-block;
            font-size: 0.95rem;
            color: #cbd5e1;
            margin-bottom: 2rem;
            backdrop-filter: blur(5px);
        }
        .countdown-badge strong {
            color: var(--secondary);
            font-size: 1.1rem;
        }

        /* Keyframes */
        @keyframes float {
            0% { transform: translateY(0) scale(1); }
            100% { transform: translateY(-30px) scale(1.05); }
        }
        @keyframes float-spin {
            0% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(10deg); }
            100% { transform: translateY(0) rotate(0deg); }
        }
        @keyframes shimmer {
            0% { left: -100%; }
            20% { left: 200%; }
            100% { left: 200%; }
        }

        /* Transisi Judul Vue */
        .slide-fade-enter-active { transition: all 0.5s ease-out; }
        .slide-fade-leave-active { transition: all 0.5s cubic-bezier(1, 0.5, 0.8, 1); position: absolute; width: 100%; }
        .slide-fade-enter-from { transform: translateY(20px); opacity: 0; }
        .slide-fade-leave-to { transform: translateY(-20px); opacity: 0; }
        
        .title-wrapper {
            position: relative;
            height: 3rem;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 768px) {
            .glass-card { padding: 3rem 1.5rem; }
            .btn-premium, .btn-glass { width: 100%; margin-bottom: 0.5rem; }
            .title-wrapper { height: 4rem; }
        }
    </style>
</head>
<body>
    <div id="app" class="error-wrapper" @mousemove="onMouseMove">
        <!-- Glowing Orbs (Parallax Interaktif) -->
        <div class="glowing-orb orb-1" :style="{ transform: `translate(${mouseX * 0.02}px, ${mouseY * 0.02}px)` }"></div>
        <div class="glowing-orb orb-2" :style="{ transform: `translate(${mouseX * -0.03}px, ${mouseY * -0.03}px)` }"></div>
        <div class="glowing-orb orb-3" :style="{ transform: `translate(${mouseX * 0.01}px, ${mouseY * 0.01}px)` }"></div>

        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8">
                    
                    <!-- Glassmorphism Card -->
                    <div class="glass-card">
                        
                        <div class="planet-icon">
                            <i class="bi bi-globe-americas"></i>
                        </div>
                        
                        <h1 class="number-404">404</h1>
                        
                        <!-- Animated Title Vue -->
                        <div class="title-wrapper">
                            <transition name="slide-fade">
                                <h2 class="error-title" :key="currentTitleIdx">{{ dynamicTitle }}</h2>
                            </transition>
                        </div>
                        
                        <!-- Deskripsi Error -->
                        <div class="error-desc">
                            <?php if (ENVIRONMENT !== 'production') : ?>
                                <div class="text-start p-3 rounded" style="background: rgba(0,0,0,0.3); font-family: monospace; font-size: 0.9rem;">
                                    <?= nl2br(esc($message)) ?>
                                </div>
                            <?php else : ?>
                                Sepertinya halaman yang Anda cari telah berpindah dimensi, menguap ke angkasa, atau memang tidak pernah ada di tata surya <strong>Universitas Terbuka</strong>.
                            <?php endif; ?>
                        </div>

                        <!-- Timer Interaktif -->
                        <div class="countdown-badge" v-if="countdown > 0">
                            <i class="bi bi-rocket-takeoff me-2 text-info"></i> Memulangkan Anda dalam <strong>{{ countdown }}</strong> detik...
                        </div>

                        <!-- Tombol Navigasi Premium -->
                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mt-2">
                            <button @click="goHome" class="btn-premium">
                                <i class="bi bi-house-door-fill me-2"></i> Bawa Saya Pulang
                            </button>
                            <button @click="goBack" class="btn-glass">
                                <i class="bi bi-arrow-left me-2"></i> Kembali
                            </button>
                        </div>
                        
                    </div> <!-- End Glass Card -->

                </div>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    titles: [
                        "Tersesat di Ruang Angkasa?",
                        "Sinyal Terputus.",
                        "Halaman Telah Menguap.",
                        "Waduh! Keluar dari Orbit."
                    ],
                    currentTitleIdx: 0,
                    countdown: 15,
                    timer: null,
                    titleTimer: null,
                    mouseX: 0,
                    mouseY: 0,
                    windowWidth: window.innerWidth,
                    windowHeight: window.innerHeight
                }
            },
            computed: {
                dynamicTitle() {
                    return this.titles[this.currentTitleIdx];
                }
            },
            methods: {
                onMouseMove(e) {
                    // Kalkulasi posisi kursor dari tengah layar untuk efek parallax orb
                    this.mouseX = e.clientX - (this.windowWidth / 2);
                    this.mouseY = e.clientY - (this.windowHeight / 2);
                },
                onResize() {
                    this.windowWidth = window.innerWidth;
                    this.windowHeight = window.innerHeight;
                },
                rotateTitle() {
                    this.currentTitleIdx = (this.currentTitleIdx + 1) % this.titles.length;
                },
                goHome() {
                    window.location.href = '/';
                },
                goBack() {
                    window.history.back();
                },
                startCountdown() {
                    this.timer = setInterval(() => {
                        if (this.countdown > 0) {
                            this.countdown--;
                        } else {
                            clearInterval(this.timer);
                            this.goHome(); // Redirect otomatis
                        }
                    }, 1000);
                }
            },
            mounted() {
                window.addEventListener('resize', this.onResize);
                this.titleTimer = setInterval(this.rotateTitle, 3500);
                this.startCountdown();
            },
            beforeUnmount() {
                window.removeEventListener('resize', this.onResize);
                if (this.timer) clearInterval(this.timer);
                if (this.titleTimer) clearInterval(this.titleTimer);
            }
        }).mount('#app');
    </script>
</body>
</html>

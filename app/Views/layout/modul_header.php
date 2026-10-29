<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#004da1">
    <title><?= esc($title ?? 'UT Tes Formatif') ?></title>
    <link rel="icon" type="image/x-icon" href="/ut.ico">
    
    <!-- Pra-koneksi DNS ke server CDN -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Global Stylesheet -->
    <link rel="stylesheet" href="/css/modul_styles.css?v=<?= time() ?>">

    <!-- CSS Tambahan Khusus Per Halaman Anak -->
    <?= $this->renderSection('extra_styles') ?>
    
    <!-- Google AdSense Auto Ads -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8140585707768741" crossorigin="anonymous"></script>
</head>
<body>

<div id="ut" v-cloak class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg fixed-top navbar-dark shadow-sm pb-3 transition-all" :class="navThemeColor" style="transition: background-color 0.3s ease;">
        <div class="container">
            
            <a class="navbar-brand d-flex align-items-center gap-2" :href="'/'">
                <img :src="isLightTheme ? '/Logo_UT.png' : '/Logo_UT_white.png'" height="38" alt="Logo UT" @error="handleLogoError">
                <div class="border-start border-2 ps-2 no-border-mobile" :class="isLightTheme ? 'border-dark text-dark' : 'border-white text-white'">
                    <div class="fw-bold lh-1">TES FORMATIF</div>
                    <div class="profile-brand-text font-monospace d-none d-md-block" :class="isLightTheme ? 'text-secondary' : 'text-white-50'">Universitas Terbuka</div>
                </div>
            </a>
            
            <a :href="'/' + (kodeMk || '')" 
               class="navbar-text text-decoration-none profile-link d-none d-md-block ms-3 ps-3 border-start" 
               :class="isLightTheme ? 'text-dark border-dark border-opacity-25' : 'text-white border-light border-opacity-25'"
               title="Kembali ke Daftar Modul">
                <i class="bi bi-folder2-open me-1"></i> {{ courseName || 'Memuat Modul...' }}
            </a>
            
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                
                <ul class="navbar-nav me-auto mt-3 mt-lg-0">
                    <li class="nav-item ms-lg-4" v-if="kodeMk">
                        <a :href="'/' + kodeMk + '/uas-simulasi'" 
                           class="nav-link fw-bold d-inline-flex align-items-center" 
                           :class="isLightTheme ? 'text-dark' : 'text-white'"
                           title="Mulai Simulasi Ujian UAS">
                           <i class="bi bi-laptop me-2"></i> Simulasi UAS
                        </a>
                    </li>

                    <!-- Menampilkan Peringkat Simulasi UAS di Beranda MK (saat modul null) ATAU saat sedang di halaman Simulasi UAS -->
                    <li class="nav-item ms-lg-2" v-if="kodeMk && (!modul || modul === 'Simulasi UAS')">
                        <a :href="'/peringkat-uas/' + kodeMk" 
                           class="btn btn-sm rounded-pill px-3 mt-2 mt-lg-0 shadow-sm fw-bold border-2 d-inline-flex align-items-center"
                           :class="isLightTheme ? 'btn-outline-dark' : 'btn-outline-light'">
                            <i class="bi bi-trophy-fill me-1 text-warning"></i> Peringkat Simulasi UAS
                        </a>
                    </li>
                    
                    <!-- Menampilkan Peringkat Modul saat berada di halaman Modul spesifik -->
                    <li class="nav-item ms-lg-2" v-if="modul && modul !== 'Simulasi UAS'">
                        <a :href="'/peringkat/' + kodeMk + '/MODUL-' + modul" 
                           class="btn btn-sm rounded-pill px-3 mt-2 mt-lg-0 shadow-sm fw-bold border-2 d-inline-flex align-items-center"
                           :class="isLightTheme ? 'btn-outline-dark' : 'btn-outline-light'">
                            <i class="bi bi-trophy-fill me-1 text-warning"></i> Peringkat Modul {{ modul }}
                        </a>
                    </li>
                </ul>
                
                <div v-if="currentUser" class="d-flex align-items-center justify-content-lg-end mt-3 mt-lg-0 pt-3 pt-lg-0 border-top d-lg-block border-lg-none" :class="isLightTheme ? 'border-dark border-opacity-25' : 'border-white border-opacity-25'">
                    
                    <div class="dropdown">
                        <a class="text-decoration-none d-flex align-items-center gap-2 dropdown-toggle dropdown-toggle-profile" 
                           :class="isLightTheme ? 'text-dark' : 'text-white'"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            
                            <img :src="'/api/dokumen/profil/' + 
                                      (currentUser.img_mhs || (currentUser.no_hp + '_' + currentUser.nama_user.toLowerCase() + '.webp') || 'mahasiswa.webp') + 
                                      (typeof imageVersion !== 'undefined' && imageVersion ? '?t=' + imageVersion : '')" 
                                class="rounded-circle border border-opacity-50 shadow-sm" 
                                :class="isLightTheme ? 'border-dark' : 'border-white'"
                                :style="isLightTheme ? 'width: 35px; height: 35px; object-fit: cover; background-color: rgba(0,0,0,0.05);' : 'width: 35px; height: 35px; object-fit: cover; background-color: rgba(255,255,255,0.1);'" 
                                alt="Profil"
                                loading="lazy"
                                @error="(e) => { e.target.src = '/api/dokumen/profil/mahasiswa.webp'; }">
                            
                            <div class="d-flex flex-column lh-1 text-start">
                                <span class="fw-bold" style="font-size: 0.9rem;">{{ currentUser.nama_user }}</span>
                            </div>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-lg-end dropdown-menu-custom">                                                    
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/profil">
                                    <i class="bi bi-person-circle text-primary"></i> Profil Saya
                                </a>
                            </li>
                            <li><hr class="dropdown-divider opacity-10 my-1"></li>
                            
                            <template v-if="currentUser.id_user == 1">
                                <li>
                                    <a class="dropdown-item text-danger d-flex align-items-center gap-2" href="/admin">
                                        <i class="bi bi-speedometer2"></i> Admin
                                    </a>
                                </li>
                            </template>
                            
                            <li>
                                <a class="dropdown-item text-danger d-flex align-items-center gap-2" href="#" @click.prevent="logout">
                                    <i class="bi bi-box-arrow-right"></i> Keluar
                                </a>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
            
        </div>
    </nav>
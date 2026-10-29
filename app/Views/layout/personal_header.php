<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    
    <meta name="theme-color" :content="typeof navThemeColor !== 'undefined' ? (navThemeColor === 'bg-dark' ? '#212529' : '#004da1') : '#004da1'">
    <title><?= esc($title ?? '') ?> | Tes Formatif</title>
    <link rel="icon" type="image/x-icon" href="/ut.ico">
    
    <!-- Pra-koneksi DNS ke server CDN -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Global Personal Stylesheet -->
    <link rel="stylesheet" href="/css/personal_styles.css?v=<?= time() ?>">
    
    <?= $this->renderSection('extra_styles') ?>

    <!-- Google AdSense Auto Ads -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8140585707768741" crossorigin="anonymous"></script>
</head>
<body>

<div id="ut" v-cloak class="min-vh-100 d-flex flex-column">

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom pb-3 fixed-top" :class="typeof navThemeColor !== 'undefined' ? navThemeColor : ''">
        <div class="container">
            
            <a class="navbar-brand d-flex align-items-center gap-2" href="/">
                <img src="/Logo_UT_white.png" height="38" alt="Logo UT" @error="handleLogoError">
                
                <div class="border-start border-white border-2 ps-2 text-white no-border-mobile">
                    <div class="fw-bold lh-1">TES FORMATIF</div>
                    <div class="profile-brand-text text-white-50 font-monospace d-none d-md-block">Universitas Terbuka</div>
                </div>
            </a>
            
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavPersonal">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNavPersonal">
                <ul class="navbar-nav mx-auto my-2 my-lg-0 gap-1">
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom <?= url_is('/') ? 'active' : '' ?>" href="/">
                            <i class="bi bi-house-door me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item" v-if="currentUser">
                        <a class="nav-link nav-link-custom <?= url_is('riwayat') ? 'active' : '' ?>" href="/riwayat">
                            <i class="bi bi-clock-history me-1"></i> Riwayat Saya
                        </a>
                    </li>
                    <li class="nav-item" v-if="currentUser">
                        <a class="nav-link nav-link-custom <?= url_is('favorit') ? 'active' : '' ?>" href="/favorit">
                            <i class="bi bi-heart me-1"></i> Favorit Saya
                        </a>
                    </li>
                </ul>
                
                <div v-if="currentUser" class="d-flex align-items-center justify-content-lg-end mt-3 mt-lg-0 pt-3 pt-lg-0 border-top border-white border-opacity-25 border-lg-0">
                    
                    <div class="dropdown">
                        <a class="text-decoration-none text-white d-flex align-items-center gap-2 dropdown-toggle dropdown-toggle-profile" 
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            
                            <img :src="'/api/dokumen/profil/' + 
                                      (currentUser.img_mhs || (currentUser.no_hp + '_' + currentUser.nama_user.toLowerCase() + '.webp') || 'mahasiswa.webp') + 
                                      (typeof imageVersion !== 'undefined' && imageVersion ? '?t=' + imageVersion : '')"
                                class="rounded-circle border border-white" 
                                style="width: 35px; height: 35px; object-fit: cover; background-color: #f8f9fa;" 
                                alt="Avatar"
                                loading="lazy"
                                @error="(e) => { e.target.src = '/api/dokumen/profil/mahasiswa.webp'; }">
                            
                            <div class="d-flex flex-column text-start">
                                <span class="fw-bold lh-sm" style="font-size: 0.9rem;">{{ currentUser.nama_user }}</span>
                            </div>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-lg-end dropdown-menu-custom">                                                    
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/profil">
                                    <i class="bi bi-person-circle text-primary"></i> Profil Saya
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="#" @click.prevent="bukaModalSaran">
                                    <i class="bi bi-chat-text-fill text-primary"></i> Saran Masukan
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
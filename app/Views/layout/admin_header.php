<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Admin Panel') ?> | Tes Formatif UT</title>
    
    <!-- Pra-koneksi DNS ke server CDN -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link class="icon" type="image/x-icon" href="/ut.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Admin Stylesheet -->
    <link rel="stylesheet" href="/css/admin/styles.css?v=<?= time() ?>">

    <!-- Style tambahan dari views -->
    <?= $this->renderSection('extra_styles') ?>
</head>
<body class="bg-light">

<div id="ut" v-cloak class="d-flex flex-column min-vh-100">
    
    <nav class="navbar navbar-expand-lg navbar-dark navbar-admin fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/admin">
                <img src="/Logo_UT_white.png" style="width:40px;" class="me-2" alt="Logo UT">Admin
            </a>
            
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAdmin">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNavAdmin">
                <ul class="navbar-nav me-auto gap-2">
                    <li class="nav-item">
                        <a class="nav-link fw-bold <?= url_is('admin/matakuliah*') ? 'active' : '' ?>" href="/admin/matakuliah">
                            <i class="bi bi-journal-text me-1"></i> Mata Kuliah
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold <?= url_is('admin/skor*') ? 'active' : '' ?>" href="/admin/skor">
                            <i class="bi bi-bar-chart-line me-1"></i> Skor
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold <?= url_is('admin/users*') ? 'active' : '' ?>" href="/admin/users">
                            <i class="bi bi-people me-1"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold <?= url_is('admin/pengumuman*') ? 'active' : '' ?>" href="/admin/pengumuman">
                            <i class="bi bi-megaphone me-1"></i> Pengumuman
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold <?= url_is('admin/logs*') ? 'active' : '' ?>" href="/admin/logs">
                            <i class="bi bi-shield-check me-1"></i> Log Sistem
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold <?= url_is('admin/saran*') ? 'active' : '' ?>" href="/admin/saran">
                            <i class="bi bi-chat-heart me-1"></i> Saran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold <?= url_is('admin/donasi*') ? 'active' : '' ?>" href="/admin/donasi">
                            <i class="bi bi-wallet2 me-1"></i> Donasi
                        </a>
                    </li>
                </ul>
                
                <div class="dropdown mt-3 mt-lg-0 ms-lg-3" v-if="adminUser">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="me-2 fw-medium">Hai, {{ adminUser.nama_user }}</span>
                        <img :src="'/api/dokumen/profil/' + 
                                (adminUser.img_mhs || (adminUser.no_hp + '_' + adminUser.nama_user.toLowerCase() + '.webp') || 'mahasiswa.webp') + 
                                (typeof imageVersion !== 'undefined' && imageVersion ? '?t=' + imageVersion : '')" 
                            class="rounded-circle border border-light shadow-sm" 
                            style="width: 32px; height: 32px; object-fit: cover; background-color: #f8f9fa;" 
                            alt="Avatar Admin"
                            loading="lazy"
                            @error="(e) => { e.target.src = '/api/dokumen/profil/mahasiswa.webp'; }">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg-end dropdown-menu-custom shadow-sm border-0 mt-2" aria-labelledby="adminDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="/admin/top-cities">
                                <i class="bi bi-bar-chart-fill text-primary"></i> Statistik Kota
                            </a>
                        </li>
                        <li><hr class="dropdown-divider opacity-10 my-1"></li>
                        <li>
                            <a class="dropdown-item text-danger d-flex align-items-center gap-2" href="#" @click.prevent="logout">
                                <i class="bi bi-box-arrow-right"></i> Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <?php if(url_is('admin/logs*')): ?>
        <div class="progress rounded-0 position-absolute w-100 start-0" style="height: 4px; bottom: -4px; z-index: 1030; background-color: transparent;">
            <div class="progress-bar bg-danger" role="progressbar" :style="{ width: typeof progressWidth !== 'undefined' ? progressWidth + '%' : '100%' }" style="transition: width 1s linear;"></div>
        </div>
        <?php endif; ?>
    </nav>